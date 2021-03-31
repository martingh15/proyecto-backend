<?php

namespace App\Http\Controllers;

use App\Mail\OlvidePassword;
use App\Rol;
use App\Services\UsuarioService;
use Exception;
use Illuminate\Contracts\Routing\ResponseFactory;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use App\Usuario;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use JWTAuth;
use Illuminate\Support\Facades\Response;
use Illuminate\Http\Response as HttpResponse;

class LoginController extends Controller {

    /**
     * @var UsuarioService
     */
    protected  $usuarioService;

    /**
     * Create a new controller instance.
     *
     * @param UsuarioService $usuarioService
     */
    public function __construct(UsuarioService $usuarioService)
    {
        $this->usuarioService = $usuarioService;
    }

    public function login(Request $request)
    {
        $servicio    = $this->getUsuarioService();
        $credentials = $request->only('email', 'password', 'forzado');

        try {
            $email   = $credentials['email'] ?? '';
            $usuario = $servicio->getUsuarioPorEmail($email);
            if (empty($usuario)) {
                return Response::json(array(
                    'code' => 500,
                    'message' => "Usuario y/o contraseña incorrectos."
                ), 500);
            }
            if ($usuario->habilitado == 0) {
                return Response::json(array(
                    'code' => 500,
                    'message' => "Su usuario no ha sido habilitado aún. Aguarde la habilitación o contáctese con nosotros."
                ), 500);
            }
            if ($usuario->borrado) {
                return Response::json(array(
                    'code' => 500,
                    'message' => "El usuario ha sido borrado, si desea recuperarlo contacte al administrador."
                ), 500);
            }
            if ($credentials['forzado'] === "" && !Hash::check($credentials['password'], $usuario->password)) {
                return Response::json(array(
                    'code' => 500,
                    'message' => "Usuario y/o contraseña incorrectos."
                ), 500);
            } else if (isset($credentials['forzado']) && $credentials['forzado'] && $credentials['password'] !== $usuario->password) {
                return Response::json(array(
                    'code' => 500,
                    'message' => "Hubo un error en el servidor contactese con el administrador de la página."
                ), 500);
            }
            //Genero token
            $datosToken = [
                'idUsuario' => $usuario->id,
                'esAdmin'   => $usuario->tieneRol(Rol::ADMIN),
                'nombre'    => $usuario->nombre . " " . $usuario->apellido
            ];
            $token = JWTAuth::fromUser($usuario, $datosToken);
            //Si hubo problema con token
            if (!$token) {
                return response()->json(['error' => 'invalid_credentials'], 401);
            }
        } catch (JWTException $e) {
            // si no se puede crear el token
            return response()->json(['error' => 'could_not_create_token'], 500);
        }
        return response()->json(compact('token'))->header('Access-Control-Allow-Origin', '*');
    }

    public function olvidoPassword(Request $request)
    {
        $this->validate($request, [
            'email' => 'email|required'
        ]);
        $credentials = $request->only('email');
        $usuario = Usuario::where("email", $credentials['email'])->first();

        if (empty($usuario))
            return Response::json(array(
                'code' => 500,
                'message' => "El email ingresado no corresponde a ningún usuario registrado."
            ), 500);

        //Genero token
        $tokenReset = Str::random(64);
        $fechaExpiraToken = (new \DateTime())->setTimezone(new \DateTimeZone("America/Argentina/Buenos_Aires"));
        $fechaExpiraToken->add(new \DateInterval('PT' . 1440 . 'M'));

        //Guardo token y fecha expira
        $usuario->tokenReset = $tokenReset;
        $usuario->fechaTokenReset = $fechaExpiraToken;
        $usuario->save();

        Mail::to($usuario->email)->send(new OlvidePassword($usuario));
        return Response::json(array(
            'code' => 200,
            'message' => "Se ha enviado un link a su email para reiniciar su contraseña. Tiene 24 horas para cambiarla."
        ), 200);
    }

    /**
     * Resetea a contraseña de un usuario alidad que el token sea válido
     *
     * @param Request $request
     * @return ResponseFactory|JsonResponse|HttpResponse
     * @throws Exception
     */
    public function resetPassword(Request $request)
    {
        $usuario = null;
        try {
            $this->validate($request, [
                'tokenReset' => 'required',
                'password'   => 'required|confirmed'
            ]);
            $credentials = $request->only('email');

            $fechaHoy = (new \DateTime())->setTimezone(new \DateTimeZone("America/Argentina/Buenos_Aires"));
            $usuario = Usuario::where([["tokenReset", $request['tokenReset']], ["fechaTokenReset", ">=", $fechaHoy]])->first();
            if (empty($usuario))
                return Response::json(array(
                    'code' => 500,
                    'message' => "El token ingresado no es válido o ha caducado. Recuerda que tiene 24 " .
                        "horas para cambiar la contraseña"
                ), 500);
            $usuario->password        = Hash::make($request['password']);
            $usuario->tokenReset      = null;
            $usuario->fechaTokenReset = null;
            //$usuario->save();
        } catch (Exception $exc) {
            \Log::info($exc->getTraceAsString());
        }
        if ($usuario === null) {
            return Response::json(array(
                'code' => 500,
                'message' => "Hubo un error al reiniciar la contraseña. Vuelva a ingresar a intentar la recuperación de la misma."
            ), 500);
        }

        return response(['usuario' => $usuario]);
    }

    /**
     * Valida que el token sea válido
     *
     * @param Request $request
     * @return JsonResponse
     * @throws Exception
     */
    public function validarToken(Request $request)
    {
        $this->validate($request, [
            'token' => 'required'
        ]);
        $validatedData = $request->only('token');

        //Busco usuario que coincida con el token y fecha
        $fechaHoy = \Carbon\Carbon::now()->startOfDay()->format('Y-m-d H:i:s');
        $usuario = Usuario::where([["tokenReset", $validatedData['token']], ["fechaTokenReset", ">=", $fechaHoy]])->first();
        if (empty($usuario))
            return Response::json(array(
                'code' => 500,
                'message' => "El token ingresado no es válido o ha caducado. Vuelva a solicitar el cambio de contraseña."
            ), 500);
        else {
            return Response::json(array(
                'code' => 200,
                'message' => "El token fue validado correctamente."
            ), 200);
        }
    }

    /**
     * Valida que el token sea válido
     *
     * @param Request $request
     * @return JsonResponse
     * @throws Exception
     */
    public function validarTokenEmail(Request $request)
    {
        $this->validate($request, [
            'token' => 'required'
        ]);
        $validatedData = $request->only('token');

        //Busco usuario que coincida con el token y fecha
        $usuario = Usuario::where("tokenEmail", $validatedData['token'])->first();
        if (empty($usuario))
            return Response::json(array(
                'code' => 500,
                'message' => "El token ingresado no es válido o ha caducado. Comuníquese con nosotros mediante la sección."
            ), 500);
        $usuario->habilitado = true;
        $usuario->tokenEmail = null;
        $usuario->save();
        $request = new Request(['email' => $usuario->email, 'password' => $usuario->password, 'forzado' => true]);
        return $this->login($request);
    }

    /**
     * Si el usuario intenta acceder a una ruta protegida por autenticación le indica
     * que no puede acceder a la misma
     *
     * @return JsonResponse
     */
    public function redirect()
    {
        return Response::json(array(
            'code' => 500,
            'message' => "No esta autorizado a ingresar a esta ruta."
        ), 500);
    }

    /**
     * @return UsuarioService
     */
    protected function getUsuarioService(): UsuarioService {
        return $this->usuarioService;
    }
}

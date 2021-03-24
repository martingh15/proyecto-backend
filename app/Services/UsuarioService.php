<?php


namespace App\Services;

use App\Mail\ValidarEmail;
use App\Rol;
use App\Usuario;
use App\UsuarioRol;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Str;

class UsuarioService  {

    public function getUsuarioPorEmail(string $email): ?Usuario {
        return Usuario::where('email', $email)->first();
    }

    public function registrarUsuario(Request $request) {

        $usuarioGuardado = Usuario::where('email', $request['email'])->first();

        if (!empty($usuarioGuardado)) {
            return Response::json(array(
                'code' => 500,
                'message' => "Ya existe un usuario con ese email."
            ), 500);
        }

        try {
            DB::beginTransaction();
            $usuario = new Usuario();
            $usuario->email      = $request['email'];
            $usuario->password   = Hash::make($request['password']);
            $usuario->nombre     = $request['nombre'];
            $usuario->tokenEmail = Str::random(64);
            $usuario->save();

            $rol   = Rol::where('nombre', Rol::ROL_ROOT)->first();
            $idRol = $rol->id;

            $usuarioRol = new UsuarioRol();
            $usuarioRol->idRol     = $idRol;
            $usuarioRol->idUsuario = $usuario->id;
            $usuarioRol->save();

            //Mail::to($usuario->email)->send(new ValidarEmail($usuario));

            DB::commit();
        } catch(Exception $e) {
            DB::rollback();
            return response()->json([
                'message' => 'Ha ocurrido un error al crear el usuario',
                'errores' => $e->getMessage()
            ], 500);
        }
        return Response::json(array(
            'code' => 200,
            'message' => "Usuario creado correctamente."
        ), 200);
    }

    public function updateUsuario($bodyContent) {
        $usuario = Usuario::find($bodyContent["id"]);
        $usuario->fill($bodyContent);
        if (empty($usuario))
            return Response::json(array(
                'code' => 401,
                'message' => "Usuario no encontrado, ingresen nuevamente"
            ), 401);
        if (isset($bodyContent['nombre_modificado']) && $bodyContent['nombre_modificado'] !== "") {
            $usuario->nombre = $bodyContent['nombre_modificado'];
        }
        if (isset($bodyContent['password'])) {
            $usuario->password = Hash::make($bodyContent['password']);
        }        
        $usuario->tokenReset = null;
        $usuario->fechaTokenReset = null;
        $usuario->save();
        return response(['usuario' => $usuario]);
    }

    public function getUsuarioLogueado() {
        $idUsuario              = Auth::user()->id;
        $usuario                = Usuario::where('id', $idUsuario)->with('roles')->first();
        $usuario['esAdmin']     = $usuario->tieneRol(Rol::ROL_ADMIN);
        $usuario['operaciones'] = $this->getOperacionesUsuario($usuario);
        return $usuario;
    }

    protected function getOperacionesUsuario(Usuario $usuario): array {
        $esAdmin          = $usuario['esAdmin'];
        $operaciones      = [];
        $operacionesAdmin = [];
        if ($esAdmin) {
            $operacionesAdmin = $this->getOperacionesAdmin();
        }
        $operaciones = array_merge($operaciones, $operacionesAdmin);
        return $operaciones;
    }

    protected function getOperacionesAdmin(): array {
        return [
            [
                'ruta'        => '/admin/usuario',
                'icono'       => '',
                'rol'         => Rol::ROL_ADMIN,
                'titulo'      => 'Usuarios',
                'descripcion' => 'Permite crear usuarios con diferentes roles',
            ],
            [
                'ruta'        => '/compras',
                'icono'       => '',
                'rol'         => Rol::ROL_ADMIN,
                'titulo'      => 'Compras',
                'descripcion' => 'Permite gestionar las compras'
            ]
        ];
    }
}
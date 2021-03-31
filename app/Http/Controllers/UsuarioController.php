<?php

namespace App\Http\Controllers;

use App\Usuario;
use App\Services\UsuarioService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;

class UsuarioController extends Controller
{
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


    public function registro(Request $request)
    {
        $this->validate($request, [
            'email' => 'email|required',
            'password' => 'required|confirmed',
            'nombre' => 'required'
        ]);
        $servicio = $this->getUsuarioService();
        return $servicio->registrarUsuario($request, 'comun');
    }

    public function registroAdmin(Request $request)
    {
        $tipoRegistro = $request['tipoRegistro'] ?? '';
        if ($tipoRegistro === '' || $tipoRegistro !== "admin") {
            return Response::json(array(
                'code' => 500,
                'message' => "Hubo un error intentado registrar al usuario, contáctese con el administrador."
            ), 500);
        }
        $this->validate($request, [
            'email'    => 'email|required',
            'dni'      => 'required',
            'nombre'   => 'required'
        ]);
        $servicio = $this->getUsuarioService();
        return $servicio->registrarUsuario($request, $tipoRegistro);
    }

    public function update(Request $request)
    {
        $servicio    = $this->getUsuarioService();
        $bodyContent = json_decode($request->getContent(), true);
        return $servicio->updateUsuario($bodyContent);
    }

    public function index()
    {
        $servicio = $this->getUsuarioService();
        return $servicio->getUsuarios();
    }

    public function store(Request $request)
    {
        $servicio    = $this->getUsuarioService();
        $bodyContent = json_decode($request->getContent(), true);
        return $servicio->updateUsuario($bodyContent, true);
    }

    public function create()
    {
        $servicio = $this->getUsuarioService();
        return $servicio->getUsuarioLogueado();
    }
	
	public function destroy(Request $request, int $id) {
		$idUsuario = Auth::user()->id;
		if ($id <= 0) {
			return response()->json([
				'message' => 'El id el usuario a buscar es inválido.',
			], 500);
		}
		if (intval($idUsuario) === $id) {
			return response()->json([
				'message' => 'No es posible borrar el usuario logueado.',
			], 500);
		}
		$servicio = $this->getUsuarioService();
        $borrado  = $servicio->borrarUsuario($id);
		if ($borrado->error()) {
			$errores = $borrado->getMensajesError();
			return Response::json(array(
				'code' => 500,
				'message' => "Hubo un error al actualizar el usuario: $errores"
			), 500);
		}
		$mensaje = $borrado->getMensajes();
		return Response::json(array(
			'code'	  => 200,
			'message' => $mensaje,
			'usuario' => $id
		), 200);
	}
	
	public function buscar(Request $request, int $id) {
		if ($id <= 0) {
			return response()->json([
				'message' => 'El id el usuario a buscar es inválido.',
			], 500);
		}
		$servicio = $this->getUsuarioService();
        return $servicio->getUsuario($id);
	}

    /**
     * @return UsuarioService
     */
    protected function getUsuarioService(): UsuarioService {
        return $this->usuarioService;
    }
}

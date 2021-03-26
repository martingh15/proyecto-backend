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
            'rol'      => 'required',
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
        return Usuario::all();
    }

    public function store(Request $request)
    {

    }

    public function create()
    {
        $servicio = $this->getUsuarioService();
        return $servicio->getUsuarioLogueado();
    }

    /**
     * @return UsuarioService
     */
    protected function getUsuarioService(): UsuarioService {
        return $this->usuarioService;
    }
}

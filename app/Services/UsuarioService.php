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

    public function registrarUsuario(Request $request, string $tipoRegistro) {

        $dni               = $request['dni'] ?? '';
        $clave             = $request['password'] ?? '';
        $tipoRegistroAdmin = $tipoRegistro === "admin";

        if ($tipoRegistroAdmin) {
            $id       = Auth::user()->id;
            $logueado = Usuario::where('id', $id)->first();
            $esAdmin  = $logueado->tieneRol(Rol::ROL_ADMIN);
            if (!$esAdmin) {
                return Response::json(array(
                    'code' => 500,
                    'message' => "No está autorizado para realizar esta operación."
                ), 500);
            }
        }

        $emailRepetido = Usuario::where('email', $request['email'])->first();
        if (!empty($emailRepetido)) {
            return Response::json(array(
                'code' => 500,
                'message' => "Ya existe un usuario con ese email."
            ), 500);
        }

        $dniRepetido = null;
        if ($tipoRegistroAdmin) {
            $dniRepetido = Usuario::where('dni', $request['dni'])->first();
        }
        if ($dniRepetido !== null && !empty($dniRepetido)) {
            return Response::json(array(
                'code' => 500,
                'message' => "Ya existe un usuario con ese dni."
            ), 500);
        }

        try {

            DB::beginTransaction();
            $usuario = new Usuario();
            $usuario->email  = $request['email'];
            $usuario->nombre = $request['nombre'];
            if ($tipoRegistroAdmin) {
                $clave               = $dni;
                $usuario->dni        = $dni;
                $usuario->habilitado = 1;
            }
            $usuario->password   = Hash::make($clave);
            $usuario->tokenEmail = Str::random(64);
            $usuario->save();

            $rolBuscar = Rol::ROL_COMENSAL;
            $rol       = $request['rol'];
            if ($tipoRegistroAdmin) {
                $encontrado = in_array($rol, Rol::ROLES);
                if (!$encontrado) {
                    return Response::json(array(
                        'code' => 500,
                        'message' => "El rol ingresado no es un rol válido."
                    ), 500);
                }
                $rolBuscar = $rol;
            }
            $rol   = Rol::where('nombre', $rolBuscar)->first();
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
        $mensaje = "¡Se ha registro con éxito! Para poder ingresar debe validar su email ingresando al link que le enviamos a su correo.";
        if ($tipoRegistroAdmin) {
            $mensaje = "El usuario fue creado correctamente. Para ingresar debe ingresar con su dni como contraseña.";
        }
        return Response::json(array(
            'code'    => 200,
            'message' => $mensaje,
            'admin'   => $tipoRegistroAdmin
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

    public function getUsuarioLogueado(bool $conOperaciones = true) {
        $idUsuario          = Auth::user()->id;
        $usuario            = Usuario::where('id', $idUsuario)->with('roles')->first();
        $usuario['esAdmin'] = $usuario->tieneRol(Rol::ROL_ADMIN);
        if ($conOperaciones) {
            $usuario['operaciones'] = $this->getOperacionesUsuario($usuario);
        }
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
                'ruta'        => '/gestion/usuarios',
                'icono'       => '',
                'rol'         => Rol::ROL_ADMIN,
                'titulo'      => 'Usuarios',
                'descripcion' => 'Permite gestionar los usuarios del sistema',
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

    public function getUsuarios(): array {
        $logueado = $this->getUsuarioLogueado(false);
        $usuarios = DB::table('usuarios')->selectRaw('usuarios.id')
            ->join("usuario_rol", "usuarios.id", "=", "usuario_rol.idUsuario")
            ->join("roles", "usuario_rol.idRol", "=", "roles.id")
            ->where(function ($query) {
                $query->orWhere("roles.nombre", Rol::ROL_MOZO)
                    ->orWhere("roles.nombre", Rol::ROL_VENDEDOR);
            })
            ->where('usuarios.id', '<>', $logueado->id)
            ->groupBy('usuarios.id')
            ->orderByRaw('usuarios.nombre ASC')->get();
        $array = [];
        foreach ($usuarios as $item) {
            $array[] = Usuario::where('id', $item->id)->with('roles')->first();
        }
        $todos = array_merge($array, [$logueado]);
        usort($todos, function($a, $b) {
            return strcmp($a["nombre"], $b["nombre"]);
        });
        return $todos;
    }
}
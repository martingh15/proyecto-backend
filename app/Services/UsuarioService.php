<?php


namespace App\Services;

use App\Mail\ValidarEmail;
use App\Rol;
use App\Usuario;
use App\UsuarioRol;
use Exception;
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
			
			$usuarioArray = json_decode($request->getContent(), true);
			$resultado    = $this->agregarRolesUsuario($usuario, $usuarioArray, $tipoRegistroAdmin);
			if ($resultado->error()) {
				$errores = $resultado->getMensajesError();
				return response()->json([
					'message' => 'Ha ocurrido un error al crear el usuario.',
					'errores' => $errores
				], 500);
			}

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

	/**
	 * Agrega los roles al usuario según la request
	 * 
	 * @param Usuario $usuario
	 * @param array $usuarioArray
	 * @param bool $tipoRegistroAdmin
	 * @return Resultado
	 */
	protected function agregarRolesUsuario(Usuario $usuario, array $usuarioArray, bool $tipoRegistroAdmin): Resultado {
		$resultado = new Resultado();
		try {
			if ($tipoRegistroAdmin) {
				$esAdministrador = isset($usuarioArray['esAdministrador']) && $usuarioArray['esAdministrador'];
				if ($esAdministrador) {
					$adminAgregado = $this->agregarRol($usuario, Rol::ROL_ADMIN);
					if ($adminAgregado->error()) {
						$resultado->fusionar($adminAgregado);
					}
				}
				$esMozo = isset($usuarioArray['esMozo']) && $usuarioArray['esMozo'];
				if ($esMozo) {
					$mozoAgregado = $this->agregarRol($usuario, Rol::ROL_MOZO);
					if ($mozoAgregado->error()) {
						$resultado->fusionar($mozoAgregado);
					}
				}
				$esVendedor = isset($usuarioArray['esVendedor']) && $usuarioArray['esVendedor'];
				if ($esVendedor) {
					$vendedorAgregado = $this->agregarRol($usuario, Rol::ROL_VENDEDOR);
					if ($vendedorAgregado->error()) {
						$resultado->fusionar($vendedorAgregado);
					}
				}
			} else {
				$agregado = $this->agregarRol($usuario, Rol::ROL_COMENSAL);
				if ($agregado->error()) {
					$resultado->fusionar($agregado);
				}
			}
		} catch (Throwable $t) {
			$resultado->agregarError(Resultado::ERROR_GENERICO, (string) $t);
		}

		return $resultado;		
	}
	
	/**
	 * Agrega un rol al usuario
	 * 
	 * @param Usuario $usuario
	 * @param string $rol
	 * @return Resultado
	 */
	protected function agregarRol(Usuario $usuario, string $rol): Resultado {
		$resultado = new Resultado();
		try {
			$rol   = Rol::where('nombre', $rol)->first();
			$idRol = $rol->id;

			$usuarioRol				 = new UsuarioRol();
			$usuarioRol->idRol		 = $idRol;
			$usuarioRol->idUsuario	 = $usuario->id;
			$usuarioRol->save();
		} catch (Throwable $t) {
			$resultado->agregarError(Resultado::ERROR_GENERICO, "Hubo un error al agregar el rol $rol.");
		}
		return $resultado;
			
	}
	/**
	 * Si $admin es true verificamos que el usuario logueado sea admin ya que
	 * el usuario logueado está editando otro usuario.
	 * 
	 * @param array $usuarioArray
	 * @param bool $admin
	 * @return type
	 */
    public function updateUsuario(array $usuarioArray, bool $admin = false) {
		if ($admin) {
			$idLogueado = Auth::user()->id;
			$usuario	= Usuario::find($idLogueado);
			$esAdmin	= $usuario->tieneRol(Rol::ROL_ADMIN);
			if (!$esAdmin) {
				 return Response::json(array(
					'code' => 401,
					'message' => "No está autorizado para editar usuarios."
				), 401);
			}
		}
        $usuario = Usuario::find($usuarioArray["id"]);
        $usuario->fill($usuarioArray);
        if (empty($usuario)) {
            return Response::json(array(
                'code' => 404,
                'message' => "Usuario no encontrado, ingresen nuevamente"
            ), 401);
		}
        if (isset($usuarioArray['password'])) {
            $usuario->password = Hash::make($usuarioArray['password']);
        }        
        $usuario->tokenReset      = null;
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
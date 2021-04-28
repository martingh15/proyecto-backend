<?php


namespace App\Services;

use App\Mail\ValidarEmail;
use App\Modelo\Rol;
use App\Modelo\UsuarioRol;
use App\Resultado\Resultado;
use App\Usuario;
use Exception;
use Illuminate\Contracts\Routing\ResponseFactory;
use Illuminate\Foundation\Application;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Str;
use Throwable;

class UsuarioService
{

	// <editor-fold defaultstate="collapsed" desc="Búsquedas">
	public function getUsuario(int $id): ?Usuario
	{
		$usuario = Usuario::where([['id', $id], ['auditoriaBorrado', null]])->first();
		return $usuario;
	}

	/**
	 * Busca usuarios por email. Sin importar si la propiedad borrado es true
	 * 
	 * @param string $email
	 * @return Usuario|null
	 */
	public function getUsuarioPorEmail(string $email): ?Usuario
	{
		return Usuario::where([['email', $email]])->first();
	}

	/**
	 * Busca usuarios por dni.
	 *
	 * @param string $dni
	 * @return Usuario|null
	 */
	public function getUsuarioPorDni(string $dni): ?Usuario
	{
		return Usuario::where([['dni', $dni], ['auditoriaBorrado', null]])->first();
	}

	public function getUsuarioLogueado(bool $conOperaciones = true)
	{
		$idUsuario = Auth::user()->id;
		$usuario   = Usuario::where([['id', $idUsuario], ['auditoriaBorrado', null]])->with('roles')->first();
		if ($conOperaciones) {
			$usuario['operaciones'] = $this->getOperacionesUsuario($usuario);
		}
		$conRoles			 = $this->setRolesUsuario($usuario);
		$usuario['logueado'] = true;
		return $conRoles;
	}

	public function getUsuarios(): array
	{
		$idLogueado = Auth::user()->id;
		$usuarios = DB::table('usuarios')->selectRaw('usuarios.id')
			->join("usuario_rol", "usuarios.id", "=", "usuario_rol.idUsuario")
			->join("roles", "usuario_rol.idRol", "=", "roles.id")
			->where("usuarios.auditoriaBorrado", null)
			->where(function ($query) {
				$query->orWhere("roles.nombre", Rol::MOZO)
					->orWhere("roles.nombre", Rol::VENDEDOR)
					->orWhere("roles.nombre", Rol::COMENSAL);
			})
			->orWhere('usuarios.id', $idLogueado)
			->groupBy('usuarios.id')
			->orderByRaw('usuarios.nombre ASC')->get();
		$array = [];
		foreach ($usuarios as $item) {
			$encontrado = Usuario::where('id', $item->id)->with('roles')->first();
			if (!empty($encontrado)) {
				$usuario = $this->setRolesUsuario($encontrado);
				$array[] = $usuario;
			}
		}
		return $array;
	}
	// </editor-fold>

	// <editor-fold defaultstate="collapsed" desc="Alta de usuario">
	public function registrarUsuario(Request $request, string $tipoRegistro)
	{

		$dni               = $request['dni'] ?? '';
		$clave             = $request['password'] ?? '';
		$tipoRegistroAdmin = $tipoRegistro === "admin";

		if ($tipoRegistroAdmin) {
			$id       = Auth::user()->id;
			$logueado = Usuario::where('id', $id)->first();
			$esAdmin  = $logueado->tieneRol(Rol::ADMIN);
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
			$usuario		 = new Usuario();
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
		} catch (Exception $e) {
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

	// </editor-fold>

	// <editor-fold defaultstate="collapsed" desc="Borrado de usuario">
	public function borrarUsuario(int $id): Resultado
	{
		$resultado = new Resultado();
		try {
			DB::beginTransaction();
			$usuario = $this->getUsuario($id);
			if (empty($usuario)) {
				$resultado->agregarError(Resultado::ERROR_NO_ENCONTRADO, "No se ha encontrado el usuario a borrar");
				return $resultado;
			}
			$usuario->delete();
			$resultado->agregarMensaje("El usuario se ha borrado con éxito.");
			DB::commit();
		} catch (Throwable $t) {
			DB::rollback();
			$resultado->agregarError(Resultado::ERROR_NO_ENCONTRADO, "Hubo un error inesperado al borrar el usuario.");
			Log::info("Hubo un error al borrar el usuario: " . (string) $t);
		}
		return $resultado;
	}
	// </editor-fold>

	// <editor-fold defaultstate="collapsed" desc="Editar usuario">
	/**
	 * Si $admin es true verificamos que el usuario logueado sea admin ya que
	 * el usuario logueado está editando otro usuario con los roles.
	 *
	 * @param array $usuarioArray
	 * @param bool $admin
	 * @return ResponseFactory|Application|\Symfony\Component\HttpFoundation\Response
	 */
	public function updateUsuario(array $usuarioArray, bool $admin = false)
	{
		if ($admin) {
			$idLogueado = Auth::user()->id;
			$usuario	= Usuario::find($idLogueado);
			$esAdmin	= $usuario->tieneRol(Rol::ADMIN);
			if (!$esAdmin) {
				return Response::json(array(
					'code' => 401,
					'message' => "No está autorizado para editar usuarios."
				), 401);
			}
		}
		$idUsuario = (int) $usuarioArray["id"] ?? 0;
		if ($idUsuario <= 0) {
			return Response::json(array(
				'code' => 401,
				'message' => "No se ha encontrado el usuario a editar."
			), 401);
		}
		$usuario = Usuario::find($idUsuario);
		if (empty($usuario)) {
			return Response::json(array(
				'code' => 404,
				'message' => "No se ha encontrado el usuario a editar."
			), 401);
		}
		$resultado = $this->agregarRolesUsuario($usuario, $usuarioArray, $admin);
		if ($resultado->error()) {
			$errores = $resultado->getMensajesError();
			return Response::json(array(
				'code' => 500,
				'message' => "Hubo un error al actualizar el usuario: $errores"
			), 500);
		}
		if (isset($usuarioArray['dni'])) {
			$dni		  = (int) $usuarioArray['dni'];
			$repetido     = $this->getUsuarioPorDni($dni);
			if ($repetido !== null && $repetido->id !== $idUsuario) {
				return Response::json(array(
					'code' => 500,
					'message' => "Ya existe un usuario con ese dni."
				), 500);
			}
			$usuario->dni = $dni > 0 ? $dni : null;
		}
		if (isset($usuarioArray['nombre']) && is_string($usuarioArray['nombre'])) {
			$usuario->nombre = $usuarioArray['nombre'];
		}
		if (isset($usuarioArray['password'])) {
			$usuario->password = Hash::make($usuarioArray['password']);
		}
		$usuario->tokenReset      = null;
		$usuario->fechaTokenReset = null;
		$usuario->save();
		$usuario = Usuario::find($idUsuario);
		$usuario['tipoRuta'] = $admin ? 'admin' : 'comun';
		return response(['usuario' => $usuario]);
	}
	// </editor-fold>

	// <editor-fold defaultstate="collapsed" desc="Gestión de roles">
	/**
	 * Agrega un rol al usuario
	 * 
	 * @param Usuario $usuario
	 * @param string $rol
	 * @return Resultado
	 */
	protected function agregarRol(Usuario $usuario, string $rol): Resultado
	{
		$resultado = new Resultado();
		if ($rol === Rol::ADMIN) {
			return $resultado;
		}
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
	 * Borra todos los roles del usuario
	 * 
	 * @param Usuario $usuario
	 * @return Resultado
	 */
	protected function borrarRoles(Usuario $usuario): Resultado
	{
		$roles	   = $usuario->roles;
		$resultado = new Resultado();
		foreach ($roles as $rol) {
			$idRol		  = $rol->id;
			$idUsuario	  = $usuario->id;
			$usuarioRoles = UsuarioRol::where([['idRol', $idRol], ['idUsuario', $idUsuario]])->get();
			foreach ($usuarioRoles as $usuarioRol) {
				if ($usuarioRol instanceof UsuarioRol) {
					$rol		  = $usuarioRol->rol;
					$nombre		  = $rol->nombre;
					$noEsRolAdmin = $nombre !== Rol::ADMIN;
					if ($noEsRolAdmin) {
						$usuarioRol->delete();
					}
				} else {
					$resultado->agregarError(Resultado::ERROR_GENERICO, "No se ha podido borrar el rol $rol");
				}
			}
		}
		return $resultado;
	}

	/**
	 * Agrega los roles al usuario según la request
	 * 
	 * @param Usuario $usuario
	 * @param array $usuarioArray
	 * @param bool $tipoRegistroAdmin
	 * @return Resultado
	 */
	protected function agregarRolesUsuario(Usuario $usuario, array $usuarioArray, bool $tipoRegistroAdmin): Resultado
	{
		$resultado = new Resultado();
		try {
			if (!$tipoRegistroAdmin) {
				return $resultado;
			}
			$borrados  = $this->borrarRoles($usuario);
			if ($borrados->error()) {
				return $borrados;
			}
			$esComensal = isset($usuarioArray['esComensal']) && $usuarioArray['esComensal'] || !$tipoRegistroAdmin;
			if ($esComensal) {
				$comensalAgregado = $this->agregarRol($usuario, Rol::COMENSAL);
				if ($comensalAgregado->error()) {
					$resultado->fusionar($comensalAgregado);
				}
			}
			$resultado->fusionar($borrados);
			$esAdmin = isset($usuarioArray['esAdmin']) && $usuarioArray['esAdmin'];
			if ($esAdmin) {
				$adminAgregado = $this->agregarRol($usuario, Rol::ADMIN);
				if ($adminAgregado->error()) {
					$resultado->fusionar($adminAgregado);
				}
			};
			$esMozo = isset($usuarioArray['esMozo']) && $usuarioArray['esMozo'];
			if ($esMozo) {
				$mozoAgregado = $this->agregarRol($usuario, Rol::MOZO);
				if ($mozoAgregado->error()) {
					$resultado->fusionar($mozoAgregado);
				}
			}
			$esVendedor = isset($usuarioArray['esVendedor']) && $usuarioArray['esVendedor'];
			if ($esVendedor) {
				$vendedorAgregado = $this->agregarRol($usuario, Rol::VENDEDOR);
				if ($vendedorAgregado->error()) {
					$resultado->fusionar($vendedorAgregado);
				}
			}
		} catch (Throwable $t) {
			$resultado->agregarError(Resultado::ERROR_GENERICO, (string) $t);
		}

		return $resultado;
	}

	/**
	 * Setea los roles del usuario
	 * 
	 * @param Usuario $usuario
	 * @return Usuario
	 */
	protected function setRolesUsuario(Usuario $usuario): Usuario
	{
		$roles = $usuario->roles;
		foreach ($roles as $rol) {
			if ($rol->nombre === Rol::ADMIN) {
				$usuario['esAdmin'] = true;
				continue;
			}
			if ($rol->nombre === Rol::MOZO) {
				$usuario['esMozo'] = true;
				continue;
			}
			if ($rol->nombre === Rol::VENDEDOR) {
				$usuario['esVendededor'] = true;
				continue;
			}
			if ($rol->nombre === Rol::COMENSAL) {
				$usuario['esComensal'] = true;
				continue;
			}
		}
		return $usuario;
	}
	// </editor-fold>

	// <editor-fold defaultstate="collapsed" desc="Operaciones">

	/**
	 * Devuelve las posibles operaciones del usuario
	 * 
	 * @param Usuario $usuario
	 * @return array
	 */
	protected function getOperacionesUsuario(Usuario $usuario): array
	{
		$esAdmin          = $usuario['esAdmin'];
		$operaciones      = [];
		$operacionesAdmin = [];
		if ($esAdmin) {
			$operacionesAdmin = $this->getOperacionesAdmin();
		}
		$operaciones = array_merge($operaciones, $operacionesAdmin);
		return $operaciones;
	}

	/**
	 * Devuelve las operaciones de un usuario administrador
	 * 
	 * @return array
	 */
	protected function getOperacionesAdmin(): array
	{
		return [
			[
				'ruta'        => '/productos/listar/admin',
				'icono'       => '',
				'rol'         => Rol::ADMIN,
				'titulo'      => 'Productos',
				'descripcion' => 'Permite gestionar los productos del sistema',
			],
			[
				'ruta'        => '/usuarios/listar',
				'icono'       => '',
				'rol'         => Rol::ADMIN,
				'titulo'      => 'Usuarios',
				'descripcion' => 'Permite gestionar los usuarios del sistema',
			],
			[
				'ruta'        => '/compras',
				'icono'       => '',
				'rol'         => Rol::ADMIN,
				'titulo'      => 'Ingreso',
				'descripcion' => 'Permite ingresar mercadería'
			]
		];
	}
	// </editor-fold>

}

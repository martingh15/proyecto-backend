<?php

namespace App\Services;

use App\Modelo\Producto\Categoria;
use App\Modelo\Producto\Producto;
use App\Resultado\Resultado;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Intervention\Image\Facades\Image;
use Throwable;
use Validator;

class ProductoService
{

	// <editor-fold defaultstate="collapsed" desc="Búsquedas">

	public function getProducto(int $id): ?Producto
	{
		$producto = Producto::where([['id', $id]])->first();
		return $producto;
	}

	public function getProductos()
	{
		$productos = Producto::where('habilitado', 1)->with('categoria')->get();
		foreach ($productos as $producto) {
			$precio		 = $producto->precioVigente;
			$precioTexto = number_format($precio, 2, ",", ".");
			$categoria	 = $producto->categoria;
			$nombre		 = $categoria->nombre;

			$producto['precioTexto']	 = "$ $precioTexto";
			$producto['categoriaNombre'] = $nombre;
		}
		return $productos;
	}

	public function getCategorias()
	{
		return Categoria::where('habilitado', 1)->with('productos')->get();
	}
	// </editor-fold>

	// <editor-fold defaultstate="collapsed" desc="Alta de productos">
	public function guardarProducto(Request $request, int $id = null): Resultado
	{
		$resultado   = new Resultado();
		try {
			DB::beginTransaction();
			$objeto		   = $request['producto'] ?? '';
			$productoArray = json_decode($objeto, true);
			$nombre		   = $productoArray['nombre'] ?? '';
			$descripcion   = $productoArray['descripcion'] ?? '';
			$nuevoPrecio   = (float) $productoArray['precioVigente'] ?? 0;
			$idCategoria   = (int) $productoArray['categoria_id'] ?? 0;

			if (empty($nombre)) {
				$resultado->agregarError(Resultado::ERROR_GUARDADO, "Debe ingresar el nombre del producto.");
			}
			$categoria = Categoria::find($idCategoria);
			if (empty($categoria)) {
				$resultado->agregarError(Resultado::ERROR_GUARDADO, "Debe seleccionar una categoría del producto.");
			}
			if ($nuevoPrecio <= 0) {
				$resultado->agregarError(Resultado::ERROR_GUARDADO, "Debe indicar el precio del producto.");
			}

			if ($resultado->error()) {
				return $resultado;
			}

			$producto = new Producto();
			if ($id !== null && is_numeric($id) && $id > 0) {
				$producto = $this->getProducto($id);
			}
			if ($producto === null) {
				$resultado->agregarError(Resultado::ERROR_GUARDADO, "No se ha encontrado el producto a editar.");
				return $resultado;
			}

			$producto->nombre		= $nombre;
			$producto->categoria_id = $categoria->id;
			$producto->descripcion  = $descripcion;
			$producto->save();
			$producto->agregarPrecio($nuevoPrecio);


			$imagen	= $request->file('imagen');
			if ($imagen === null) {
				DB::commit();
				return $resultado;
			}
			$creada = $this->crearImagen($producto, $imagen);
			$creada->fusionar($resultado);
			if ($creada->error()) {
				return $creada;
			}
			$nuevo = $creada->getResultado();
			if (!$nuevo instanceof Producto) {
				$resultado->agregarError(Resultado::ERROR_GENERICO, "Ha ocurrido un error al guardar el producto.");
				return $resultado;
			}
			$nuevo->save();
			DB::commit();
		} catch (Throwable $t) {
			$resultado->agregarError(Resultado::ERROR_GENERICO, "Ha ocurrido un error al guardar el producto.");
			Log::info($t);
		}
		return $resultado;
	}

	protected function crearImagen(Producto $producto, $imagen): Resultado
	{
		$resultado = new Resultado();
		try {
			//custom mensajes en las validaciones.
			$messages = [
				'image.mimes'	 => 'La imagen debe ser .png, .jpg, .jpeg o .gif',
				'image.max'		 => "La imagen debe teber un tamaño menor a 10Mb",
				'image.uploaded' => "Hubo un error al guardar la imagen, intente con una imagen de menor tamaño"
			];

			//creamos un array con la imagen para validad.
			$imgArray = array('image' => $imagen);

			//ponemos reglas de validación
			$rules = array(
				'image' => 'max:10000|mimes:jpeg,jpg,png,gif'
			);

			//llamamos al validator con la imagen las reglas y los custom mensajes
			$validator = Validator::make($imgArray, $rules, $messages);
			//Chequeamos las validaciones.
			if ($validator->fails()) {
				$mensajes = $validator->errors()->getMessages();
				$mensaje  = implode(PHP_EOL, $mensajes['image']);
				$resultado->agregarError(Resultado::ERROR_GENERICO, "$mensaje");
				return $resultado;
			}

			//nombre de la imagen con idUnico-idGremio, obtengo la extension original del archivo
			$fileName = "$producto->id-" . uniqid() . "." . $imagen->getClientOriginalExtension();
			$carpeta  = public_path() . '\img\productos\\' . $fileName;
			$img	  = Image::make($imagen);

			//Altura de la imagen a redimensionar en px
			$height = 600;
			//Redimensiono la imagen manteniedno aspectRatio
			$img->resize(null, $height, function ($constraint) {
				$constraint->aspectRatio();
				$constraint->upsize();
			});

			//Guardo la imagen
			$nombreOriginal = $imagen->getClientOriginalName();
			if ($producto->fileImagen !== $nombreOriginal) {
				$producto->imagen     = $fileName;
				$producto->fileImagen = $nombreOriginal;
				$img->save($carpeta);
				if ($resultado->error() && $img instanceof Image) {
					$img->destroy();
				}
			}
			$resultado->setResultado($producto);
			return $resultado;
		} catch (Throwable $exception) {
			Log::info('Error imagen:' . $exception);
			$resultado->agregarError(Resultado::ERROR_GUARDADO, "Hubo un error al guardar la imagen.");
		}
		return $resultado;
	}

	// </editor-fold>

	// <editor-fold defaultstate="collapsed" desc="Borrado de productos">
	public function borrarProducto(int $id): Resultado
	{
		try {
			DB::beginTransaction();
			$producto  = $this->getProducto($id);
			$resultado = new Resultado();
			if (empty($producto)) {
				$resultado->agregarError(Resultado::ERROR_NO_ENCONTRADO, "No se ha encontrado el producto a borrar");
				return $resultado;
			}
			array_map('unlink', glob(public_path() . "/img/productos/$id-*"));
			$producto->delete();
			DB::commit();
		} catch (Throwable $t) {
			DB::rollback();
			$resultado->agregarError(Resultado::ERROR_NO_ENCONTRADO, "Hubo un error al borrar el producto.");
			Log::info("Hubo un error al borrar el producto: " . (string) $t);
		}
		return $resultado;
	}
	// </editor-fold>

}

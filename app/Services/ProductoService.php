<?php

namespace App\Services;

use App\Modelo\Producto\Categoria;
use App\Modelo\Producto\Producto;
use App\Resultado\Resultado;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Intervention\Image\Facades\Image;
use Throwable;

class ProductoService  {
	
	// <editor-fold defaultstate="collapsed" desc="Búsquedas">
	public function getProductos() {
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
	
	public function getCategorias() {
		return Categoria::where('habilitado', 1)->with('productos')->get();
	}	
	// </editor-fold>
	
	public function guardarProducto(Request $request): Resultado {
		$resultado   = new Resultado();
		try {
			DB::beginTransaction();
			$nombre		   = $request['nombre'] ?? '';
			$descripcion   = $request['descripcion'] ?? '';
			$precioVigente = (float) $request['precioVigente'] ?? 0;
			$idCategoria   = (int) $request['idCategoria'] ?? 0;
			
			if (empty($nombre)) {
				$resultado->agregarError(Resultado::ERROR_GUARDADO, "Debe ingresar el nombre del producto.");
			}
			$categoria = Categoria::find($idCategoria);
			if (empty($categoria)) {
				$resultado->agregarError(Resultado::ERROR_GUARDADO, "Debe seleccionar una categoría del producto.");
			}
			if ($precioVigente <= 0) {
				$resultado->agregarError(Resultado::ERROR_GUARDADO, "Debe indicar el precio del producto.");
			}

			$producto				 = new Producto();
			$producto->nombre		 = $nombre;
			$producto->categoria_id  = $categoria->id;
			$producto->descripcion   = $descripcion;
			$producto->precioVigente = $precioVigente;
			
			$imagen = $request->file('imagen');
			$creada = $this->crearImagen($producto, $imagen);
			$resultado->fusionar($creada);
			if ($resultado->error()) {
				return $resultado;
			}
			$producto->save();
			DB::commit();
		} catch (Throwable $t) {
			$resultado->agregarError(Resultado::ERROR_GENERICO, "Ha ocurrido un error al guardar el producto.");
			\Log::info($t);
		}
		return $resultado;
	}
	
	protected function crearImagen(Producto $producto, $imagen): Resultado {
		$resultado = new Resultado();
		try {
			//custom mensajes en las validaciones.
			$messages = [
				'image.mimes'	 => 'La imagen debe ser .png, .jpg, .jpeg o .gif',
				'image.max'		 => "La imagen debe teber un tamaño menor a 2MB",
				'image.uploaded' => "Ocurrió un error al intentar subir la imagen"
			];

			//creamos un array con la imagen para validad.
			$imgArray = array('image' => $imagen);

			//ponemos reglas de validación
			$rules = array(
				'image' => 'mimes:jpeg,jpg,png,gif|max:2000'
			);

			//llamamos al validator con la imagen las reglas y los custom mensajes
			$validator = Validator::make($imgArray, $rules, $messages);

			//Chequeamos las validaciones.
			if ($validator->fails()) {
				$mensajes = $validator->errors()->getMessages();
				$mensaje  = implode(PHP_EOL, $mensajes['image']);
				$resultado->agregarError(Resultado::ERROR_GENERICO, "Hubo un error al guardar la imagen del producto: $mensaje");
				return $resultado;
			}

			//nombre de la imagen con idUnico-idGremio, obtengo la extension original del archivo
			$fileName = "$producto->id-" . uniqid() . "." . $imagen->getClientOriginalExtension();
			$carpeta  = public_path() . '/img/animales/' . $fileName;
			$img	  = Image::make($imagen);

			//Altura de la imagen a redimensionar en px
			$height = 600;
			//Redimensiono la imagen manteniedno aspectRatio
			$img->resize(null, $height, function ($constraint) {
				$constraint->aspectRatio();
				$constraint->upsize();
			});

			//Guardo la imagen
			$producto->imagen	  = $fileName;
			$producto->fileImagen = $imagen->getClientOriginalName();
			$producto->save();
			$img->save($carpeta);
		} catch (Throwable $exception) {
			$resultado->agregarError(Resultado::ERROR_GUARDADO, "Hubo un error al guardar la imagen.");
			\Log::info($exception);
		}
        return $resultado;
    }

}
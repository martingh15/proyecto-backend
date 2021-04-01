<?php

namespace App\Services;

use App\Modelo\Producto\Categoria;
use App\Modelo\Producto\Producto;
use App\Resultado\Resultado;
use Illuminate\Http\Request;

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
			if ($resultado->error()) {
				return $resultado;
			}
			

			$producto				 = new Producto();
			$producto->nombre		 = $nombre;
			$producto->categoria_id  = $categoria->id;
			$producto->descripcion   = $descripcion;
			$producto->precioVigente = $precioVigente;
			$producto->save();
		} catch (Throwable $t) {
			$resultado->agregarError(Resultado::ERROR_GENERICO, "Ha ocurrido un error al guardar el producto.");
			\Log::info($t);
		}
		return $resultado;
	}

}
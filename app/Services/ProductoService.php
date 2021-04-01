<?php

namespace App\Services;

use App\Modelo\Producto\Producto;

class ProductoService  {
	
	// <editor-fold defaultstate="collapsed" desc="Búsquedas">
	public function getProductos() {
		return Producto::where('habilitado', 1)->with('categoria')->get();
	}	
	// </editor-fold>

}
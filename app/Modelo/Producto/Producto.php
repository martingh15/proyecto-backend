<?php

namespace App\Modelo\Producto;

use App\GenericModel;

class Producto extends GenericModel {
    
	protected $table = "producto_productos";
	
	public function categoria() {
        return $this->hasOne(Categoria::class, 'id');
    }
	
}

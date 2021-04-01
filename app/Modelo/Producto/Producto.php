<?php

namespace App\Modelo\Producto;

use App\GenericModel;

class Producto extends GenericModel {
    
	protected $table = "producto_productos";
	
	/**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;
	
	public function categoria() {
        return $this->belongsTo(Categoria::class, 'categoria_id');
    }
	
}

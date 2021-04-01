<?php

namespace App\Modelo\Producto;

use App\GenericModel;

class Categoria extends GenericModel {
	
	protected $table = "producto_categorias";
	
	/**
     * The roles that belong to the user.
     */
    public function productos()
    {
       return $this->hasMany(Producto::class, "categoria_id" ,"id");
    }
    
}

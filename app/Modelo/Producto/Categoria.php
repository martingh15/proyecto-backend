<?php

namespace App\Modelo\Producto;

use App\GenericModel;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Categoria extends GenericModel {
	
	protected $table = "producto_categorias";

    /**
     * Los productos que pertenecen a la categoría.
     *
     * @return HasMany
     */
    public function productos() {
       return $this->hasMany(Producto::class, "categoria_id" ,"id");
    }
    
}

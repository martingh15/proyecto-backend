<?php

namespace App\Modelo\Producto;

use App\GenericModel;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Categoria extends GenericModel {

    use SoftDeletes;

    const DELETED_AT = "auditoriaBorrado";
	
	protected $table = "producto_categorias";

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = ['auditoriaBorrado'];

    /**
     * Los productos que pertenecen a la categoría.
     *
     * @return HasMany
     */
    public function productos() {
       return $this->hasMany(Producto::class, "categoria_id" ,"id");
    }
    
}

<?php

namespace App\Modelo\Producto;

use App\GenericModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Precio extends GenericModel {

    protected $table = "producto_precios";

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * El producto que pertenece al precio.
     *
     * @return BelongsTo
     */
    public function producto() {
        return $this->belongsTo(Producto::class, 'producto_id');
    }
}

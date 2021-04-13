<?php

namespace App\Modelo\Pedido;

use App\GenericModel;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Pedido extends GenericModel {

    protected $table = "pedido_pedidos";

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * Las líneas que pertenecen al producto.
     *
     * @return HasMany
     */
    public function lineas() {
        return $this->hasMany(Linea::class, "pedido_id" ,"id");
    }

    /**
     * Los estados que pertenecen al producto.
     *
     * @return HasMany
     */
    public function estados() {
        return $this->hasMany(Estado::class, "pedido_id" ,"id");
    }
}

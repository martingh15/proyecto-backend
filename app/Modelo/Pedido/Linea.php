<?php

namespace App\Modelo\Pedido;

use App\GenericModel;
use App\Modelo\Producto\Producto;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Linea extends GenericModel {

    protected $table = "pedido_lineas";

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * El pedido de la línea.
     *
     * @return BelongsTo
     */
    public function pedido() {
        return $this->belongsTo(Pedido::class, 'pedido_id');
    }

    /**
     * El producto de la línea.
     *
     * @return BelongsTo
     */
    public function producto() {
        return $this->belongsTo(Producto::class, 'producto_id');
    }
}

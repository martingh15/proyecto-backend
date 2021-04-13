<?php

namespace App\Modelo\Pedido;

use App\GenericModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Estado extends GenericModel {

    const ABIERTO = 'abierto';

    protected $table = "pedido_estados";

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * La categoría al la cual el producto pertenece.
     *
     * @return BelongsTo
     */
    public function pedido() {
        return $this->belongsTo(Pedido::class, 'producto_id');
    }

}

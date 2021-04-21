<?php

namespace App\Modelo\Pedido;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Estado extends Model {

    const ABIERTO = 'abierto';
    const FINALIZADO = 'finalizado';

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
        return $this->belongsTo(Pedido::class, 'pedido_id');
    }

}

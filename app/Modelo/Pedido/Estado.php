<?php

namespace App\Modelo\Pedido;

use App\GenericModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $pedido_id
 * @property string $estado
 * @property DateTime $fecha
 */
class Estado extends GenericModel
{

    const ABIERTO = 'abierto';
    const FINALIZADO = 'finalizado';

    const ESTADOS = [
        self::ABIERTO,
        self::FINALIZADO
    ];

    /**
     * @var string
     */
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
    public function pedido()
    {
        return $this->belongsTo(Pedido::class, 'producto_id');
    }
}

<?php

namespace App\Modelo\Pedido;

use App\GenericModel;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property int $pedido_id
 * @property int $producto_id
 * @property int $cantidad
 * @property float $subtotal
 * @property float $total
 * @property DateTime $auditoriaCreado  
 * @property DateTime $auditoriaBorrado
 * @property DateTime $auditoriaModificado
 * @property int $auditoriaCreador_id
 * @property int $auditoriaBorradoPor_id
 * @property int $auditoriaModificadoPor_id
 */
class Linea extends GenericModel
{

    /**
     * @var string
     */
    protected $table = "pedido_lineas";

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

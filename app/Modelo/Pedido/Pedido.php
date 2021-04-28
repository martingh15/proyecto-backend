<?php

namespace App\Modelo\Pedido;

use App\GenericModel;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int $i
 * @property int $usuario_id
 * @property DateTime $fecha
 * @property string $ultimoEstado
 * @property float $total
 * @property bool $forzar
 * @property DateTime $auditoriaCreado
 * @property DateTime $auditoriaBorrado
 * @property DateTime $auditoriaModificado
 * @property int $auditoriaCreador_id
 * @property int $auditoriaBorradoPor_id
 * @property int $auditoriaModificadoPor_id
 */
class Pedido extends GenericModel
{

    /**
     * @var string
     */
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
    public function lineas()
    {
        return $this->hasMany(Linea::class, "pedido_id", "id");
    }

    /**
     * Los estados que pertenecen al producto.
     *
     * @return HasMany
     */
    public function estados()
    {
        return $this->hasMany(Estado::class, "pedido_id", "id");
    }
}

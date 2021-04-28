<?php

namespace App\Modelo\Pedido;

use App\GenericModel;
use App\Resultado\Resultado;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Auth;

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
     * Devuelve los ids de las líneas del pedido
     *
     * @return array
     */
    public function getIdsLineas(): array
    {
        $ids = [];
        $lineas = $this->lineas;
        foreach ($lineas as $linea) {
            $ids[] = $linea->id;
        }
        return $ids;
    }

    /**
     * Finaliza el pedido actual.
     *
     * @return Resultado
     */
    public function finalizar()
    {
        $resultado = new Resultado();
        try {
            $estado = new Estado();
            $estado->fecha     = new Carbon();
            $estado->pedido_id = $this->id;
            $estado->estado    = Estado::FINALIZADO;
            $estado->save();
            $this->forzar       = false;
            $this->ultimoEstado = $estado->estado;
            $this->save();
        } catch (\Throwable $exc) {
            $resultado->agregarError(Resultado::ERROR_GUARDADO, "Hubo un error al guardar el pedido.");
        }
        return $resultado;
    }

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

    /**
     * The "booted" method of the model.
     *
     * @return void
     */
    public static function boot()
    {

        parent::boot();

        static::created(function ($pedido) {
            $estado            = new Estado();
            $estado->fecha     = new Carbon();
            $estado->pedido_id = $pedido->id;
            $estado->estado    = Estado::ABIERTO;
            $estado->save();
            $pedido->ultimoEstado = $estado->estado;
            $pedido->save();
        });
    }
}

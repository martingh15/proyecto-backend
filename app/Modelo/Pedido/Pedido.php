<?php

namespace App\Modelo\Pedido;

use App\GenericModel;
use App\Resultado\Resultado;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Auth;

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

    /**
     * The "booted" method of the model.
     *
     * @return void
     */
    protected static function boot() {

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

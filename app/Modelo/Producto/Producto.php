<?php

namespace App\Modelo\Producto;

use App\GenericModel;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Producto extends GenericModel {
    
	protected $table = "producto_productos";
	
	/**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;

    public function agregarPrecio(float $nuevoPrecio): Producto {
        $anterior = $this->precioVigente;
        if ($anterior === null || floatval($anterior) !== $nuevoPrecio) {
            $precio              = new Precio();
            $precio->fecha       = new Carbon();
            $precio->precio      = $nuevoPrecio;
            $precio->producto_id = $this->id;
            $precio->save();
            $this->precioVigente = $nuevoPrecio;
            $this->save();
        }
        return $this;
    }


    public static function boot() {

        parent::boot();

        self::deleting(function ($model) {
            $model->precios()->delete();
        });
    }

    /**
     * La categoría al la cual el producto pertenece.
     *
     * @return BelongsTo
     */
	public function categoria() {
        return $this->belongsTo(Categoria::class, 'categoria_id');
    }

    /**
     * Los precios que pertenecen al producto.
     *
     * @return HasMany
     */
    public function precios() {
        return $this->hasMany(Precio::class, "producto_id" ,"id");
    }
	
}

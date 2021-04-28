<?php

namespace App\Modelo\Producto;

use App\GenericModel;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property int $id
 * @property int $categoria_id
 * @property string $nombre
 * @property string $imagen
 * @property string $fileImagen
 * @property string $descripcion
 * @property float $precioVigente
 * @property bool $habilitado
 * @property DateTime $auditoriaCreado
 * @property DateTime $auditoriaBorrado
 * @property DateTime $auditoriaModificado
 * @property int $auditoriaCreador_id
 * @property int $auditoriaBorradoPor_id
 * @property int $auditoriaModificadoPor_id
 */
class Producto extends GenericModel
{

    use SoftDeletes;

    const DELETED_AT = "auditoriaBorrado";

    protected $table = "producto_productos";

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = ['auditoriaBorrado'];

    /**
     * Agrega un nuevo precio al histórico de precios del producto con la
     * fecha actual.
     * 
     * @param float $nuevoPrecio
     * @return Producto
     */
    public function agregarPrecio(float $nuevoPrecio): Producto
    {
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

    /**
     * The "booting" method of the model.
     *
     * @return void
     */
    public static function boot()
    {

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
    public function categoria()
    {
        return $this->belongsTo(Categoria::class, 'categoria_id');
    }

    /**
     * Los precios que pertenecen al producto.
     *
     * @return HasMany
     */
    public function precios()
    {
        return $this->hasMany(Precio::class, "producto_id", "id");
    }
}

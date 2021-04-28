<?php

namespace App\Modelo;

use App\Usuario;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * @property int $id
 * @property string $nombre
 * @property string $legible
 * @property string $descripcion
 * @property bool $root
 * @property bool $habilitado
 * @property DateTime $auditoriaCreado
 * @property DateTime $auditoriaModificado
 * @property int $auditoriaCreador_id
 * @property int $auditoriaModificadoPor_id
 */
class Rol extends Model
{

    const ROOT     = 'root';
    const ADMIN    = 'admin';
    const MOZO     = 'mozo';
    const COMENSAL = 'comensal';
    const VENDEDOR = 'vendedor';

    const ROLES = [
        self::ROOT,
        self::ADMIN,
        self::MOZO,
        self::COMENSAL,
        self::VENDEDOR
    ];

    /**
     * @var string
     */
    protected $table = "roles";

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * Los usuarios que poseen este rol.
     * @return BelongsToMany
     */
    public function usuarios()
    {
        return $this->belongsToMany(Usuario::class, 'usuario_rol', 'idUsuario');
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->legible;
    }
}

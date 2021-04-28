<?php

namespace App\Modelo;

use App\Usuario;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * @property int $id
 * @property int $idRol
 * @property int $idUsuario
 */
class UsuarioRol extends Model
{

    protected $table = "usuario_rol";

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * Usuario al que pertenece.
     * @return HasOne
     */
    public function usuario()
    {
        return $this->hasOne(Usuario::class, 'id', 'idUsuario');
    }

    /**
     * Rol al que pertenece
     * @return HasOne
     */
    public function rol()
    {
        return $this->hasOne(Rol::class, 'id', 'idRol');
    }
}

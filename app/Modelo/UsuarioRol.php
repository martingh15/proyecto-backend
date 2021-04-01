<?php

namespace App\Modelo;

use App\Usuario;
use Illuminate\Database\Eloquent\Model;

class UsuarioRol extends Model {

    protected $table = "usuario_rol";

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;

    public function usuario()
    {
        return $this->hasOne(Usuario::class, 'id', 'idUsuario');
    }

    public function rol()
    {
        return $this->hasOne(Rol::class, 'id', 'idRol');
    }
}

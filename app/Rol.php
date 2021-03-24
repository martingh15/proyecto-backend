<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Rol extends Model {

    const ROL_ROOT  = 'root';
    const ROL_ADMIN = 'admin';

    protected $table = "roles";

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * The users that belong to the role.
     */
    public function users()
    {
        return $this->belongsToMany(Usuario::class, 'usuario_rol', 'idUsuario');
    }
}

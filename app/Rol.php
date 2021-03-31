<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Rol extends Model {

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
	
	public function __toString() {
		return $this->legible;
	}
}

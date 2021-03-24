<?php

namespace App;

use Carbon\Carbon;
use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;

/**
 * @property int $id
 * @property string $nombre
 * @property string $descripcion
 */
class Usuario extends Authenticatable
{
    use Notifiable;

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'nombre', 'email', 'password'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password'
    ];

    /**
     * The "booted" method of the model.
     *
     * @return void
     */
    protected static function boot()
    {

        parent::boot();

        static::creating(function ($user) {
            $hoy = Carbon::now()->setTimezone('America/Argentina/Salta')->toDateTimeString();
            $user->auditoriaCreado     = $hoy;
            $user->auditoriaModificado = $hoy;
        });
    }

    /**
     * The roles that belong to the user.
     */
    public function roles()
    {
        return $this->belongsToMany(Rol::class, 'usuario_rol', 'idUsuario', 'idRol');
    }

    public function tieneRol(string $nombre): bool {
        $roles = $this->roles;
        foreach ($roles as $rol) {
            $nombreRol = $rol['nombre'];
            if ($nombre === $nombreRol) {
                return true;
            }
        }
        return false;
    }
}

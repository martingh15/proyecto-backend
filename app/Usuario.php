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
        'nombre', 'email', 'password', 'dni'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password'
    ];

	protected $appends = [ 'esAdmin', 'esMozo', 'esVendedor' ];
	
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

    public function tieneRol(string $nombre, bool $esAdmin = false): bool {
		if ($esAdmin) {
			return true;
		}
        $roles = $this->roles;
        foreach ($roles as $rol) {
            $nombreRol = $rol['nombre'];
			$persona = $this->nombre;
            if ($nombre === $nombreRol) {
                return true;
            }
        }
        return false;
    }
	
	/**
     * Indica si el usuario es administrador
	 * 
     * @return bool
     */
    public function getEsAdminAttribute()
    {
        return $this->tieneRol(Rol::ROL_ADMIN);
    }
	
	/**
     * Indica si el usuario es mozo
	 * 
     * @return bool
     */
    public function getEsMozoAttribute()
    {
        return $this->tieneRol(Rol::ROL_MOZO);
    }
	
	/**
     * Indica si el usuario es vendedor
	 * 
     * @return bool
     */
    public function getEsVendedorAttribute()
    {
        return $this->tieneRol(Rol::ROL_VENDEDOR);
    }
}

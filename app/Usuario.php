<?php

namespace App;

use Carbon\Carbon;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Auth;

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

	protected $appends = [ 'esAdmin', 'esMozo', 'esVendedor', 'esComensal' ];
	
    /**
     * The "booted" method of the model.
     *
     * @return void
     */
    protected static function boot()
    {

        parent::boot();

        static::creating(function ($usuario) {
			$hoy						  = Carbon::now()->setTimezone('America/Argentina/Salta')->toDateTimeString();
			$logueado					  = Auth::user();
			if (!empty($logueado)) {
				$usuario->auditoriaCreador_id = $logueado->id;
			}
			$usuario->auditoriaCreado     = $hoy;
			$usuario->auditoriaModificado = null;
        });
		
		static::updating(function($usuario) {
			$logueado							= Auth::user();
            $hoy								= Carbon::now()->setTimezone('America/Argentina/Salta')->toDateTimeString();
            $usuario->auditoriaModificado		= $hoy;
			$usuario->auditoriaModificadoPor_id = $logueado->id;
			\Log::info('USUARIO MODIFICADO');
			\Log::info($usuario);
        });
    }

    /**
     * The roles that belong to the user.
     */
    public function roles()
    {
        return $this->belongsToMany(Rol::class, 'usuario_rol', 'idUsuario', 'idRol');
    }
   
	/**
     *  
     */
    public function relacionRoles()
    {
        return $this->hasMany(UsuarioRol::class, 'idUsuario');
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
	
	/**
     * Indica si el usuario es comensal
	 * 
     * @return bool
     */
    public function getEsComensalAttribute()
    {
        return $this->tieneRol(Rol::ROL_COMENSAL);
    }
	
	public function __toString() {
		return $this->nombre;
	}
}
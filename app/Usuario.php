<?php

namespace App;

use App\Modelo\Rol;
use App\Modelo\UsuarioRol;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\SoftDeletes;
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
    use Notifiable, SoftDeletes;

    const DELETED_AT = "auditoriaBorrado";

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
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = ['auditoriaBorrado'];

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
        return $this->tieneRol(Rol::ADMIN);
	}
	
	/**
     * Indica si el usuario es mozo
	 * 
     * @return bool
     */
    public function getEsMozoAttribute()
    {
        return $this->tieneRol(Rol::MOZO);
    }
	
	/**
     * Indica si el usuario es vendedor
	 * 
     * @return bool
     */
    public function getEsVendedorAttribute()
    {
        return $this->tieneRol(Rol::VENDEDOR);
    }
	
	/**
     * Indica si el usuario es comensal
	 * 
     * @return bool
     */
    public function getEsComensalAttribute()
    {
        return $this->tieneRol(Rol::COMENSAL);
    }
	
	/**
     * The "booted" method of the model.
     *
     * @return void
     */
    protected static function boot() {

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
        });

		static::deleting(function($usuario) {
            $logueado						 = Auth::user();
            $usuario->auditoriaBorradoPor_id = $logueado->id;
            $usuario->save();
        });
    }
	
	public function __toString() {
		return $this->nombre;
	}
}
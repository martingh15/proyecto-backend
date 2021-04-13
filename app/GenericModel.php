<?php

namespace App;

use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Validator;


class GenericModel extends Model {

    protected $rules = array();

    protected $errors;

    protected $messages = [
        'required' => ':attribute es un campo requerido',
        'alpha' => ':attribute debe ser solo caracteres alfabeticos',
        'numeric' => ':attribute debe ser solo caracteres numéricos',
        'url' => ':attribute debe ser una dirección web',
        'email' => ':attribute debe ser una dirección de e-mail',
        'date_format' => ':attribute debe respetar el formato de HH:MM',
        'fechaDesde.date_format' => ':attribute debe respetar el formato de AAAA-MM-DD',
        'fechaHasta.date_format' => ':attribute debe respetar el formato de AAAA-MM-DD',
        'fechaDesde.before' => ' :attribute debe ser menor a fecha hasta',
        'fechaHasta.after' => ' :attribute debe ser mayor a fecha desde',
        'url_j' => ":attribute no contiene una url válida",
        'integer' => ":attribute debe ser un numero entero",
    ];

    public function validate($data)
    {

        // make a new validator object
        $v = Validator::make($data, $this->rules, $this->messages);

        // check for failure
        if ($v->fails()) {
            // set errors and return false
            $this->errors = $v->messages();
            return false;
        }
        // validation pass
        return true;
    }

    public function errors() {
        return $this->errors;
    }

    public static function boot() {
		
        parent::boot();

        self::creating(function ($model) {
            $hoy	  = Carbon::now()->setTimezone('America/Argentina/Salta')->toDateTimeString();
			$logueado = Auth::user();
			if (!empty($logueado)) {
				$model->auditoriaCreador_id = $logueado->id;
			}
			$model->auditoriaCreado     = $hoy;
			$model->auditoriaModificado = null;
        });

        self::created(function ($model) {
            // ... code here
        });

        self::saving(function ($model) {
			// ... code here
        });

        self::updating(function ($model) {
			$logueado						  = Auth::user();
			$hoy							  = Carbon::now()->setTimezone('America/Argentina/Salta')->toDateTimeString();
			$model->auditoriaModificado		  = $hoy;
			$model->auditoriaModificadoPor_id = $logueado->id;
        });

        self::updated(function ($model) {
            // ... code here
        });

        static::deleting(function($usuario) {
            $logueado						 = Auth::user();
            $usuario->auditoriaBorradoPor_id = $logueado->id;
            $usuario->save();
        });

        self::deleted(function ($model) {
            // ... code here
        });
    }
}

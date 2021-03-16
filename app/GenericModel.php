<?php

namespace App;

use App\Exceptions\BusinessException;
use Auth;
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

    public function errors()
    {
        return $this->errors;
    }

    public static function boot()
    {
        parent::boot();

        self::creating(function ($model) {
            // ... code here
        });

        self::created(function ($model) {
            // ... code here
        });

        self::saving(function ($model) {

            //Validacion eloquent
            if (!$model->validate(json_decode($model, true))) {
                $errors = $model->errors();
                \Log::info("error");
                \Log::info($errors);
                throw new BusinessException($errors);
            }

            //fechaUltMdf
            $model->fechaUltMdf = (new \DateTime())->setTimezone(new \DateTimeZone("America/Argentina/Buenos_Aires"));

            //estado
            if ($model->estado===null){
                $model->estado = true;
            }

            //usuario ultima modificación
            if(empty($model->idUsuarioUltMdf) && empty($model->usuarioUltMdf)) {
                if (!empty(Auth::user())) {
                    $model->usuarioUltMdf = Auth::user()->nombre . " " . Auth::user()->apellido;
                    $model->idUsuarioUltMdf = Auth::user()->id;
                } else {
                    $model->usuarioUltMdf = "Administrador del Sistema";
                    $model->idUsuarioUltMdf = 0;
                }
            }

        });

        self::updating(function ($model) {
            // ... code here
        });

        self::updated(function ($model) {
            // ... code here
        });

        self::deleting(function ($model) {
            // ... code here
        });

        self::deleted(function ($model) {
            // ... code here
        });
    }
}

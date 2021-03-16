<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Menu extends Model
{
    protected $table = "menus";

    /**
     * Indicates if the model should be timestamped.
     *
     * @var bool
     */
    public $timestamps = false;

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
}

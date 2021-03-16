<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUsuariosTabla extends Migration
{
    public function up(): void {
        Schema::create('usuarios', function (Blueprint $table) {
            $table->increments('id');
            $table->string('nombre', 191);
            $table->string('email', 191)->unique();
            $table->string('tokenEmail', 191)->nullable();
            $table->string('tokenReset', 191)->nullable();
            $table->dateTime('fechaTokenReset')->nullable();
            $table->tinyInteger('habilitado')->default(0);
            $table->string('password', 191);
            $table->dateTime('auditoriaCreado');
            $table->dateTime('auditoriaModificado');
            $table->unsignedInteger('auditoriaCreador_id')->nullable();
            $table->unsignedInteger('auditoriaModificadoPor_id')->nullable();

            $table->foreign('auditoriaCreador_id')->references('id')->on('usuarios');
            $table->foreign('auditoriaModificadoPor_id')->references('id')->on('usuarios');


        });
    }

    public function down(): void {
        Schema::dropIfExists('usuarios');
    }
}

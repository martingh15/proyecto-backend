<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePreciosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('producto_precios', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('producto_id')->nullable();
            $table->dateTime('fecha');
            $table->float('precio');

            $table->foreign('producto_id')->references('id')->on('producto_productos');

            $table->dateTime('auditoriaCreado');
            $table->dateTime('auditoriaModificado')->nullable();
            $table->unsignedInteger('auditoriaCreador_id')->nullable();
            $table->unsignedInteger('auditoriaModificadoPor_id')->nullable();

            $table->foreign('auditoriaCreador_id')->references('id')->on('usuarios');
            $table->foreign('auditoriaModificadoPor_id')->references('id')->on('usuarios');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('producto_precios');
    }
}

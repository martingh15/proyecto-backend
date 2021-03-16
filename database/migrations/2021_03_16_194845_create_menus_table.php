<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMenusTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('menus', function (Blueprint $table) {
            $table->increments('id');
            $table->string('nombre');
            $table->float('precio');            
            $table->dateTime('auditoriaCreado');
            $table->dateTime('auditoriaModificado');
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
        Schema::dropIfExists('menus');
    }
}

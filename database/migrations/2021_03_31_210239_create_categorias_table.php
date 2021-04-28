<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCategoriasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
         Schema::create('producto_categorias', function (Blueprint $table) {
            $table->increments('id');
			$table->unsignedInteger('superior_id')->nullable();
			$table->string('nombre');
			$table->tinyInteger('habilitado')->default(1);

             $table->dateTime('auditoriaCreado');
             $table->dateTime('auditoriaBorrado')->nullable();
             $table->dateTime('auditoriaModificado')->nullable();
             $table->unsignedInteger('auditoriaCreador_id')->nullable();
             $table->unsignedInteger('auditoriaBorradoPor_id')->nullable();
             $table->unsignedInteger('auditoriaModificadoPor_id')->nullable();

             $table->foreign('auditoriaCreador_id')->references('id')->on('usuarios');
             $table->foreign('auditoriaBorradoPor_id')->references('id')->on('usuarios');
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
        Schema::dropIfExists('producto_categorias');
    }
}

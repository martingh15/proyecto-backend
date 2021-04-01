<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateProductosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('producto_productos', function (Blueprint $table) {
            $table->increments('id');
			$table->unsignedInteger('categoria_id')->nullable();
			$table->string('codigo');
			$table->string('nombre');
			$table->string('imagen');
			$table->string('descripcion');
			$table->float('precioVigente', 2);
			$table->tinyInteger('habilitado')->default(1);
			
			$table->foreign('categoria_id')->references('id')->on('producto_categorias');
			
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
        Schema::dropIfExists('producto_productos');
    }
}

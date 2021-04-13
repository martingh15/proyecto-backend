<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateEstadosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('pedido_estados', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('producto_id');
            $table->string('estado');
            $table->dateTime('fecha');

            $table->foreign('producto_id')->references('id')->on('producto_productos');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('pedido_estados');
    }
}

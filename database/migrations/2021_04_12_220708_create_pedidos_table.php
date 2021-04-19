<?php

use App\Modelo\Pedido\Estado;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePedidosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('pedido_pedidos', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('usuario_id');
            $table->dateTime('fecha');
            $table->string('ultimoEstado')->default(Estado::ABIERTO);
            $table->float('total')->default(0);

            $table->dateTime('auditoriaCreado');
            $table->dateTime('auditoriaBorrado')->nullable();
            $table->dateTime('auditoriaModificado')->nullable();
            $table->unsignedInteger('auditoriaCreador_id')->nullable();
            $table->unsignedInteger('auditoriaBorradoPor_id')->nullable();
            $table->unsignedInteger('auditoriaModificadoPor_id')->nullable();

            $table->foreign('usuario_id')->references('id')->on('usuarios');
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
        Schema::dropIfExists('pedido_pedidos');
    }
}

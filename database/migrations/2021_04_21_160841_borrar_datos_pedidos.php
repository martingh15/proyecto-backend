<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Migrations\Migration;

class BorrarDatosPedidos extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        DB::table('pedido_estados')->delete();
        DB::table('pedido_lineas')->delete();
        DB::table('pedido_pedidos')->delete();
    }
}

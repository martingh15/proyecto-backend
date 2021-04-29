<?php

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Migrations\Migration;

class InsertarCategorias extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        $hoy = Carbon::now()->toDateTimeString();
        DB::table('producto_categorias')->insert(
            array(
                'id' => null,
                'superior_id' => null,
                'nombre' => 'Hamburguesas',
                'habilitado' => 1,
                'auditoriaCreado' => "$hoy",
                'auditoriaBorrado' => null,
                'auditoriaModificado' => null,
                'auditoriaCreador_id' => null,
                'auditoriaBorradoPor_id' => null,
                'auditoriaModificadoPor_id' => null
            )
        );
        DB::table('producto_categorias')->insert(
            array(
                'id' => null,
                'superior_id' => null,
                'nombre' => 'Bebidas',
                'habilitado' => 1,
                'auditoriaCreado' => "$hoy",
                'auditoriaBorrado' => null,
                'auditoriaModificado' => null,
                'auditoriaCreador_id' => null,
                'auditoriaBorradoPor_id' => null,
                'auditoriaModificadoPor_id' => null
            )
        );
        DB::table('producto_categorias')->insert(
            array(
                'id' => null,
                'superior_id' => null,
                'nombre' => 'Pizzas',
                'habilitado' => 1,
                'auditoriaCreado' => "$hoy",
                'auditoriaBorrado' => null,
                'auditoriaModificado' => null,
                'auditoriaCreador_id' => null,
                'auditoriaBorradoPor_id' => null,
                'auditoriaModificadoPor_id' => null
            )
        );
        DB::table('producto_categorias')->insert(
            array(
                'id' => null,
                'superior_id' => null,
                'nombre' => 'Cervezas en lata',
                'habilitado' => 1,
                'auditoriaCreado' => "$hoy",
                'auditoriaBorrado' => null,
                'auditoriaModificado' => null,
                'auditoriaCreador_id' => null,
                'auditoriaBorradoPor_id' => null,
                'auditoriaModificadoPor_id' => null
            )
        );
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        DB::table('producto_categorias')->delete();
    }
}

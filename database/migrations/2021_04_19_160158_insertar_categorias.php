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
                'codigo' => 'H1',
                'nombre' => 'Hamburguesas',
                'orden' => 1,
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
                'codigo' => 'B1',
                'nombre' => 'Bebidas',
                'orden' => 1,
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
                'codigo' => 'P1',
                'nombre' => 'Pizzas',
                'orden' => 1,
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
                'codigo' => 'C1',
                'nombre' => 'Cervezas en lata',
                'orden' => 1,
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

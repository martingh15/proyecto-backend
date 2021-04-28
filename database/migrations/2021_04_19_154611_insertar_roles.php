<?php

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class InsertarRoles extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        $hoy = Carbon::now()->toDateTimeString();
        DB::table('roles')->insert(
            array(
                'id' => null,
                'nombre' => 'root',
                'legible' => 'Root',
                'descripcion' => 'Rol con todos los permisos.',
                'root' => 1,
                'habilitado' => 1,
                'auditoriaCreado' => "$hoy",
                'auditoriaModificado' => "$hoy",
                'auditoriaCreador_id' => null,
                'auditoriaModificadoPor_id' => null
            )
        );
        DB::table('roles')->insert(
            array(
                'id' => null,
                'nombre' => 'admin',
                'legible' => 'Administrador',
                'descripcion' => 'Administrador del sistema. Se encarga del ingreso y creación de usuarios.',
                'root' => 1,
                'habilitado' => 1,
                'auditoriaCreado' => "$hoy",
                'auditoriaModificado' => "$hoy",
                'auditoriaCreador_id' => null,
                'auditoriaModificadoPor_id' => null
            )
        );
        DB::table('roles')->insert(
            array(
                'id' => null,
                'nombre' => 'mozo',
                'legible' => 'Mozo',
                'descripcion' => 'Persona que puede gestiona las mesas.',
                'root' => 1,
                'habilitado' => 1,
                'auditoriaCreado' => "$hoy",
                'auditoriaModificado' => "$hoy",
                'auditoriaCreador_id' => null,
                'auditoriaModificadoPor_id' => null
            )
        );
        DB::table('roles')->insert(
            array(
                'id' => null,
                'nombre' => 'comensal',
                'legible' => 'Comensal',
                'descripcion' => 'Usuario que realiza pedidos online.',
                'root' => 1,
                'habilitado' => 1,
                'auditoriaCreado' => "$hoy",
                'auditoriaModificado' => "$hoy",
                'auditoriaCreador_id' => null,
                'auditoriaModificadoPor_id' => null
            )
        );
        DB::table('roles')->insert(
            array(
                'id' => null,
                'nombre' => 'vendedor',
                'legible' => 'Vendedor',
                'descripcion' => 'Usuario que realiza ventas desde la caja.',
                'root' => 1,
                'habilitado' => 1,
                'auditoriaCreado' => "$hoy",
                'auditoriaModificado' => "$hoy",
                'auditoriaCreador_id' => null,
                'auditoriaModificadoPor_id' => null
            )
        );
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::table('roles')->delete();
    }
}

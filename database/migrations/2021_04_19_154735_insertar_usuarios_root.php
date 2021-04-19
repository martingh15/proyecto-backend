<?php

use App\Modelo\Rol;
use App\Usuario;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class InsertarUsuariosRoot extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        $hoy = Carbon::now()->toDateTimeString();
        DB::table('usuarios')->insert(
            array(
                'id' => null,
                'nombre' => 'Martín',
                'email' => 'martinghiotti2013@gmail.com',
                'dni' => 36860948,
                'tokenEmail' => null,
                'tokenReset' => null,
                'fechaTokenReset' => null,
                'habilitado' => 1,
                'password' => "$10$0hakHJWsP2G1aMeqJj0NHumx9rZyMPlDSUqBLt5r6rA2yJjDQZujO",
                'auditoriaCreado' => "$hoy",
                'auditoriaBorrado' => null,
                'auditoriaModificado' => null,
                'auditoriaCreador_id' => null,
                'auditoriaBorradoPor_id' => null,
                'auditoriaModificadoPor_id' => null
            )
        );
        DB::table('usuarios')->insert(
            array(
                'id' => null,
                'nombre' => 'Bernardo',
                'email' => 'bernardopolidoro@gmail.com',
                'dni' => null,
                'tokenEmail' => null,
                'tokenReset' => null,
                'fechaTokenReset' => null,
                'habilitado' => 1,
                'password' => "$10$0hakHJWsP2G1aMeqJj0NHumx9rZyMPlDSUqBLt5r6rA2yJjDQZujO",
                'auditoriaCreado' => "$hoy",
                'auditoriaBorrado' => null,
                'auditoriaModificado' => null,
                'auditoriaCreador_id' => null,
                'auditoriaBorradoPor_id' => null,
                'auditoriaModificadoPor_id' => null
            )
        );
        DB::table('usuarios')->insert(
            array(
                'id' => null,
                'nombre' => 'Administrador',
                'email' => 'administrador@gmail.com',
                'dni' => null,
                'tokenEmail' => null,
                'tokenReset' => null,
                'fechaTokenReset' => null,
                'habilitado' => 1,
                'password' => "$10$0hakHJWsP2G1aMeqJj0NHumx9rZyMPlDSUqBLt5r6rA2yJjDQZujO",
                'auditoriaCreado' => "$hoy",
                'auditoriaBorrado' => null,
                'auditoriaModificado' => null,
                'auditoriaCreador_id' => null,
                'auditoriaBorradoPor_id' => null,
                'auditoriaModificadoPor_id' => null
            )
        );
        $rolRoot  = Rol::where('nombre', Rol::ROOT)->first();
        $rolAdmin = Rol::where('nombre', Rol::ADMIN)->first();
        $admin    = Usuario::where('nombre', 'Administrador')->first();
        $martin   = Usuario::where('nombre', 'Martín')->first();
        $bernardo = Usuario::where('nombre', 'Bernardo')->first();
        DB::table('usuario_rol')->insert(
            array(
                'id' => null,
                'idRol' => $rolAdmin->id,
                'idUsuario' => $martin->id
            )
        );
        DB::table('usuario_rol')->insert(
            array(
                'id' => null,
                'idRol' => $rolRoot->id,
                'idUsuario' => $martin->id
            )
        );
        DB::table('usuario_rol')->insert(
            array(
                'id' => null,
                'idRol' => $rolAdmin->id,
                'idUsuario' => $bernardo->id
            )
        );
        DB::table('usuario_rol')->insert(
            array(
                'id' => null,
                'idRol' => $rolRoot->id,
                'idUsuario' => $bernardo->id
            )
        );
        DB::table('usuario_rol')->insert(
            array(
                'id' => null,
                'idRol' => $rolAdmin->id,
                'idUsuario' => $admin->id
            )
        );
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        DB::table('usuario_rol')->delete();
        DB::table('usuarios')->delete();
    }
}

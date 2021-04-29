<?php

use App\Modelo\Producto\Categoria;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Migrations\Migration;

class InsertarProductos extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        $hoy = Carbon::now()->toDateTimeString();
        $hamburguesas = Categoria::where('nombre', 'Hamburguesas')->first();
        DB::table('producto_productos')->insert(
            array(
                'id'              => null,
                'nombre'          => 'Stacker Simple',
                'categoria_id'    => $hamburguesas->id,
                'imagen'          => 'ssimple.jpeg',
                'fileImagen'      => 'ssimple.jpeg',
                'descripcion'     => 'Medallon simple de carne, cheddar, panceta y aderezo stacker.',
                'precioVigente'   => 560,
                'habilitado'      => 1,
                'auditoriaCreado' => "$hoy",
                'auditoriaBorrado' => null,
                'auditoriaModificado' => null,
                'auditoriaCreador_id' => null,
                'auditoriaBorradoPor_id' => null,
                'auditoriaModificadoPor_id' => null
            )
        );
        DB::table('producto_productos')->insert(
            array(
                'id'              => null,
                'nombre'          => 'Stacker Doble',
                'categoria_id'    => $hamburguesas->id,
                'imagen'          => 'sdoble.jpeg',
                'fileImagen'      => 'sdoble.jpeg',
                'descripcion'     => 'Doble medallon, cheddar, panceta y aderezo stacker.',
                'precioVigente'   => 630,
                'habilitado'      => 1,
                'auditoriaCreado' => "$hoy",
                'auditoriaBorrado' => null,
                'auditoriaModificado' => null,
                'auditoriaCreador_id' => null,
                'auditoriaBorradoPor_id' => null,
                'auditoriaModificadoPor_id' => null
            )
        );
        DB::table('producto_productos')->insert(
            array(
                'id'              => null,
                'nombre'          => 'Stacker Triple',
                'categoria_id'    => $hamburguesas->id,
                'imagen'          => 'striple.jpeg',
                'fileImagen'      => 'striple.jpeg',
                'descripcion'     => 'Triple medallon, queso cheddar, panceta y aderezo Stacker.',
                'precioVigente'   => 750,
                'habilitado'      => 1,
                'auditoriaCreado' => "$hoy",
                'auditoriaBorrado' => null,
                'auditoriaModificado' => null,
                'auditoriaCreador_id' => null,
                'auditoriaBorradoPor_id' => null,
                'auditoriaModificadoPor_id' => null
            )
        );
        DB::table('producto_productos')->insert(
            array(
                'id'              => null,
                'nombre'          => 'Cheeseburger',
                'categoria_id'    => $hamburguesas->id,
                'imagen'          => 'cheeseburguer.jpg',
                'fileImagen'      => 'cheeseburguer.jpg',
                'descripcion'     => 'Medallón simple, queso cheddar, ketchup, cebolla morada & mostaza. (Con papas rústicas).',
                'precioVigente'   => 750,
                'habilitado'      => 1,
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
        DB::table('producto_productos')->delete();
    }
}

<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class UsuariosRoot extends AbstractMigration
{
    public function up(): void {
        $hoy = \Carbon\Carbon::now()->format('Y-m-d H:i');
        $this->execute("INSERT INTO usuarios VALUES (default, 'Martín', 'martinghiotti2013@gmail.com', null, null, null, null, 1, '$10$0hakHJWsP2G1aMeqJj0NHumx9rZyMPlDSUqBLt5r6rA2yJjDQZujO', '$hoy', null, null, null, null, null);");
        $this->execute("INSERT INTO usuarios VALUES (default, 'Bernardo', 'bernardopolidoro@gmail.com', null, null, null, null, 1, '$10$0hakHJWsP2G1aMeqJj0NHumx9rZyMPlDSUqBLt5r6rA2yJjDQZujO',  '$hoy', null, null, null, null, null);");
        $this->execute("INSERT INTO usuarios VALUES (default, 'Administrador', 'administrador@gmail.com', null, null, null, null, 1, '$10$0hakHJWsP2G1aMeqJj0NHumx9rZyMPlDSUqBLt5r6rA2yJjDQZujO', '$hoy', null, null, null, null, null);");
        $this->execute("INSERT INTO usuario_rol VALUES (
				default,
                (SELECT id FROM roles WHERE nombre = 'root'),
                (SELECT id FROM usuarios WHERE nombre = 'Martín')
            )
        ");
        $this->execute("INSERT INTO usuario_rol VALUES (
				default,
                (SELECT id FROM roles WHERE nombre = 'admin'),
                (SELECT id FROM usuarios WHERE nombre = 'Martín')
            )
        ");
        $this->execute("INSERT INTO usuario_rol VALUES (
				default,
                (SELECT id FROM roles WHERE nombre = 'root'),
                (SELECT id FROM usuarios WHERE nombre = 'Bernardo')
            )
        ");
        $this->execute("INSERT INTO usuario_rol VALUES (
				default,
                (SELECT id FROM roles WHERE nombre = 'admin'),
                (SELECT id FROM usuarios WHERE nombre = 'Bernardo')
            )
        ");
        $this->execute("INSERT INTO usuario_rol VALUES (
				default,
                (SELECT id FROM roles WHERE nombre = 'admin'),
                (SELECT id FROM usuarios WHERE nombre = 'Administrador')
            )
        ");
        $this->execute("INSERT INTO producto_categorias VALUES(default, null, 'A1', 'Almacén', 1, 1, '2021-09-04 17:44', null, null, null, null, null);");
    }

    public function down(): void {
        $table = $this->table('usuarios');

        if ($table->hasForeignKey('auditoriaCreador_id')) {
            $table->dropForeignKey('auditoriaCreador_id');
        }
        if ($table->hasForeignKey('auditoriaBorradoPor_id')) {
            $table->dropForeignKey('auditoriaBorradoPor_id');
        }
        if ($table->hasForeignKey('auditoriaModificadoPor_id')) {
            $table->dropForeignKey('auditoriaModificadoPor_id');
        }
        $table->save();
        $this->execute("DELETE FROM usuario_rol;");
        $this->execute("DELETE FROM usuarios;");
    }
}

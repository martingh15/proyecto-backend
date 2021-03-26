<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class AgregarRoles extends AbstractMigration
{
    public function up(): void {
        $hoy = \Carbon\Carbon::now()->format('Y-m-d H:i');
        $this->execute("INSERT INTO roles VALUES(default, 'root', 'Root', 'Rol con todos los permisos', 1, 1, '$hoy', '$hoy', null, null);");
        $this->execute("INSERT INTO roles VALUES(default, 'admin', 'Administrador', 'Administrador del sistema. Se encarga del ingreso y creación de usuarios', 0, 1, '$hoy', '$hoy', null, null);");
        $this->execute("INSERT INTO roles VALUES(default, 'mozo', 'Mozo', 'Persona que puede gestiona las mesas', 0, 1, '$hoy', '$hoy', null, null);");
        $this->execute("INSERT INTO roles VALUES(default, 'comensal', 'Comensal', 'Usuario que realiza pedidos online', 0, 1, '$hoy', '$hoy', null, null);");
        $this->execute("INSERT INTO roles VALUES(default, 'vendedor', 'Vendedor', 'Usuario que realiza ventas desde la caja', 0, 1, '$hoy', '$hoy', null, null);");
    }

    public function down(): void {
        $this->execute("DELETE FROM usuario_rol;");
        $this->execute("DELETE FROM roles;");
    }
}

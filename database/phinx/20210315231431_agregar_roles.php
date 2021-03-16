<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class AgregarRoles extends AbstractMigration
{
    public function up(): void {
        $hoy = \Carbon\Carbon::now()->format('Y-m-d H:i');
        $this->execute("INSERT INTO roles VALUES(null, 'root', 'Root', 'Rol con todos los permisos', 1, 1, '$hoy', '$hoy', null, null);");
        $this->execute("INSERT INTO roles VALUES(null, 'invitado', 'Invitado', 'Usuario que no ha sido autenticado', 0, 1, '$hoy', '$hoy', null, null);");
        $this->execute("INSERT INTO roles VALUES(null, 'admin', 'Administrador', 'Administrador del sistema', 0, 1, '$hoy', '$hoy', null, null);");
        $this->execute("INSERT INTO roles VALUES(null, 'comensal', 'Comensal', 'Persona que puede realizar pedidos en la web', 0, 1, '$hoy', '$hoy', null, null);");
    }

    public function down(): void {
        $this->execute("DELETE FROM roles;");
    }
}

<?php
declare(strict_types=1);

use Phinx\Migration\AbstractMigration;

final class UsuarioCampoDni extends AbstractMigration
{
    public function up(): void {
        $this->execute("ALTER TABLE usuarios ADD dni BIGINT DEFAULT NULL AFTER email;");
    }

    public function down(): void {
        $this->execute("ALTER TABLE usuarios DROP dni;");
    }
}

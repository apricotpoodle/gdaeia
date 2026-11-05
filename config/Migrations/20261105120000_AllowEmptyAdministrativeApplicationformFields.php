<?php
declare(strict_types=1);

use Migrations\BaseMigration;

/** Autorise les brouillons issus d'une duplication sans champs réservés. */
final class AllowEmptyAdministrativeApplicationformFields extends BaseMigration
{
    public function up(): void
    {
        $this->table('applicationforms')
            ->changeColumn('budgetfeature_id', 'integer', [
                'default' => null,
                'null' => true,
                'signed' => false,
            ])
            ->changeColumn('yesno_id', 'integer', [
                'default' => null,
                'null' => true,
                'signed' => false,
            ])
            ->update();
    }

    public function down(): void
    {
        $this->table('applicationforms')
            ->changeColumn('budgetfeature_id', 'integer', [
                'default' => 1,
                'null' => false,
                'signed' => false,
            ])
            ->changeColumn('yesno_id', 'integer', [
                'default' => null,
                'null' => false,
                'signed' => false,
            ])
            ->update();
    }
}

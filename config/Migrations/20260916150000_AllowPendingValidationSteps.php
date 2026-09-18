<?php
declare(strict_types=1);

use Migrations\BaseMigration;

/**
 * Autorise l'absence de statut tant qu'une étape du nouveau workflow n'a pas reçu de vote.
 */
final class AllowPendingValidationSteps extends BaseMigration
{
    public function up(): void
    {
        $this->table('applicationvalidationsteps')
            ->changeColumn('validationstatus_id', 'integer', [
                'null' => true,
                'signed' => false,
            ])
            ->update();
    }

    public function down(): void
    {
        $this->table('applicationvalidationsteps')
            ->changeColumn('validationstatus_id', 'integer', [
                'null' => false,
                'signed' => false,
            ])
            ->update();
    }
}

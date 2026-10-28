<?php
declare(strict_types=1);

use Migrations\BaseMigration;

/** Ajoute les données de saisie et de validation prévues par le cahier V3. */
final class AddGdaeV3ApplicationformFields extends BaseMigration
{
    /** Ajoute les colonnes V3 sans altérer les demandes existantes. */
    public function up(): void
    {
        $this->table('applicationforms')
            ->addColumn('assistant_id', 'integer', ['default' => null, 'null' => true, 'signed' => false])
            ->addColumn('manager_id', 'integer', ['default' => null, 'null' => true, 'signed' => false])
            ->addColumn('applicant_first_name', 'string', ['limit' => 120, 'default' => null, 'null' => true])
            ->addColumn('applicant_last_name', 'string', ['limit' => 120, 'default' => null, 'null' => true])
            ->addColumn('working_days', 'integer', ['default' => null, 'null' => true])
            ->addColumn('salary_base', 'decimal', [
                'default' => null,
                'null' => true,
                'precision' => 19,
                'scale' => 4,
            ])
            ->addColumn('hr_description', 'text', ['default' => null, 'null' => true])
            ->addColumn('control_management_validation', 'text', ['default' => null, 'null' => true])
            ->addColumn('final_argument', 'text', ['default' => null, 'null' => true])
            ->addIndex(['assistant_id'], ['name' => 'idx_applicationforms_assistant_id'])
            ->addIndex(['manager_id'], ['name' => 'idx_applicationforms_manager_id'])
            ->addForeignKey('assistant_id', 'users', 'id', ['delete' => 'SET_NULL', 'update' => 'CASCADE'])
            ->addForeignKey('manager_id', 'users', 'id', ['delete' => 'SET_NULL', 'update' => 'CASCADE'])
            ->update();
    }

    /** Retire uniquement les colonnes ajoutées par cette migration. */
    public function down(): void
    {
        $table = $this->table('applicationforms');
        foreach (['assistant_id', 'manager_id'] as $column) {
            $table->dropForeignKey($column);
        }
        $table
            ->removeIndexByName('idx_applicationforms_assistant_id')
            ->removeIndexByName('idx_applicationforms_manager_id')
            ->removeColumn('assistant_id')
            ->removeColumn('manager_id')
            ->removeColumn('applicant_first_name')
            ->removeColumn('applicant_last_name')
            ->removeColumn('working_days')
            ->removeColumn('salary_base')
            ->removeColumn('hr_description')
            ->removeColumn('control_management_validation')
            ->removeColumn('final_argument')
            ->update();
    }
}

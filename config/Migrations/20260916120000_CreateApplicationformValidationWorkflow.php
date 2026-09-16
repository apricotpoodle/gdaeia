<?php
declare(strict_types=1);

use Migrations\BaseMigration;

/** Ajoute un workflow de validation immuable aux demandes de recrutement. */
final class CreateApplicationformValidationWorkflow extends BaseMigration
{
    public function up(): void
    {
        $this->table('validation_workflow_runs', ['id' => false])
            ->addColumn('id', 'integer', ['autoIncrement' => true, 'null' => false, 'signed' => false])
            ->addPrimaryKey(['id'])
            ->addColumn('applicationform_id', 'integer', ['null' => false, 'signed' => false])
            ->addColumn('started_by_user_id', 'integer', ['null' => false, 'signed' => false])
            ->addColumn('state', 'string', ['limit' => 16, 'null' => false])
            ->addColumn('started_at', 'datetime', ['null' => false])
            ->addColumn('finished_at', 'datetime', ['null' => true])
            ->addColumn('created', 'datetime', ['null' => false])
            ->addColumn('modified', 'datetime', ['null' => false])
            ->addIndex(['applicationform_id'], ['unique' => true, 'name' => 'validation_workflow_runs_applicationform_un'])
            ->addForeignKey('applicationform_id', 'applicationforms', 'id')
            ->addForeignKey('started_by_user_id', 'users', 'id')
            ->create();

        $this->table('workflow_settings', ['id' => false])
            ->addColumn('id', 'integer', ['autoIncrement' => true, 'null' => false, 'signed' => false])
            ->addPrimaryKey(['id'])
            ->addColumn('name', 'string', ['limit' => 64, 'null' => false])
            ->addColumn('value', 'string', ['limit' => 255, 'null' => false])
            ->addColumn('created', 'datetime', ['null' => false])
            ->addColumn('modified', 'datetime', ['null' => false])
            ->addIndex(['name'], ['unique' => true, 'name' => 'workflow_settings_name_un'])
            ->create();
        $this->table('workflow_settings')->insert([
            ['name' => 'validation.default_due_hours', 'value' => '72', 'created' => date('Y-m-d H:i:s'), 'modified' => date('Y-m-d H:i:s')],
        ])->save();

        $this->table('validation_comment_templates', ['id' => false])
            ->addColumn('id', 'integer', ['autoIncrement' => true, 'null' => false, 'signed' => false])
            ->addPrimaryKey(['id'])
            ->addColumn('decision', 'string', ['limit' => 16, 'null' => false])
            ->addColumn('label', 'string', ['limit' => 120, 'null' => false])
            ->addColumn('content', 'text', ['null' => false])
            ->addColumn('position', 'integer', ['null' => false, 'default' => 0])
            ->addColumn('active', 'boolean', ['null' => false, 'default' => true])
            ->addColumn('created', 'datetime', ['null' => false])
            ->addColumn('modified', 'datetime', ['null' => false])
            ->create();

        $sequences = $this->table('validationsequences');
        $sequences->addColumn('reminder_delay_hours', 'integer', ['null' => true, 'signed' => false])->update();

        $steps = $this->table('applicationvalidationsteps');
        $steps
            ->addColumn('validation_workflow_run_id', 'integer', ['null' => true, 'signed' => false])
            ->addColumn('sequence_number', 'integer', ['null' => true, 'signed' => false])
            ->addColumn('state', 'string', ['limit' => 16, 'null' => true])
            ->addColumn('due_at', 'datetime', ['null' => true])
            ->addColumn('activated_at', 'datetime', ['null' => true])
            ->addColumn('completed_at', 'datetime', ['null' => true])
            ->addColumn('reminder_count', 'integer', ['null' => false, 'default' => 0, 'signed' => false])
            ->addColumn('last_reminded_at', 'datetime', ['null' => true])
            ->addIndex(['validation_workflow_run_id', 'role_id'], ['unique' => true, 'name' => 'applicationvalidationsteps_run_role_un'])
            ->addIndex(['state', 'due_at'], ['name' => 'applicationvalidationsteps_due_idx'])
            ->addForeignKey('validation_workflow_run_id', 'validation_workflow_runs', 'id')
            ->update();

        $validations = $this->table('validations');
        $validations
            ->addColumn('applicationvalidationstep_id', 'integer', ['null' => true, 'signed' => false])
            ->addColumn('is_proxy', 'boolean', ['null' => false, 'default' => false])
            ->addIndex(['applicationvalidationstep_id'], ['unique' => true, 'name' => 'validations_step_un'])
            ->addForeignKey('applicationvalidationstep_id', 'applicationvalidationsteps', 'id')
            ->update();

        // Les pourcentages portent sur tous les rôles de la séquence du département,
        // pas seulement sur les votes déjà créés : 1 vote sur 5 vaut donc 20 %.
        $this->execute("CREATE OR REPLACE VIEW applicationformstatuses AS
            SELECT af.id AS applicationform_id,
              EXISTS(SELECT 1 FROM validations v WHERE v.applicationform_id = af.id) AS has_validations,
              COALESCE((SELECT ROUND(100 * COUNT(DISTINCT v.role_id) / NULLIF((SELECT COUNT(*) FROM validationsequences vs WHERE vs.department_id = af.department_id AND vs.deleted IS NULL), 0), 2) FROM validations v WHERE v.applicationform_id = af.id AND v.validationstatus_id >= 3), 0) AS valid_percentage,
              0 AS validationstatus_id, 0 AS current_sequence, 0 AS en_cours,
              0 AS accepted, 0 AS rejected
            FROM applicationforms af");
    }

    public function down(): void
    {
        $this->table('validations')->dropForeignKey('applicationvalidationstep_id')->removeIndexByName('validations_step_un')->removeColumn('applicationvalidationstep_id')->removeColumn('is_proxy')->update();
        $this->table('applicationvalidationsteps')->dropForeignKey('validation_workflow_run_id')->removeIndexByName('applicationvalidationsteps_run_role_un')->removeIndexByName('applicationvalidationsteps_due_idx')->removeColumn('validation_workflow_run_id')->removeColumn('sequence_number')->removeColumn('state')->removeColumn('due_at')->removeColumn('activated_at')->removeColumn('completed_at')->removeColumn('reminder_count')->removeColumn('last_reminded_at')->update();
        $this->table('validationsequences')->removeColumn('reminder_delay_hours')->update();
        $this->table('validation_comment_templates')->drop()->save();
        $this->table('workflow_settings')->drop()->save();
        $this->table('validation_workflow_runs')->drop()->save();
    }
}

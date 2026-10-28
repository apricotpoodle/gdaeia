<?php
declare(strict_types=1);

use Migrations\BaseMigration;

/** Ajoute le paramétrage local des fins de circuit par contrat. */
final class CreateValidationCircuitRules extends BaseMigration
{
    public function up(): void
    {
        $this->table('workflow_settings')->insert([
            [
                'name' => 'salary.k_coefficient',
                'value' => '17.46',
                'created' => date('Y-m-d H:i:s'),
                'modified' => date('Y-m-d H:i:s'),
            ],
            [
                'name' => 'applicationform.duplicate_reset_fields',
                'value' => 'assistant_id,manager_id,control_management_validation,final_argument,salary_base',
                'created' => date('Y-m-d H:i:s'),
                'modified' => date('Y-m-d H:i:s'),
            ],
        ])->save();
        $this->table('validation_circuit_rules', ['id' => false])
            ->addColumn('id', 'integer', ['autoIncrement' => true, 'null' => false, 'signed' => false])
            ->addPrimaryKey(['id'])
            ->addColumn('department_id', 'integer', ['null' => false, 'signed' => false])
            ->addColumn('contracttype_id', 'integer', ['null' => false, 'signed' => false])
            ->addColumn('final_role_id', 'integer', ['null' => false, 'signed' => false])
            ->addColumn('created', 'datetime', ['null' => false])
            ->addColumn('modified', 'datetime', ['null' => false])
            ->addIndex(['department_id', 'contracttype_id'], [
                'unique' => true,
                'name' => 'validation_circuit_rules_department_contract_un',
            ])
            ->addForeignKey('department_id', 'departments', 'id')
            ->addForeignKey('contracttype_id', 'contracttypes', 'id')
            ->addForeignKey('final_role_id', 'roles', 'id')
            ->create();
    }

    public function down(): void
    {
        $this->execute("DELETE FROM workflow_settings WHERE name IN ('salary.k_coefficient', 'applicationform.duplicate_reset_fields')");
        $this->table('validation_circuit_rules')->drop()->save();
    }
}

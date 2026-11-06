<?php
declare(strict_types=1);

use Migrations\BaseMigration;

/** Ajoute les habilitations locales sans reprendre les anciens droits implicites. */
final class CreateValidationCircuitManagers extends BaseMigration
{
    public function up(): void
    {
        $this->table('validation_circuit_managers', ['id' => false])
            ->addColumn('id', 'integer', ['autoIncrement' => true, 'null' => false, 'signed' => false])
            ->addPrimaryKey(['id'])
            ->addColumn('department_id', 'integer', ['null' => false, 'signed' => false])
            ->addColumn('role_id', 'integer', ['null' => false, 'signed' => false])
            ->addColumn('created', 'datetime', ['null' => false])
            ->addColumn('modified', 'datetime', ['null' => false])
            ->addIndex(['department_id', 'role_id'], ['unique' => true, 'name' => 'uq_circuit_managers_department_role'])
            ->addForeignKey('department_id', 'departments', 'id')
            ->addForeignKey('role_id', 'roles', 'id')
            ->create();
    }

    public function down(): void
    {
        $this->table('validation_circuit_managers')->drop()->save();
    }
}

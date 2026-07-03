<?php
declare(strict_types=1);

use Migrations\BaseMigration;

class Initial extends BaseMigration
{
    public bool $autoId = false;

    /**
     * Up Method.
     *
     * More information on this method is available here:
     * https://book.cakephp.org/migrations/5/guides/writing-migrations/migration-methods.html#the-up-method
     *
     * @return void
     */
    public function up(): void
    {
        $this->table('applicationforms')
            ->addColumn('id', 'integer', [
                'autoIncrement' => true,
                'default' => null,
                'limit' => null,
                'null' => false,
                'signed' => false,
            ])
            ->addPrimaryKey(['id'])
            ->addColumn('department_id', 'integer', [
                'default' => null,
                'limit' => null,
                'null' => false,
            ])
            ->addColumn('user_id', 'integer', [
                'default' => null,
                'limit' => null,
                'null' => false,
            ])
            ->addColumn('cgr', 'string', [
                'default' => null,
                'limit' => 255,
                'null' => true,
            ])
            ->addColumn('contracttype_id', 'integer', [
                'default' => null,
                'limit' => null,
                'null' => false,
            ])
            ->addColumn('hiringreason_id', 'integer', [
                'default' => null,
                'limit' => null,
                'null' => false,
            ])
            ->addColumn('reasonforreplacement', 'string', [
                'default' => null,
                'limit' => 255,
                'null' => true,
            ])
            ->addColumn('budgetfeature_id', 'integer', [
                'default' => '1',
                'limit' => null,
                'null' => false,
            ])
            ->addColumn('jobtitle', 'string', [
                'default' => null,
                'limit' => 255,
                'null' => false,
            ])
            ->addColumn('professionalcategory_id', 'integer', [
                'default' => null,
                'limit' => null,
                'null' => false,
            ])
            ->addColumn('worktime_id', 'integer', [
                'default' => null,
                'limit' => null,
                'null' => false,
            ])
            ->addColumn('workingtimedistribution', 'string', [
                'default' => null,
                'limit' => 255,
                'null' => true,
            ])
            ->addColumn('grossremuneration', 'decimal', [
                'default' => '0.0000',
                'null' => false,
                'precision' => 19,
                'scale' => 4,
            ])
            ->addColumn('period_id', 'integer', [
                'default' => null,
                'limit' => null,
                'null' => false,
            ])
            ->addColumn('qualification', 'string', [
                'default' => null,
                'limit' => 255,
                'null' => true,
            ])
            ->addColumn('begin_at', 'date', [
                'default' => null,
                'limit' => null,
                'null' => true,
            ])
            ->addColumn('end_at', 'date', [
                'default' => null,
                'limit' => null,
                'null' => true,
            ])
            ->addColumn('applicantname', 'string', [
                'default' => null,
                'limit' => 255,
                'null' => true,
            ])
            ->addColumn('yesno_id', 'integer', [
                'default' => null,
                'limit' => null,
                'null' => false,
            ])
            ->addColumn('deleted', 'datetime', [
                'default' => null,
                'limit' => null,
                'null' => true,
            ])
            ->addColumn('created', 'timestamp', [
                'default' => 'CURRENT_TIMESTAMP',
                'limit' => null,
                'null' => false,
            ])
            ->addColumn('modified', 'timestamp', [
                'default' => null,
                'limit' => null,
                'null' => true,
            ])
            ->addIndex(
                $this->index('department_id')
                    ->setName('applicationforms_department_id_IDX')
            )
            ->create();

        $this->table('applicationvalidationsteps')
            ->addColumn('id', 'integer', [
                'autoIncrement' => true,
                'default' => null,
                'limit' => null,
                'null' => false,
                'signed' => false,
            ])
            ->addPrimaryKey(['id'])
            ->addColumn('applicationform_id', 'integer', [
                'default' => null,
                'limit' => null,
                'null' => false,
                'signed' => false,
            ])
            ->addColumn('role_id', 'integer', [
                'default' => null,
                'limit' => null,
                'null' => false,
                'signed' => false,
            ])
            ->addColumn('validationstatus_id', 'integer', [
                'default' => null,
                'limit' => null,
                'null' => false,
                'signed' => false,
            ])
            ->addColumn('comment', 'string', [
                'default' => null,
                'limit' => 100,
                'null' => true,
            ])
            ->addColumn('validationsequence_id', 'integer', [
                'default' => null,
                'limit' => null,
                'null' => false,
                'signed' => false,
            ])
            ->addColumn('deleted', 'datetime', [
                'default' => null,
                'limit' => null,
                'null' => true,
            ])
            ->addColumn('modified', 'timestamp', [
                'default' => null,
                'limit' => null,
                'null' => true,
            ])
            ->addColumn('created', 'timestamp', [
                'default' => 'CURRENT_TIMESTAMP',
                'limit' => null,
                'null' => false,
            ])
            ->create();

        $this->table('budgetfeatures')
            ->addColumn('id', 'integer', [
                'autoIncrement' => true,
                'default' => null,
                'limit' => null,
                'null' => false,
                'signed' => false,
            ])
            ->addPrimaryKey(['id'])
            ->addColumn('base', 'boolean', [
                'default' => false,
                'limit' => null,
                'null' => false,
            ])
            ->addColumn('code', 'string', [
                'default' => null,
                'limit' => 16,
                'null' => false,
            ])
            ->addColumn('name', 'string', [
                'default' => null,
                'limit' => 32,
                'null' => false,
            ])
            ->addColumn('sort', 'string', [
                'default' => '',
                'limit' => 32,
                'null' => false,
            ])
            ->addColumn('deleted', 'datetime', [
                'default' => null,
                'limit' => null,
                'null' => true,
            ])
            ->addColumn('created', 'timestamp', [
                'default' => 'CURRENT_TIMESTAMP',
                'limit' => null,
                'null' => false,
            ])
            ->addColumn('modified', 'timestamp', [
                'default' => null,
                'limit' => null,
                'null' => true,
            ])
            ->addIndex(
                $this->index('code')
                    ->setName('code')
                    ->setType('unique')
            )
            ->addIndex(
                $this->index('name')
                    ->setName('name')
                    ->setType('unique')
            )
            ->addIndex(
                $this->index('sort')
                    ->setName('sort')
            )
            ->create();

        $this->table('cgr_codes')
            ->addColumn('id', 'integer', [
                'autoIncrement' => true,
                'default' => null,
                'limit' => null,
                'null' => false,
                'signed' => false,
            ])
            ->addPrimaryKey(['id'])
            ->addColumn('department_id', 'integer', [
                'comment' => 'Lien vers l\'Entité propriétaire',
                'default' => null,
                'limit' => null,
                'null' => false,
                'signed' => false,
            ])
            ->addColumn('type', 'string', [
                'comment' => 'Type de zone (SERVICE, TITRE...)',
                'default' => null,
                'limit' => 32,
                'null' => false,
            ])
            ->addColumn('code', 'string', [
                'comment' => 'La valeur courte (ex: S01)',
                'default' => null,
                'limit' => 16,
                'null' => false,
            ])
            ->addColumn('label', 'string', [
                'comment' => 'Libellé complet',
                'default' => null,
                'limit' => 255,
                'null' => false,
            ])
            ->addColumn('active', 'boolean', [
                'default' => true,
                'limit' => null,
                'null' => false,
            ])
            ->addColumn('is_system', 'boolean', [
                'default' => false,
                'limit' => null,
                'null' => false,
            ])
            ->addColumn('created', 'timestamp', [
                'default' => 'CURRENT_TIMESTAMP',
                'limit' => null,
                'null' => false,
            ])
            ->addColumn('modified', 'timestamp', [
                'default' => null,
                'limit' => null,
                'null' => true,
            ])
            ->addIndex(
                $this->index([
                        'department_id',
                        'type',
                        'code',
                    ])
                    ->setName('idx_cgr_codes_unique_definition')
                    ->setType('unique')
            )
            ->create();

        $this->table('cgr_strategies')
            ->addColumn('id', 'integer', [
                'autoIncrement' => true,
                'default' => null,
                'limit' => null,
                'null' => false,
                'signed' => false,
            ])
            ->addPrimaryKey(['id'])
            ->addColumn('code', 'string', [
                'comment' => 'Code technique unique (ex: STANDARD, SEM)',
                'default' => null,
                'limit' => 32,
                'null' => false,
            ])
            ->addColumn('name', 'string', [
                'comment' => 'Nom lisible de la stratégie',
                'default' => null,
                'limit' => 64,
                'null' => false,
            ])
            ->addColumn('definition_json', 'json', [
                'comment' => 'Configuration JSON des champs requis',
                'default' => null,
                'limit' => null,
                'null' => false,
            ])
            ->addColumn('created', 'timestamp', [
                'default' => 'CURRENT_TIMESTAMP',
                'limit' => null,
                'null' => false,
            ])
            ->addColumn('modified', 'timestamp', [
                'default' => null,
                'limit' => null,
                'null' => true,
            ])
            ->addIndex(
                $this->index('code')
                    ->setName('code')
                    ->setType('unique')
            )
            ->create();

        $this->table('contracttypes')
            ->addColumn('id', 'integer', [
                'autoIncrement' => true,
                'default' => null,
                'limit' => null,
                'null' => false,
                'signed' => false,
            ])
            ->addPrimaryKey(['id'])
            ->addColumn('base', 'boolean', [
                'default' => false,
                'limit' => null,
                'null' => false,
            ])
            ->addColumn('code', 'string', [
                'default' => null,
                'limit' => 16,
                'null' => false,
            ])
            ->addColumn('name', 'string', [
                'default' => null,
                'limit' => 32,
                'null' => false,
            ])
            ->addColumn('sort', 'string', [
                'default' => '',
                'limit' => 32,
                'null' => false,
            ])
            ->addColumn('deleted', 'datetime', [
                'default' => null,
                'limit' => null,
                'null' => true,
            ])
            ->addColumn('created', 'timestamp', [
                'default' => 'CURRENT_TIMESTAMP',
                'limit' => null,
                'null' => false,
            ])
            ->addColumn('modified', 'timestamp', [
                'default' => null,
                'limit' => null,
                'null' => true,
            ])
            ->addIndex(
                $this->index('code')
                    ->setName('code')
                    ->setType('unique')
            )
            ->addIndex(
                $this->index('name')
                    ->setName('name')
                    ->setType('unique')
            )
            ->addIndex(
                $this->index('sort')
                    ->setName('sort')
            )
            ->create();

        $this->table('departments')
            ->addColumn('id', 'integer', [
                'autoIncrement' => true,
                'default' => null,
                'limit' => null,
                'null' => false,
                'signed' => false,
            ])
            ->addPrimaryKey(['id'])
            ->addColumn('parent_id', 'integer', [
                'default' => null,
                'limit' => null,
                'null' => true,
            ])
            ->addColumn('cgr_code_id', 'integer', [
                'default' => null,
                'limit' => null,
                'null' => true,
                'signed' => false,
            ])
            ->addColumn('lft', 'integer', [
                'default' => null,
                'limit' => null,
                'null' => true,
            ])
            ->addColumn('rght', 'integer', [
                'default' => null,
                'limit' => null,
                'null' => true,
            ])
            ->addColumn('level', 'integer', [
                'default' => '0',
                'limit' => null,
                'null' => true,
            ])
            ->addColumn('base', 'boolean', [
                'default' => true,
                'limit' => null,
                'null' => false,
            ])
            ->addColumn('code', 'string', [
                'default' => null,
                'limit' => 32,
                'null' => false,
            ])
            ->addColumn('name', 'string', [
                'default' => null,
                'limit' => 64,
                'null' => false,
            ])
            ->addColumn('sort', 'string', [
                'default' => '',
                'limit' => 64,
                'null' => true,
            ])
            ->addColumn('department_type_id', 'integer', [
                'default' => '1',
                'limit' => null,
                'null' => false,
            ])
            ->addColumn('cgr_strategy_id', 'integer', [
                'comment' => 'Référence à la stratégie CGR',
                'default' => null,
                'limit' => null,
                'null' => true,
                'signed' => false,
            ])
            ->addColumn('default_cgr', 'string', [
                'comment' => 'CGR par défaut pré-calculé',
                'default' => null,
                'limit' => 255,
                'null' => true,
            ])
            ->addColumn('current_manager_id', 'integer', [
                'comment' => 'identifiant responsable de service',
                'default' => null,
                'limit' => null,
                'null' => true,
                'signed' => false,
            ])
            ->addColumn('deleted', 'datetime', [
                'default' => null,
                'limit' => null,
                'null' => true,
            ])
            ->addColumn('created', 'timestamp', [
                'default' => 'CURRENT_TIMESTAMP',
                'limit' => null,
                'null' => false,
            ])
            ->addColumn('modified', 'timestamp', [
                'default' => null,
                'limit' => null,
                'null' => true,
            ])
            ->addIndex(
                $this->index('code')
                    ->setName('code')
                    ->setType('unique')
            )
            ->addIndex(
                $this->index('name')
                    ->setName('name')
                    ->setType('unique')
            )
            ->addIndex(
                $this->index('code')
                    ->setName('code_2')
                    ->setType('unique')
            )
            ->addIndex(
                $this->index('name')
                    ->setName('name_2')
                    ->setType('unique')
            )
            ->addIndex(
                $this->index('sort')
                    ->setName('sort')
            )
            ->addIndex(
                $this->index('sort')
                    ->setName('sort_2')
            )
            ->addIndex(
                $this->index('lft')
                    ->setName('idx_lft')
            )
            ->addIndex(
                $this->index('parent_id')
                    ->setName('parent_id')
            )
            ->addIndex(
                $this->index('cgr_strategy_id')
                    ->setName('fk_departments_cgr_strategy_id')
            )
            ->addIndex(
                $this->index('cgr_code_id')
                    ->setName('fk_departments_cgr_codes')
            )
            ->create();

        $this->table('email_logs')
            ->addColumn('id', 'integer', [
                'autoIncrement' => true,
                'default' => null,
                'limit' => null,
                'null' => false,
            ])
            ->addPrimaryKey(['id'])
            ->addColumn('subject', 'string', [
                'default' => null,
                'limit' => 255,
                'null' => false,
            ])
            ->addColumn('content_text', 'text', [
                'default' => null,
                'limit' => null,
                'null' => true,
            ])
            ->addColumn('content_html', 'text', [
                'default' => null,
                'limit' => null,
                'null' => true,
            ])
            ->addColumn('error_message', 'text', [
                'default' => null,
                'limit' => null,
                'null' => true,
            ])
            ->addColumn('created', 'datetime', [
                'default' => 'CURRENT_TIMESTAMP',
                'limit' => null,
                'null' => true,
            ])
            ->addColumn('modified', 'datetime', [
                'default' => 'CURRENT_TIMESTAMP',
                'limit' => null,
                'null' => true,
            ])
            ->create();

        $this->table('email_recipients')
            ->addColumn('id', 'integer', [
                'autoIncrement' => true,
                'default' => null,
                'limit' => null,
                'null' => false,
            ])
            ->addPrimaryKey(['id'])
            ->addColumn('email_log_id', 'integer', [
                'default' => null,
                'limit' => null,
                'null' => false,
            ])
            ->addColumn('recipient_email', 'string', [
                'default' => null,
                'limit' => 255,
                'null' => false,
            ])
            ->addIndex(
                $this->index('email_log_id')
                    ->setName('emaillog_id')
            )
            ->create();

        $this->table('field_authorizations')
            ->addColumn('id', 'integer', [
                'autoIncrement' => true,
                'default' => null,
                'limit' => null,
                'null' => false,
            ])
            ->addPrimaryKey(['id'])
            ->addColumn('role_id', 'integer', [
                'default' => null,
                'limit' => null,
                'null' => false,
                'signed' => false,
            ])
            ->addColumn('resource', 'string', [
                'default' => null,
                'limit' => 50,
                'null' => false,
            ])
            ->addColumn('field', 'string', [
                'default' => null,
                'limit' => 50,
                'null' => false,
            ])
            ->addColumn('access_level', 'string', [
                'default' => 'EDIT',
                'limit' => 20,
                'null' => false,
            ])
            ->addColumn('created', 'timestamp', [
                'default' => 'CURRENT_TIMESTAMP',
                'limit' => null,
                'null' => false,
            ])
            ->addColumn('modified', 'timestamp', [
                'default' => null,
                'limit' => null,
                'null' => true,
            ])
            ->addIndex(
                $this->index([
                        'role_id',
                        'resource',
                        'field',
                    ])
                    ->setName('role_id')
                    ->setType('unique')
            )
            ->create();

        $this->table('hiringreasons')
            ->addColumn('id', 'integer', [
                'autoIncrement' => true,
                'default' => null,
                'limit' => null,
                'null' => false,
                'signed' => false,
            ])
            ->addPrimaryKey(['id'])
            ->addColumn('base', 'boolean', [
                'default' => false,
                'limit' => null,
                'null' => false,
            ])
            ->addColumn('code', 'string', [
                'default' => null,
                'limit' => 16,
                'null' => false,
            ])
            ->addColumn('name', 'string', [
                'default' => null,
                'limit' => 32,
                'null' => false,
            ])
            ->addColumn('sort', 'string', [
                'default' => '',
                'limit' => 32,
                'null' => false,
            ])
            ->addColumn('created', 'timestamp', [
                'default' => 'CURRENT_TIMESTAMP',
                'limit' => null,
                'null' => false,
            ])
            ->addColumn('modified', 'timestamp', [
                'default' => null,
                'limit' => null,
                'null' => true,
            ])
            ->addIndex(
                $this->index('code')
                    ->setName('code')
                    ->setType('unique')
            )
            ->addIndex(
                $this->index('name')
                    ->setName('name')
                    ->setType('unique')
            )
            ->addIndex(
                $this->index('sort')
                    ->setName('sort')
            )
            ->create();

        $this->table('menus')
            ->addColumn('id', 'integer', [
                'autoIncrement' => true,
                'default' => null,
                'limit' => null,
                'null' => false,
                'signed' => false,
            ])
            ->addPrimaryKey(['id'])
            ->addColumn('parent_id', 'integer', [
                'default' => null,
                'limit' => null,
                'null' => true,
            ])
            ->addColumn('lft', 'integer', [
                'default' => null,
                'limit' => null,
                'null' => false,
            ])
            ->addColumn('rght', 'integer', [
                'default' => null,
                'limit' => null,
                'null' => false,
            ])
            ->addColumn('level', 'integer', [
                'default' => '0',
                'limit' => null,
                'null' => true,
            ])
            ->addColumn('name', 'string', [
                'default' => null,
                'limit' => 255,
                'null' => true,
            ])
            ->addColumn('url', 'string', [
                'default' => null,
                'limit' => 255,
                'null' => true,
            ])
            ->addColumn('active', 'boolean', [
                'default' => null,
                'limit' => null,
                'null' => true,
            ])
            ->addColumn('disabled', 'boolean', [
                'default' => null,
                'limit' => null,
                'null' => true,
            ])
            ->addColumn('dividor_before', 'boolean', [
                'default' => false,
                'limit' => null,
                'null' => true,
            ])
            ->addIndex(
                $this->index('lft')
                    ->setName('idx_lft')
            )
            ->addIndex(
                $this->index('parent_id')
                    ->setName('parent_id')
            )
            ->create();

        $this->table('periods')
            ->addColumn('id', 'integer', [
                'autoIncrement' => true,
                'default' => null,
                'limit' => null,
                'null' => false,
                'signed' => false,
            ])
            ->addPrimaryKey(['id'])
            ->addColumn('base', 'boolean', [
                'default' => false,
                'limit' => null,
                'null' => false,
            ])
            ->addColumn('code', 'string', [
                'default' => null,
                'limit' => 16,
                'null' => false,
            ])
            ->addColumn('name', 'string', [
                'default' => null,
                'limit' => 32,
                'null' => false,
            ])
            ->addColumn('sort', 'string', [
                'default' => '',
                'limit' => 32,
                'null' => false,
            ])
            ->addColumn('deleted', 'datetime', [
                'default' => null,
                'limit' => null,
                'null' => true,
            ])
            ->addColumn('created', 'timestamp', [
                'default' => 'CURRENT_TIMESTAMP',
                'limit' => null,
                'null' => false,
            ])
            ->addColumn('modified', 'timestamp', [
                'default' => null,
                'limit' => null,
                'null' => true,
            ])
            ->addIndex(
                $this->index('code')
                    ->setName('code')
                    ->setType('unique')
            )
            ->addIndex(
                $this->index('name')
                    ->setName('name')
                    ->setType('unique')
            )
            ->addIndex(
                $this->index('sort')
                    ->setName('sort')
            )
            ->create();

        $this->table('professionalcategories')
            ->addColumn('id', 'integer', [
                'autoIncrement' => true,
                'default' => null,
                'limit' => null,
                'null' => false,
                'signed' => false,
            ])
            ->addPrimaryKey(['id'])
            ->addColumn('base', 'boolean', [
                'default' => false,
                'limit' => null,
                'null' => false,
            ])
            ->addColumn('code', 'string', [
                'default' => null,
                'limit' => 16,
                'null' => false,
            ])
            ->addColumn('name', 'string', [
                'default' => null,
                'limit' => 32,
                'null' => false,
            ])
            ->addColumn('sort', 'string', [
                'default' => '',
                'limit' => 32,
                'null' => false,
            ])
            ->addColumn('deleted', 'datetime', [
                'default' => null,
                'limit' => null,
                'null' => true,
            ])
            ->addColumn('created', 'timestamp', [
                'default' => 'CURRENT_TIMESTAMP',
                'limit' => null,
                'null' => false,
            ])
            ->addColumn('modified', 'timestamp', [
                'default' => null,
                'limit' => null,
                'null' => true,
            ])
            ->addIndex(
                $this->index('code')
                    ->setName('code')
                    ->setType('unique')
            )
            ->addIndex(
                $this->index('name')
                    ->setName('name')
                    ->setType('unique')
            )
            ->addIndex(
                $this->index('sort')
                    ->setName('sort')
            )
            ->create();

        $this->table('role_menus')
            ->addColumn('id', 'integer', [
                'autoIncrement' => true,
                'default' => null,
                'limit' => null,
                'null' => false,
                'signed' => false,
            ])
            ->addPrimaryKey(['id'])
            ->addColumn('role_id', 'integer', [
                'default' => null,
                'limit' => null,
                'null' => false,
            ])
            ->addColumn('menu_id', 'integer', [
                'default' => null,
                'limit' => null,
                'null' => false,
            ])
            ->addColumn('department_id', 'integer', [
                'default' => null,
                'limit' => null,
                'null' => true,
                'signed' => false,
            ])
            ->addColumn('created', 'timestamp', [
                'default' => 'CURRENT_TIMESTAMP',
                'limit' => null,
                'null' => false,
            ])
            ->addColumn('modified', 'timestamp', [
                'default' => null,
                'limit' => null,
                'null' => true,
            ])
            ->addIndex(
                $this->index([
                        'role_id',
                        'menu_id',
                        'department_id',
                    ])
                    ->setName('role_menus_role_menu_dept_UN')
                    ->setType('unique')
            )
            ->addIndex(
                $this->index('role_id')
                    ->setName('user_id')
            )
            ->addIndex(
                $this->index('menu_id')
                    ->setName('menu_id')
            )
            ->addIndex(
                $this->index('department_id')
                    ->setName('idx_role_menus_dept')
            )
            ->create();

        $this->table('roles')
            ->addColumn('id', 'integer', [
                'autoIncrement' => true,
                'default' => null,
                'limit' => null,
                'null' => false,
                'signed' => false,
            ])
            ->addPrimaryKey(['id'])
            ->addColumn('base', 'boolean', [
                'default' => false,
                'limit' => null,
                'null' => false,
            ])
            ->addColumn('code', 'string', [
                'default' => null,
                'limit' => 16,
                'null' => false,
            ])
            ->addColumn('name', 'string', [
                'default' => null,
                'limit' => 64,
                'null' => false,
            ])
            ->addColumn('sort', 'string', [
                'default' => '',
                'limit' => 64,
                'null' => false,
            ])
            ->addColumn('deleted', 'datetime', [
                'default' => null,
                'limit' => null,
                'null' => true,
            ])
            ->addColumn('created', 'timestamp', [
                'default' => 'CURRENT_TIMESTAMP',
                'limit' => null,
                'null' => false,
            ])
            ->addColumn('modified', 'timestamp', [
                'default' => null,
                'limit' => null,
                'null' => true,
            ])
            ->addIndex(
                $this->index('code')
                    ->setName('code')
                    ->setType('unique')
            )
            ->addIndex(
                $this->index('name')
                    ->setName('name')
                    ->setType('unique')
            )
            ->addIndex(
                $this->index('sort')
                    ->setName('sort')
            )
            ->create();

        $this->table('user_departments')
            ->addColumn('id', 'integer', [
                'autoIncrement' => true,
                'default' => null,
                'limit' => null,
                'null' => false,
                'signed' => false,
            ])
            ->addPrimaryKey(['id'])
            ->addColumn('user_id', 'integer', [
                'default' => null,
                'limit' => null,
                'null' => false,
            ])
            ->addColumn('department_id', 'integer', [
                'default' => null,
                'limit' => null,
                'null' => false,
            ])
            ->addColumn('created', 'timestamp', [
                'default' => 'CURRENT_TIMESTAMP',
                'limit' => null,
                'null' => false,
            ])
            ->addColumn('modified', 'timestamp', [
                'default' => null,
                'limit' => null,
                'null' => true,
            ])
            ->addIndex(
                $this->index([
                        'user_id',
                        'department_id',
                    ])
                    ->setName('user_departments_user_id_IDX')
                    ->setType('unique')
            )
            ->addIndex(
                $this->index('user_id')
                    ->setName('user_id')
            )
            ->addIndex(
                $this->index('department_id')
                    ->setName('department_id')
            )
            ->create();

        $this->table('users')
            ->addColumn('id', 'integer', [
                'autoIncrement' => true,
                'default' => null,
                'limit' => null,
                'null' => false,
                'signed' => false,
            ])
            ->addPrimaryKey(['id'])
            ->addColumn('username', 'string', [
                'default' => null,
                'limit' => 255,
                'null' => true,
            ])
            ->addColumn('email', 'string', [
                'default' => null,
                'limit' => 255,
                'null' => false,
            ])
            ->addColumn('password', 'string', [
                'default' => null,
                'limit' => 255,
                'null' => false,
            ])
            ->addColumn('firstname', 'string', [
                'default' => null,
                'limit' => 255,
                'null' => true,
            ])
            ->addColumn('lastname', 'string', [
                'default' => null,
                'limit' => 255,
                'null' => true,
            ])
            ->addColumn('token', 'string', [
                'default' => null,
                'limit' => 255,
                'null' => true,
            ])
            ->addColumn('issuperuser', 'boolean', [
                'default' => false,
                'limit' => null,
                'null' => false,
            ])
            ->addColumn('role_id', 'integer', [
                'default' => '1',
                'limit' => null,
                'null' => false,
            ])
            ->addColumn('token_expires', 'timestamp', [
                'default' => null,
                'limit' => null,
                'null' => true,
            ])
            ->addColumn('deleted', 'datetime', [
                'default' => null,
                'limit' => null,
                'null' => true,
            ])
            ->addColumn('created', 'timestamp', [
                'default' => 'CURRENT_TIMESTAMP',
                'limit' => null,
                'null' => false,
            ])
            ->addColumn('modified', 'timestamp', [
                'default' => null,
                'limit' => null,
                'null' => true,
            ])
            ->create();

        $this->table('validations')
            ->addColumn('id', 'integer', [
                'autoIncrement' => true,
                'default' => null,
                'limit' => null,
                'null' => false,
                'signed' => false,
            ])
            ->addPrimaryKey(['id'])
            ->addColumn('applicationform_id', 'integer', [
                'default' => null,
                'limit' => null,
                'null' => false,
            ])
            ->addColumn('user_id', 'integer', [
                'default' => null,
                'limit' => null,
                'null' => false,
            ])
            ->addColumn('role_id', 'integer', [
                'default' => null,
                'limit' => null,
                'null' => false,
            ])
            ->addColumn('validated', 'datetime', [
                'default' => null,
                'limit' => null,
                'null' => true,
            ])
            ->addColumn('validationstatus_id', 'integer', [
                'default' => null,
                'limit' => null,
                'null' => true,
            ])
            ->addColumn('obs', 'string', [
                'default' => null,
                'limit' => 255,
                'null' => true,
            ])
            ->addColumn('deleted', 'datetime', [
                'default' => null,
                'limit' => null,
                'null' => true,
            ])
            ->addColumn('created', 'timestamp', [
                'default' => 'CURRENT_TIMESTAMP',
                'limit' => null,
                'null' => false,
            ])
            ->addColumn('modified', 'timestamp', [
                'default' => 'CURRENT_TIMESTAMP',
                'limit' => null,
                'null' => false,
            ])
            ->addIndex(
                $this->index('applicationform_id')
                    ->setName('applicationform_id')
            )
            ->addIndex(
                $this->index('validationstatus_id')
                    ->setName('validated')
            )
            ->create();

        $this->table('validationsequences')
            ->addColumn('id', 'integer', [
                'autoIncrement' => true,
                'default' => null,
                'limit' => null,
                'null' => false,
                'signed' => false,
            ])
            ->addPrimaryKey(['id'])
            ->addColumn('department_id', 'integer', [
                'default' => null,
                'limit' => null,
                'null' => false,
                'signed' => false,
            ])
            ->addColumn('name', 'string', [
                'default' => '',
                'limit' => 255,
                'null' => false,
            ])
            ->addColumn('description', 'text', [
                'default' => null,
                'limit' => null,
                'null' => true,
            ])
            ->addColumn('role_id', 'integer', [
                'comment' => 'Rôle Requis pour valider l\'étape',
                'default' => null,
                'limit' => null,
                'null' => false,
                'signed' => false,
            ])
            ->addColumn('sequence', 'integer', [
                'comment' => 'ordre sequentel de validation',
                'default' => '1',
                'limit' => null,
                'null' => false,
            ])
            ->addColumn('deleted', 'datetime', [
                'default' => null,
                'limit' => null,
                'null' => true,
            ])
            ->addColumn('modified', 'timestamp', [
                'default' => null,
                'limit' => null,
                'null' => true,
            ])
            ->addColumn('created', 'timestamp', [
                'default' => 'CURRENT_TIMESTAMP',
                'limit' => null,
                'null' => false,
            ])
            ->addIndex(
                $this->index([
                        'department_id',
                        'role_id',
                    ])
                    ->setName('validationsequences_UN')
                    ->setType('unique')
            )
            ->addIndex(
                $this->index([
                        'department_id',
                        'sequence',
                    ])
                    ->setName('validationsequences_department_id_IDX')
            )
            ->create();

        $this->table('validationstatuses')
            ->addColumn('id', 'integer', [
                'autoIncrement' => true,
                'default' => null,
                'limit' => null,
                'null' => false,
                'signed' => false,
            ])
            ->addPrimaryKey(['id'])
            ->addColumn('code', 'string', [
                'default' => null,
                'limit' => 100,
                'null' => false,
            ])
            ->addColumn('name', 'string', [
                'default' => null,
                'limit' => 100,
                'null' => true,
            ])
            ->addColumn('deleted', 'datetime', [
                'default' => null,
                'limit' => null,
                'null' => true,
            ])
            ->addColumn('modified', 'timestamp', [
                'default' => null,
                'limit' => null,
                'null' => true,
            ])
            ->addColumn('created', 'timestamp', [
                'default' => 'CURRENT_TIMESTAMP',
                'limit' => null,
                'null' => false,
            ])
            ->create();

        $this->table('worktimes')
            ->addColumn('id', 'integer', [
                'autoIncrement' => true,
                'default' => null,
                'limit' => null,
                'null' => false,
                'signed' => false,
            ])
            ->addPrimaryKey(['id'])
            ->addColumn('base', 'boolean', [
                'default' => false,
                'limit' => null,
                'null' => false,
            ])
            ->addColumn('code', 'string', [
                'default' => null,
                'limit' => 16,
                'null' => false,
            ])
            ->addColumn('name', 'string', [
                'default' => null,
                'limit' => 32,
                'null' => false,
            ])
            ->addColumn('sort', 'string', [
                'default' => '',
                'limit' => 32,
                'null' => false,
            ])
            ->addColumn('created', 'timestamp', [
                'default' => 'CURRENT_TIMESTAMP',
                'limit' => null,
                'null' => false,
            ])
            ->addColumn('modified', 'timestamp', [
                'default' => null,
                'limit' => null,
                'null' => true,
            ])
            ->addIndex(
                $this->index('code')
                    ->setName('code')
                    ->setType('unique')
            )
            ->addIndex(
                $this->index('name')
                    ->setName('name')
                    ->setType('unique')
            )
            ->addIndex(
                $this->index('sort')
                    ->setName('sort')
            )
            ->create();

        $this->table('yesnos')
            ->addColumn('id', 'integer', [
                'autoIncrement' => true,
                'default' => null,
                'limit' => null,
                'null' => false,
                'signed' => false,
            ])
            ->addPrimaryKey(['id'])
            ->addColumn('base', 'boolean', [
                'default' => false,
                'limit' => null,
                'null' => false,
            ])
            ->addColumn('code', 'string', [
                'default' => null,
                'limit' => 16,
                'null' => false,
            ])
            ->addColumn('name', 'string', [
                'default' => null,
                'limit' => 32,
                'null' => false,
            ])
            ->addColumn('sort', 'string', [
                'default' => '',
                'limit' => 32,
                'null' => false,
            ])
            ->addColumn('created', 'timestamp', [
                'default' => 'CURRENT_TIMESTAMP',
                'limit' => null,
                'null' => false,
            ])
            ->addColumn('modified', 'timestamp', [
                'default' => null,
                'limit' => null,
                'null' => true,
            ])
            ->addColumn('deleted', 'datetime', [
                'default' => null,
                'limit' => null,
                'null' => true,
            ])
            ->addIndex(
                $this->index('code')
                    ->setName('code')
                    ->setType('unique')
            )
            ->addIndex(
                $this->index('name')
                    ->setName('name')
                    ->setType('unique')
            )
            ->addIndex(
                $this->index('sort')
                    ->setName('sort')
            )
            ->create();

        $this->table('cgr_codes')
            ->addForeignKey(
                $this->foreignKey('department_id')
                    ->setReferencedTable('departments')
                    ->setReferencedColumns('id')
                    ->setDelete('CASCADE')
                    ->setUpdate('CASCADE')
                    ->setName('fk_cgr_codes_department_id')
            )
            ->update();

        $this->table('departments')
            ->addForeignKey(
                $this->foreignKey('cgr_code_id')
                    ->setReferencedTable('cgr_codes')
                    ->setReferencedColumns('id')
                    ->setDelete('SET_NULL')
                    ->setUpdate('CASCADE')
                    ->setName('fk_departments_cgr_codes')
            )
            ->addForeignKey(
                $this->foreignKey('cgr_strategy_id')
                    ->setReferencedTable('cgr_strategies')
                    ->setReferencedColumns('id')
                    ->setDelete('SET_NULL')
                    ->setUpdate('CASCADE')
                    ->setName('fk_departments_cgr_strategy_id')
            )
            ->update();

        $this->table('email_recipients')
            ->addForeignKey(
                $this->foreignKey('email_log_id')
                    ->setReferencedTable('email_logs')
                    ->setReferencedColumns('id')
                    ->setDelete('CASCADE')
                    ->setUpdate('NO_ACTION')
                    ->setName('email_recipients_ibfk_1')
            )
            ->update();

        $this->table('field_authorizations')
            ->addForeignKey(
                $this->foreignKey('role_id')
                    ->setReferencedTable('roles')
                    ->setReferencedColumns('id')
                    ->setDelete('CASCADE')
                    ->setUpdate('CASCADE')
                    ->setName('field_authorizations_ibfk_1')
            )
            ->update();

        $this->createApplicationViews();

    }

    /**
     * Down Method.
     *
     * More information on this method is available here:
     * https://book.cakephp.org/migrations/5/guides/writing-migrations/migration-methods.html#the-down-method
     *
     * @return void
     */
    public function down(): void
    {
        $this->execute("DROP VIEW IF EXISTS validation_visas;");
        $this->execute("DROP VIEW IF EXISTS urds;");
        $this->execute("DROP VIEW IF EXISTS currentvalidationroles;");
        $this->execute("DROP VIEW IF EXISTS applicationformstatuses;");
        
        $this->table('cgr_codes')
            ->dropForeignKey(
                'department_id'
            )->save();

        $this->table('departments')
            ->dropForeignKey(
                'cgr_code_id'
            )
            ->dropForeignKey(
                'cgr_strategy_id'
            )->save();

        $this->table('email_recipients')
            ->dropForeignKey(
                'email_log_id'
            )->save();

        $this->table('field_authorizations')
            ->dropForeignKey(
                'role_id'
            )->save();

        $this->table('applicationforms')->drop()->save();
        $this->table('applicationvalidationsteps')->drop()->save();
        $this->table('budgetfeatures')->drop()->save();
        $this->table('cgr_codes')->drop()->save();
        $this->table('cgr_strategies')->drop()->save();
        $this->table('contracttypes')->drop()->save();
        $this->table('departments')->drop()->save();
        $this->table('email_logs')->drop()->save();
        $this->table('email_recipients')->drop()->save();
        $this->table('field_authorizations')->drop()->save();
        $this->table('hiringreasons')->drop()->save();
        $this->table('menus')->drop()->save();
        $this->table('periods')->drop()->save();
        $this->table('professionalcategories')->drop()->save();
        $this->table('role_menus')->drop()->save();
        $this->table('roles')->drop()->save();
        $this->table('user_departments')->drop()->save();
        $this->table('users')->drop()->save();
        $this->table('validations')->drop()->save();
        $this->table('validationsequences')->drop()->save();
        $this->table('validationstatuses')->drop()->save();
        $this->table('worktimes')->drop()->save();
        $this->table('yesnos')->drop()->save();
    }

    /**
     * Crée les vues SQL de l'application (Workflow et ACL)
     *
     * @return void
     */
    protected function createApplicationViews(): void
    {
        // 1. Vue : applicationformstatuses
        $this->execute("
            CREATE OR REPLACE
            ALGORITHM = UNDEFINED VIEW `daetf`.`applicationformstatuses` AS
            select
                `af`.`id` AS `applicationform_id`,
                (case
                    when (count(`v`.`id`) > 0) then true
                    else false
                end) AS `has_validations`,
                (case
                    when (count(`v`.`id`) > 0) then (case
                        when (max(`v`.`validationstatus_id`) = 6) then 6
                        when (max(`v`.`validationstatus_id`) = 5) then 5
                        when ((min(`v`.`validationstatus_id`) >= 3)
                        and (max(`v`.`validationstatus_id`) <= 4)) then 4
                        when ((max(`v`.`validationstatus_id`) < 6)
                        and (sum((case when (`v`.`validationstatus_id` = 2) then 1 else 0 end)) > 0)) then 2
                        else 1
                    end)
                    else 1
                end) AS `validationstatus_id`,
                (case
                    when (count(`v`.`id`) > 0) then round(((sum((case when (`v`.`validationstatus_id` > 2) then 1 else 0 end)) / count(`v`.`id`)) * 100), 2)
                    else 0
                end) AS `valid_percentage`,
                (case
                    when (count(`vs`.`id`) > 0) then min(`vs`.`sequence`)
                    else NULL
                end) AS `current_sequence`,
                (case
                    when ((count(`v`.`id`) > 0)
                    and (max(`v`.`validationstatus_id`) <> 2)) then true
                    else false
                end) AS `en_cours`,
                (case
                    when ((count(`v`.`id`) > 0)
                    and (min(`v`.`validationstatus_id`) in (3, 4))
                    and (max(`v`.`validationstatus_id`) in (3, 4))) then true
                    else false
                end) AS `accepted`,
                (case
                    when ((count(`v`.`id`) > 0)
                    and (max(`v`.`validationstatus_id`) in (5, 6))) then true
                    else false
                end) AS `rejected`
            from
                ((`daetf`.`applicationforms` `af`
            left join `daetf`.`validations` `v` on
                ((`af`.`id` = `v`.`applicationform_id`)))
            left join `daetf`.`validationsequences` `vs` on
                (((`af`.`department_id` = `vs`.`department_id`)
                    and `vs`.`role_id` in (
                    select
                        `v1`.`role_id`
                    from
                        `daetf`.`validations` `v1`
                    where
                        ((`v1`.`applicationform_id` = `af`.`id`)
                            and (`v1`.`validationstatus_id` in (4, 3)))) is false)))
            group by
                `af`.`id`;
        ");

        // 2. Vue : currentvalidationroles
        $this->execute("
            CREATE OR REPLACE
            ALGORITHM = UNDEFINED VIEW `daetf`.`currentvalidationroles` AS
            select
                `a`.`id` AS `applicationform_id`,
                `a`.`department_id` AS `department_id`,
                `vs`.`role_id` AS `validator_role_id`,
                `vs`.`sequence` AS `validation_sequence`,
                `v`.`validationstatus_id` AS `validationstatus_id`,
                `daetf`.`a2`.`en_cours` AS `en_cours`,
                `daetf`.`a2`.`accepted` AS `accepted`,
                `daetf`.`a2`.`rejected` AS `rejected`
            from
                (((`daetf`.`applicationforms` `a`
            join `daetf`.`validationsequences` `vs` on
                ((`vs`.`department_id` = `a`.`department_id`)))
            join `daetf`.`applicationformstatuses` `a2` on
                (((0 <> `daetf`.`a2`.`has_validations`)
                    and (`a`.`id` = `daetf`.`a2`.`applicationform_id`)
                        and (`vs`.`sequence` = `daetf`.`a2`.`current_sequence`))))
            left join `daetf`.`validations` `v` on
                (((`v`.`applicationform_id` = `a`.`id`)
                    and (`v`.`role_id` = `vs`.`role_id`))));
        ");

        // 2. Vue : urds
        $this->execute("
            CREATE OR REPLACE
            ALGORITHM = UNDEFINED VIEW `daetf`.`urds` AS
            select
                `ud`.`user_id` AS `user_id`,
                `u`.`role_id` AS `role_id`,
                `ud`.`department_id` AS `department_id`
            from
                (`daetf`.`user_departments` `ud`
            join `daetf`.`users` `u` on
                ((`u`.`id` = `ud`.`user_id`)));
        ");

        // 2. Vue : validation_visas
        $this->execute("
            CREATE OR REPLACE
            ALGORITHM = UNDEFINED VIEW `daetf`.`validation_visas` AS
            select
                `af`.`id` AS `applicationform_id`,
                `vs`.`sequence` AS `sequence`,
                `vs`.`role_id` AS `role_id`,
                (case
                    when (`v`.`validationstatus_id` = 2) then ''
                    else concat(`u`.`firstname`, ' ', `u`.`lastname`)
                end) AS `op_name`,
                `r`.`name` AS `role_name`,
                `s`.`name` AS `status_name`,
                `v`.`modified` AS `validated_at`
            from
                (((((`daetf`.`applicationforms` `af`
            join `daetf`.`validationsequences` `vs` on
                ((`vs`.`department_id` = `af`.`department_id`)))
            join `daetf`.`roles` `r` on
                ((`r`.`id` = `vs`.`role_id`)))
            left join `daetf`.`validations` `v` on
                (((`v`.`applicationform_id` = `af`.`id`)
                    and (`v`.`role_id` = `vs`.`role_id`))))
            left join `daetf`.`users` `u` on
                ((`v`.`user_id` = `u`.`id`)))
            left join `daetf`.`validationstatuses` `s` on
                ((`s`.`id` = `v`.`validationstatus_id`)))
            where
                ((`af`.`deleted` is null)
                    and (`v`.`created` is not null))
            order by
                `af`.`id`,
                `vs`.`sequence`;
        ");
    }

}

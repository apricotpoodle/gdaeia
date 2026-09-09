# Agrégation de la Base de Données (Schéma & Migrations)

Ce document rassemble la structure exacte de la base de données de l'application (tables, vues SQL, clés étrangères et index FULLTEXT).

=== FILE: config/Migrations/20260702143600_Initial.php ===
```php
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

```
=== FILE: config/Migrations/20260814120000_AddFulltextIndexToApplicationforms.php ===
```php
<?php
declare(strict_types=1);

use Migrations\BaseMigration;

class AddFulltextIndexToApplicationforms extends BaseMigration
{
    /**
     * Change Method.
     *
     * @return void
     */
    public function change(): void
    {
        $table = $this->table('applicationforms');
        $table->addIndex(
            ['jobtitle', 'applicantname', 'qualification', 'reasonforreplacement'],
            [
                'name' => 'ft_applicationforms_global',
                'type' => 'fulltext',
            ]
        )->update();
    }
}
```
=== FILE: config/Migrations/20260820140000_CreateCommentsAndExtendApplicationforms.php ===
```php
<?php
declare(strict_types=1);

use Migrations\BaseMigration;

/**
 * Migration : Creation de la table centralisee 'comments' et extension d'applicationforms.
 */
class CreateCommentsAndExtendApplicationforms extends BaseMigration
{
    /**
     * Method Up : Applique les modifications sur la base de donnees.
     *
     * @return void
     */
    public function up(): void
    {
        // 1. Creation de la table centrale polymorphique des commentaires
        $comments = $this->table('comments');
        $comments
            ->addColumn('parent_id', 'integer', [
                'default' => null,
                'null' => true,
                'signed' => false,
                'comment' => 'ID du commentaire parent (0 ou NULL si premier niveau)',
            ])
            ->addColumn('model', 'string', [
                'limit' => 64,
                'null' => false,
                'comment' => 'Nom du modele associe (ex: Applicationforms)',
            ])
            ->addColumn('foreign_key', 'integer', [
                'null' => false,
                'signed' => false,
                'comment' => 'ID de l enregistrement lie dans le modele',
            ])
            ->addColumn('type', 'string', [
                'default' => 'GENERAL',
                'limit' => 32,
                'null' => false,
                'comment' => 'Type/Contexte (OBSERVATION, HIRING_REASON, PART_TIME, GENERAL)',
            ])
            ->addColumn('content', 'text', [
                'null' => false,
                'comment' => 'Contenu texte du commentaire',
            ])
            ->addColumn('user_id', 'integer', [
                'null' => false,
                'signed' => false,
                'comment' => 'ID de l auteur du commentaire',
            ])
            ->addColumn('created', 'timestamp', [
                'default' => 'CURRENT_TIMESTAMP',
                'null' => false,
                'comment' => 'Horodatage de creation',
            ])
            ->addColumn('modified', 'timestamp', [
                'default' => null,
                'null' => true,
                'comment' => 'Horodatage de derniere modification',
            ])
            ->addIndex(['model', 'foreign_key'], [
                'name' => 'idx_comments_polymorphic',
            ])
            ->addIndex(['parent_id'], [
                'name' => 'idx_comments_parent',
            ])
            ->addIndex(['user_id'], [
                'name' => 'idx_comments_user',
            ])
            ->addForeignKey('user_id', 'users', 'id', [
                'delete' => 'CASCADE',
                'update' => 'CASCADE',
                'constraint' => 'fk_comments_user',
            ])
            ->create();

        // 2. Extension de la table applicationforms
        $appForms = $this->table('applicationforms');
        $appForms
            ->addColumn('collaborator_id', 'biginteger', [
                'default' => null,
                'limit' => 11,
                'null' => true,
                'comment' => 'ID collaborateur interne concerne (CLB_ID)',
            ])
            ->addColumn('archived', 'datetime', [
                'default' => null,
                'null' => true,
                'comment' => 'Horodatage d archivage de la fiche (DAE_ARCHIVE_IND)',
            ])
            ->update();
    }

    /**
     * Method Down : Annule proprement les modifications.
     *
     * @return void
     */
    public function down(): void
    {
        // 1. Suppression de la table comments
        if ($this->hasTable('comments')) {
            $this->table('comments')->drop()->save();
        }

        // 2. Retrait des colonnes ajoutees dans applicationforms
        $appForms = $this->table('applicationforms');
        if ($appForms->hasColumn('collaborator_id')) {
            $appForms->removeColumn('collaborator_id');
        }
        if ($appForms->hasColumn('archived')) {
            $appForms->removeColumn('archived');
        }
        $appForms->update();
    }
}
```
=== FILE: config/schema/schema_dump.sql ===
```SQL
-- daetf.applicationforms definition

CREATE TABLE `applicationforms` (
  `id` int unsigned NOT NULL AUTO_INCREMENT COMMENT 'Identifiant unique de la demande de recrutement',
  `department_id` int NOT NULL COMMENT 'Identifiant du département/service rattaché',
  `user_id` int NOT NULL COMMENT 'Identifiant de l''utilisateur émetteur/demandeur',
  `cgr` varchar(255) DEFAULT NULL COMMENT 'Code analytique CGR d''affectation',
  `contracttype_id` int NOT NULL COMMENT 'Identifiant du type de contrat (CDI, CDD...)',
  `hiringreason_id` int NOT NULL COMMENT 'Identifiant du motif principal d''embauche',
  `reasonforreplacement` varchar(255) DEFAULT NULL COMMENT 'Précision sur la personne ou le motif en cas de remplacement',
  `budgetfeature_id` int NOT NULL DEFAULT '1' COMMENT 'Identifiant de la caractéristique budgétaire (Au/Hors budget)',
  `jobtitle` varchar(255) NOT NULL COMMENT 'Intitulé ou description du poste à pourvoir',
  `professionalcategory_id` int NOT NULL COMMENT 'Identifiant de la catégorie professionnelle (Cadre, Employé...)',
  `worktime_id` int NOT NULL COMMENT 'Identifiant du régime de temps de travail (Complet/Partiel)',
  `workingtimedistribution` varchar(255) DEFAULT NULL COMMENT 'Répartition horaire du temps de travail',
  `grossremuneration` decimal(19, 4) NOT NULL DEFAULT '0.0000' COMMENT 'Montant de la rémunération brute',
  `period_id` int NOT NULL COMMENT 'Identifiant de la périodicité de rémunération (Mensuel, Annuel...)',
  `qualification` varchar(255) DEFAULT NULL COMMENT 'Niveau de qualification ou diplôme requis',
  `begin_at` date DEFAULT NULL COMMENT 'Date de début souhaitée du contrat',
  `end_at` date DEFAULT NULL COMMENT 'Date de fin de contrat (si CDD)',
  `applicantname` varchar(255) DEFAULT NULL COMMENT 'Nom et prénom du salarié pressenti',
  `yesno_id` int NOT NULL COMMENT 'Identifiant d''option binaire/validation',
  `deleted` datetime DEFAULT NULL COMMENT 'Horodatage de suppression logique (Soft Delete)',
  `created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Horodatage de création de la fiche',
  `modified` timestamp NULL DEFAULT NULL ON
UPDATE
    CURRENT_TIMESTAMP COMMENT 'Horodatage de dernière modification',
    `collaborator_id` bigint DEFAULT NULL COMMENT 'ID collaborateur interne concerne (CLB_ID)',
    `archived` datetime DEFAULT NULL COMMENT 'Horodatage d archivage de la fiche (DAE_ARCHIVE_IND)',
    PRIMARY KEY (`id`),
    KEY `applicationforms_department_id_IDX` (`department_id`)
        USING BTREE,
    FULLTEXT KEY `ft_applicationforms_global` (`jobtitle`,
    `applicantname`,
    `qualification`,
    `reasonforreplacement`)
) ENGINE = InnoDB AUTO_INCREMENT = 43 DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_0900_ai_ci;
-- daetf.applicationvalidationsteps definition

CREATE TABLE `applicationvalidationsteps` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `applicationform_id` int unsigned NOT NULL,
  `role_id` int unsigned NOT NULL,
  `validationstatus_id` int unsigned NOT NULL,
  `comment` varchar(100) DEFAULT NULL,
  `validationsequence_id` int unsigned NOT NULL,
  `deleted` datetime DEFAULT NULL,
  `modified` timestamp NULL DEFAULT NULL ON
UPDATE
    CURRENT_TIMESTAMP,
    `created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`)
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_0900_ai_ci COMMENT = 'gère chaque étape de validation d''une demande';
-- daetf.budgetfeatures definition

CREATE TABLE `budgetfeatures` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `base` tinyint(1) NOT NULL DEFAULT '0',
  `code` varchar(16) NOT NULL,
  `name` varchar(32) NOT NULL,
  `sort` varchar(32) NOT NULL DEFAULT '',
  `deleted` datetime DEFAULT NULL,
  `created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `modified` timestamp NULL DEFAULT NULL ON
UPDATE
    CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `code` (`code`),
    UNIQUE KEY `name` (`name`),
    KEY `sort` (`sort`)
) ENGINE = InnoDB AUTO_INCREMENT = 3 DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_0900_ai_ci;
-- daetf.cgr_strategies definition

CREATE TABLE `cgr_strategies` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `code` varchar(32) NOT NULL COMMENT 'Code technique unique (ex: STANDARD, SEM)',
  `name` varchar(64) NOT NULL COMMENT 'Nom lisible de la stratégie',
  `definition_json` json NOT NULL COMMENT 'Configuration JSON des champs requis',
  `created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `modified` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `code` (`code`)
) ENGINE = InnoDB AUTO_INCREMENT = 4 DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_0900_ai_ci COMMENT = 'Définit les stratégies de construction du CGR';
-- daetf.contracttypes definition

CREATE TABLE `contracttypes` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `base` tinyint(1) NOT NULL DEFAULT '0',
  `code` varchar(16) NOT NULL,
  `name` varchar(32) NOT NULL,
  `sort` varchar(32) NOT NULL DEFAULT '',
  `deleted` datetime DEFAULT NULL,
  `created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `modified` timestamp NULL DEFAULT NULL ON
UPDATE
    CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `code` (`code`),
    UNIQUE KEY `name` (`name`),
    KEY `sort` (`sort`)
) ENGINE = InnoDB AUTO_INCREMENT = 6 DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_0900_ai_ci;
-- daetf.email_logs definition

CREATE TABLE `email_logs` (
  `id` int NOT NULL AUTO_INCREMENT,
  `subject` varchar(255) NOT NULL,
  `content_text` text,
  `content_html` text,
  `error_message` text,
  `created` datetime DEFAULT CURRENT_TIMESTAMP,
  `modified` datetime DEFAULT CURRENT_TIMESTAMP ON
UPDATE
    CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`)
) ENGINE = InnoDB AUTO_INCREMENT = 638 DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_0900_ai_ci COMMENT = 'Table enregistrant les informations des e-mails envoyés, y compris le sujet, le contenu en texte et en HTML, ainsi que les messages d''erreur éventuels.';
-- daetf.hiringreasons definition

CREATE TABLE `hiringreasons` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `base` tinyint(1) NOT NULL DEFAULT '0',
  `code` varchar(16) NOT NULL,
  `name` varchar(32) NOT NULL,
  `sort` varchar(32) NOT NULL DEFAULT '',
  `created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `modified` timestamp NULL DEFAULT NULL ON
UPDATE
    CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `code` (`code`),
    UNIQUE KEY `name` (`name`),
    KEY `sort` (`sort`)
) ENGINE = InnoDB AUTO_INCREMENT = 4 DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_0900_ai_ci;
-- daetf.menus definition

CREATE TABLE `menus` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `parent_id` int DEFAULT NULL,
  `lft` int NOT NULL,
  `rght` int NOT NULL,
  `level` int DEFAULT '0',
  `name` varchar(255) DEFAULT NULL,
  `url` varchar(255) DEFAULT NULL,
  `active` tinyint(1) DEFAULT NULL,
  `disabled` tinyint(1) DEFAULT NULL,
  `dividor_before` tinyint(1) DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `idx_lft` (`lft`),
  KEY `parent_id` (`parent_id`)
) ENGINE = InnoDB AUTO_INCREMENT = 24 DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_0900_ai_ci;
-- daetf.periods definition

CREATE TABLE `periods` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `base` tinyint(1) NOT NULL DEFAULT '0',
  `code` varchar(16) NOT NULL,
  `name` varchar(32) NOT NULL,
  `sort` varchar(32) NOT NULL DEFAULT '',
  `deleted` datetime DEFAULT NULL,
  `created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `modified` timestamp NULL DEFAULT NULL ON
UPDATE
    CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `code` (`code`),
    UNIQUE KEY `name` (`name`),
    KEY `sort` (`sort`)
) ENGINE = InnoDB AUTO_INCREMENT = 4 DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_0900_ai_ci;
-- daetf.phinxlog definition

CREATE TABLE `phinxlog` (
  `version` bigint NOT NULL,
  `migration_name` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `start_time` timestamp NULL DEFAULT NULL,
  `end_time` timestamp NULL DEFAULT NULL,
  `breakpoint` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`version`)
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_unicode_ci;
-- daetf.professionalcategories definition

CREATE TABLE `professionalcategories` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `base` tinyint(1) NOT NULL DEFAULT '0',
  `code` varchar(16) NOT NULL,
  `name` varchar(32) NOT NULL,
  `sort` varchar(32) NOT NULL DEFAULT '',
  `deleted` datetime DEFAULT NULL,
  `created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `modified` timestamp NULL DEFAULT NULL ON
UPDATE
    CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `code` (`code`),
    UNIQUE KEY `name` (`name`),
    KEY `sort` (`sort`)
) ENGINE = InnoDB AUTO_INCREMENT = 4 DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_0900_ai_ci;
-- daetf.role_menus definition

CREATE TABLE `role_menus` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `role_id` int NOT NULL,
  `menu_id` int NOT NULL,
  `department_id` int unsigned DEFAULT NULL,
  `created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `modified` timestamp NULL DEFAULT NULL ON
UPDATE
    CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `role_menus_role_menu_dept_UN` (`role_id`,
    `menu_id`,
    `department_id`),
    KEY `user_id` (`role_id`),
    KEY `menu_id` (`menu_id`),
    KEY `idx_role_menus_dept` (`department_id`)
) ENGINE = InnoDB AUTO_INCREMENT = 43 DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_0900_ai_ci;
-- daetf.roles definition

CREATE TABLE `roles` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `base` tinyint(1) NOT NULL DEFAULT '0',
  `code` varchar(16) NOT NULL,
  `name` varchar(64) NOT NULL,
  `sort` varchar(64) NOT NULL DEFAULT '',
  `deleted` datetime DEFAULT NULL,
  `created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `modified` timestamp NULL DEFAULT NULL ON
UPDATE
    CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `code` (`code`),
    UNIQUE KEY `name` (`name`),
    KEY `sort` (`sort`)
) ENGINE = InnoDB AUTO_INCREMENT = 8 DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_0900_ai_ci;
-- daetf.user_departments definition

CREATE TABLE `user_departments` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `department_id` int NOT NULL,
  `created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `modified` timestamp NULL DEFAULT NULL ON
UPDATE
    CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `user_departments_user_id_IDX` (`user_id`,
    `department_id`)
        USING BTREE,
    KEY `user_id` (`user_id`),
    KEY `department_id` (`department_id`)
) ENGINE = InnoDB AUTO_INCREMENT = 1728 DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_0900_ai_ci;
-- daetf.users definition

CREATE TABLE `users` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `username` varchar(255) DEFAULT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `firstname` varchar(255) DEFAULT NULL,
  `lastname` varchar(255) DEFAULT NULL,
  `token` varchar(255) DEFAULT NULL,
  `issuperuser` tinyint(1) NOT NULL DEFAULT '0',
  `role_id` int NOT NULL DEFAULT '1',
  `token_expires` timestamp NULL DEFAULT NULL,
  `deleted` datetime DEFAULT NULL,
  `created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `modified` timestamp NULL DEFAULT NULL ON
UPDATE
    CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`)
) ENGINE = InnoDB AUTO_INCREMENT = 165 DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_0900_ai_ci;
-- daetf.validations definition

CREATE TABLE `validations` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `applicationform_id` int NOT NULL,
  `user_id` int NOT NULL,
  `role_id` int NOT NULL,
  `validated` datetime DEFAULT NULL,
  `validationstatus_id` int DEFAULT NULL,
  `obs` varchar(255) DEFAULT NULL,
  `deleted` datetime DEFAULT NULL,
  `created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `modified` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON
UPDATE
    CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    KEY `applicationform_id` (`applicationform_id`),
    KEY `validated` (`validationstatus_id`)
) ENGINE = InnoDB AUTO_INCREMENT = 1423 DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_0900_ai_ci;
-- daetf.validationsequences definition

CREATE TABLE `validationsequences` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `department_id` int unsigned NOT NULL,
  `name` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci NOT NULL DEFAULT '',
  `description` text,
  `role_id` int unsigned NOT NULL COMMENT 'Rôle Requis pour valider l''étape',
  `sequence` int NOT NULL DEFAULT '1' COMMENT 'ordre sequentel de validation',
  `deleted` datetime DEFAULT NULL,
  `modified` timestamp NULL DEFAULT NULL ON
UPDATE
    CURRENT_TIMESTAMP,
    `created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `validationsequences_UN` (`department_id`,
    `role_id`),
    KEY `validationsequences_department_id_IDX` (`department_id`,
    `sequence`)
        USING BTREE
) ENGINE = InnoDB AUTO_INCREMENT = 1289 DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_0900_ai_ci COMMENT = 'définit l''ordre des séquences pour chaque department.';
-- daetf.validationstatuses definition

CREATE TABLE `validationstatuses` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `code` varchar(100) NOT NULL,
  `name` varchar(100) DEFAULT NULL,
  `deleted` datetime DEFAULT NULL,
  `modified` timestamp NULL DEFAULT NULL ON
UPDATE
    CURRENT_TIMESTAMP,
    `created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`)
) ENGINE = InnoDB AUTO_INCREMENT = 7 DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_0900_ai_ci COMMENT = 'Table de référence des statuts de validation';
-- daetf.worktimes definition

CREATE TABLE `worktimes` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `base` tinyint(1) NOT NULL DEFAULT '0',
  `code` varchar(16) NOT NULL,
  `name` varchar(32) NOT NULL,
  `sort` varchar(32) NOT NULL DEFAULT '',
  `created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `modified` timestamp NULL DEFAULT NULL ON
UPDATE
    CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `code` (`code`),
    UNIQUE KEY `name` (`name`),
    KEY `sort` (`sort`)
) ENGINE = InnoDB AUTO_INCREMENT = 3 DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_0900_ai_ci;
-- daetf.yesnos definition

CREATE TABLE `yesnos` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `base` tinyint(1) NOT NULL DEFAULT '0',
  `code` varchar(16) NOT NULL,
  `name` varchar(32) NOT NULL,
  `sort` varchar(32) NOT NULL DEFAULT '',
  `created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `modified` timestamp NULL DEFAULT NULL ON
UPDATE
    CURRENT_TIMESTAMP,
    `deleted` datetime DEFAULT NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `code` (`code`),
    UNIQUE KEY `name` (`name`),
    KEY `sort` (`sort`)
) ENGINE = InnoDB AUTO_INCREMENT = 3 DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_0900_ai_ci;
-- daetf.comments definition

CREATE TABLE `comments` (
  `id` int NOT NULL AUTO_INCREMENT,
  `parent_id` int unsigned DEFAULT NULL COMMENT 'ID du commentaire parent (0 ou NULL si premier niveau)',
  `model` varchar(64) NOT NULL COMMENT 'Nom du modele associe (ex: Applicationforms)',
  `foreign_key` int unsigned NOT NULL COMMENT 'ID de l enregistrement lie dans le modele',
  `type` varchar(32) NOT NULL DEFAULT 'GENERAL' COMMENT 'Type/Contexte (OBSERVATION, HIRING_REASON, PART_TIME, GENERAL)',
  `content` text NOT NULL COMMENT 'Contenu texte du commentaire',
  `user_id` int unsigned NOT NULL COMMENT 'ID de l auteur du commentaire',
  `created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'Horodatage de creation',
  `modified` timestamp NULL DEFAULT NULL COMMENT 'Horodatage de derniere modification',
  PRIMARY KEY (`id`),
  KEY `idx_comments_polymorphic` (`model`,
`foreign_key`),
  KEY `idx_comments_parent` (`parent_id`),
  KEY `idx_comments_user` (`user_id`),
  CONSTRAINT `fk_comments_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON
DELETE
    CASCADE ON
    UPDATE
        CASCADE
) ENGINE = InnoDB AUTO_INCREMENT = 10 DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_0900_ai_ci;
-- daetf.email_recipients definition

CREATE TABLE `email_recipients` (
  `id` int NOT NULL AUTO_INCREMENT,
  `email_log_id` int NOT NULL,
  `recipient_email` varchar(255) NOT NULL,
  PRIMARY KEY (`id`),
  KEY `emaillog_id` (`email_log_id`),
  CONSTRAINT `email_recipients_ibfk_1` FOREIGN KEY (`email_log_id`) REFERENCES `email_logs` (`id`) ON
DELETE
    CASCADE
) ENGINE = InnoDB AUTO_INCREMENT = 2006 DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_0900_ai_ci COMMENT = 'Table contenant les destinataires des e-mails envoyés, avec une référence à l''e-mail correspondant dans la table emaillogs.';
-- daetf.field_authorizations definition

CREATE TABLE `field_authorizations` (
  `id` int NOT NULL AUTO_INCREMENT,
  `role_id` int unsigned NOT NULL,
  `resource` varchar(50) NOT NULL,
  `field` varchar(50) NOT NULL,
  `access_level` varchar(20) NOT NULL DEFAULT 'EDIT',
  `created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `modified` timestamp NULL DEFAULT NULL ON
UPDATE
    CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `role_id` (`role_id`,
    `resource`,
    `field`),
    CONSTRAINT `field_authorizations_ibfk_1` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON
    DELETE
        CASCADE ON
        UPDATE
            CASCADE
) ENGINE = InnoDB AUTO_INCREMENT = 109 DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_0900_ai_ci;
-- daetf.cgr_codes definition

CREATE TABLE `cgr_codes` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `department_id` int unsigned NOT NULL COMMENT 'Lien vers l''Entité propriétaire',
  `type` varchar(32) NOT NULL COMMENT 'Type de zone (SERVICE, TITRE...)',
  `code` varchar(16) NOT NULL COMMENT 'La valeur courte (ex: S01)',
  `label` varchar(255) NOT NULL COMMENT 'Libellé complet',
  `active` tinyint(1) NOT NULL DEFAULT '1',
  `is_system` tinyint(1) NOT NULL DEFAULT '0',
  `created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `modified` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `idx_cgr_codes_unique_definition` (`department_id`,
`type`,
`code`),
  CONSTRAINT `fk_cgr_codes_department_id` FOREIGN KEY (`department_id`) REFERENCES `departments` (`id`) ON
DELETE
    CASCADE ON
    UPDATE
        CASCADE
) ENGINE = InnoDB AUTO_INCREMENT = 9 DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_0900_ai_ci COMMENT = 'Dictionnaire des valeurs analytiques';
-- daetf.departments definition

CREATE TABLE `departments` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `parent_id` int DEFAULT NULL,
  `cgr_code_id` int unsigned DEFAULT NULL,
  `lft` int DEFAULT NULL,
  `rght` int DEFAULT NULL,
  `level` int DEFAULT '0',
  `base` tinyint(1) NOT NULL DEFAULT '1',
  `code` varchar(32) NOT NULL,
  `name` varchar(64) NOT NULL,
  `sort` varchar(64) DEFAULT '',
  `department_type_id` int NOT NULL DEFAULT '1',
  `cgr_strategy_id` int unsigned DEFAULT NULL COMMENT 'Référence à la stratégie CGR',
  `default_cgr` varchar(255) DEFAULT NULL COMMENT 'CGR par défaut pré-calculé',
  `current_manager_id` int unsigned DEFAULT NULL COMMENT 'identifiant responsable de service',
  `deleted` datetime DEFAULT NULL,
  `created` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `modified` timestamp NULL DEFAULT NULL ON
UPDATE
    CURRENT_TIMESTAMP,
    PRIMARY KEY (`id`),
    UNIQUE KEY `code` (`code`),
    UNIQUE KEY `name` (`name`),
    UNIQUE KEY `code_2` (`code`),
    UNIQUE KEY `name_2` (`name`),
    KEY `sort` (`sort`),
    KEY `sort_2` (`sort`),
    KEY `idx_lft` (`lft`),
    KEY `parent_id` (`parent_id`),
    KEY `fk_departments_cgr_strategy_id` (`cgr_strategy_id`),
    KEY `fk_departments_cgr_codes` (`cgr_code_id`),
    CONSTRAINT `fk_departments_cgr_codes` FOREIGN KEY (`cgr_code_id`) REFERENCES `cgr_codes` (`id`) ON
    DELETE
        SET
        NULL ON
        UPDATE
            CASCADE,
            CONSTRAINT `fk_departments_cgr_strategy_id` FOREIGN KEY (`cgr_strategy_id`) REFERENCES `cgr_strategies` (`id`) ON
            DELETE
                SET
                NULL ON
                UPDATE
                    CASCADE
) ENGINE = InnoDB AUTO_INCREMENT = 73 DEFAULT CHARSET = utf8mb4 COLLATE = utf8mb4_0900_ai_ci;

-- daetf.applicationformstatuses source

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
-- daetf.currentvalidationroles source

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
-- daetf.urds source

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
-- daetf.validation_visas source

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

```

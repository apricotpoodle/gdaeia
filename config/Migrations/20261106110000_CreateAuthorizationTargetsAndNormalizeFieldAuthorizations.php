<?php
declare(strict_types=1);

use Migrations\BaseMigration;

/** Stabilise le référentiel des cibles d'autorisation. */
final class CreateAuthorizationTargetsAndNormalizeFieldAuthorizations extends BaseMigration
{
    /** Crée les cibles fonctionnelles et migre les autorisations historiques. */
    public function up(): void
    {
        $this->table('authorization_targets')
            ->addColumn('resource', 'string', ['limit' => 80, 'null' => false])
            ->addColumn('target_type', 'string', ['limit' => 20, 'null' => false])
            ->addColumn('target', 'string', ['limit' => 80, 'null' => false])
            ->addColumn('label', 'string', ['limit' => 160, 'null' => false])
            ->addColumn('description', 'text', ['null' => true, 'default' => null])
            ->addColumn('active', 'boolean', ['default' => true, 'null' => false])
            ->addColumn('position', 'integer', ['default' => 0, 'null' => false])
            ->addColumn('created', 'datetime', ['null' => false])
            ->addColumn('modified', 'datetime', ['null' => false])
            ->addIndex(['resource', 'target_type', 'target'], ['unique' => true, 'name' => 'uq_authorization_targets_identity'])
            ->addIndex(['resource', 'target_type', 'active', 'position'], ['name' => 'idx_authorization_targets_listing'])
            ->create();

        $now = date('Y-m-d H:i:s');
        $this->table('authorization_targets')->insert([
            ['resource' => 'Applicationforms', 'target_type' => 'section', 'target' => 'job_section', 'label' => 'Zone métier', 'description' => 'Zones administration, contrat et rémunération.', 'active' => true, 'position' => 10, 'created' => $now, 'modified' => $now],
            ['resource' => 'Applicationforms', 'target_type' => 'section', 'target' => 'rh_section', 'label' => 'Zone RH et administration', 'description' => 'Zone réservée aux ressources humaines et à l’administration.', 'active' => true, 'position' => 20, 'created' => $now, 'modified' => $now],
        ])->save();

        $authorizations = $this->table('field_authorizations');
        $authorizations->addColumn('target_type', 'string', ['limit' => 20, 'default' => 'field', 'null' => false])->update();
        $this->execute("UPDATE field_authorizations SET target_type = CASE WHEN field IN ('job_section', 'rh_section') THEN 'section' ELSE 'field' END");
        $authorizations
            ->removeIndexByName('role_id')
            ->renameColumn('field', 'target')
            ->addIndex(['role_id', 'resource', 'target_type', 'target'], ['unique' => true, 'name' => 'uq_field_authorizations_target'])
            ->update();
    }

    /** Restaure le nom historique et supprime le référentiel ajouté. */
    public function down(): void
    {
        $authorizations = $this->table('field_authorizations');
        $authorizations
            ->removeIndexByName('uq_field_authorizations_target')
            ->renameColumn('target', 'field')
            ->removeColumn('target_type')
            ->addIndex(['role_id', 'resource', 'field'], ['unique' => true, 'name' => 'role_id'])
            ->update();
        $this->table('authorization_targets')->drop();
    }
}

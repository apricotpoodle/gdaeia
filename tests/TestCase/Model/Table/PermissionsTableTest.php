<?php
declare(strict_types=1);

namespace App\Test\TestCase\Model\Table;

use Cake\Database\Connection;
use Cake\TestSuite\TestCase;

final class PermissionsTableTest extends TestCase
{
    protected array $fixtures = [
        'app.Roles',
        'app.Permissions',
        'app.PermissionScopes',
        'app.Role1Permissions',
        'app.Menus',
    ];

    public function testLeCatalogueCompletChargeLesAttributionsDuRoleEtLesMenusDeLaPermission(): void
    {
        $permissions = $this->getTableLocator()->get('Permissions');
        $connection = $permissions->getConnection();
        $connection->insert('roles', ['id' => 2, 'code' => 'dem', 'name' => 'Demandeur', 'sort' => '2']);
        $connection->insert('role_permissions', ['role_id' => 2, 'permission_id' => 5, 'scope_type' => 'self']);
        $this->ajouterLeMenuDesDemandes($connection);

        $catalogue = $permissions->find('catalog', roleId: 2)->all()->indexBy('id')->toArray();

        self::assertEqualsCanonicalizing(range(1, 42), array_keys($catalogue));
        self::assertEqualsCanonicalizing(
            ['self', 'assigned', 'all'],
            array_column($catalogue[5]->get('permission_scopes'), 'scope_type'),
        );
        self::assertSame([2], array_column($catalogue[5]->get('role_permissions'), 'role_id'));
        self::assertSame(['self'], array_column($catalogue[5]->get('role_permissions'), 'scope_type'));
        self::assertSame([], $catalogue[1]->get('role_permissions'));

        $menus = $catalogue[5]->get('menus');
        self::assertSame([2], array_column($menus, 'id'));
        self::assertSame(5, $menus[0]->get('required_permission_id'));
        self::assertSame([], $catalogue[1]->get('menus'));
    }

    public function testLeCatalogueSansHydratationExposeSeulementLesChampsPublicsDuMenuLie(): void
    {
        $permissions = $this->getTableLocator()->get('Permissions');
        $this->ajouterLeMenuDesDemandes($permissions->getConnection());

        $catalogue = $permissions->find('catalog', roleId: 1)->disableHydration()->all()->indexBy('id')->toArray();

        self::assertEqualsCanonicalizing(range(1, 42), array_keys($catalogue));
        $menus = $catalogue[5]['menus'];
        self::assertEquals([['id' => 2, 'name' => 'Demandes', 'url' => '/applicationforms']], $menus);
        self::assertSame([], $catalogue[1]['menus']);
    }

    public function testLesFiltresLiesPreserventLaCardinaliteDuCatalogueEtIsolentLeRole(): void
    {
        /** @var \App\Model\Table\PermissionsTable $permissions */
        $permissions = $this->getTableLocator()->get('Permissions');
        $connection = $permissions->getConnection();
        $connection->insert('roles', ['id' => 2, 'code' => 'dem', 'name' => 'Demandeur', 'sort' => '2']);
        $connection->insert('role_permissions', ['role_id' => 2, 'permission_id' => 5, 'scope_type' => 'self']);
        $this->ajouterLeMenuDesDemandes($connection);
        $connection->insert('menus', [
            'id' => 3,
            'lft' => 5,
            'rght' => 6,
            'name' => 'Archives',
            'url' => '/applicationforms/archives',
            'required_permission_id' => 5,
        ]);

        $query = $permissions->find('catalog', roleId: 2)
            ->where($permissions->resolveCatalogRelationFilter('Menus', '/applicationforms', 'like'))
            ->where($permissions->resolveCatalogRelationFilter('PermissionScopes', 's', 'like'));

        $catalogue = (clone $query)
            ->where($permissions->resolveCatalogRelationFilter('RolePermissions', 'self', '=', 2))
            ->all()
            ->toList();

        self::assertSame([5], array_column($catalogue, 'id'));
        self::assertEqualsCanonicalizing([2, 3], array_column($catalogue[0]->get('menus'), 'id'));
        self::assertSame(0, $query
            ->where($permissions->resolveCatalogRelationFilter('RolePermissions', 'assigned', '=', 2))
            ->count());
    }

    /** Ajoute une option liée à la permission de consultation des demandes. */
    private function ajouterLeMenuDesDemandes(Connection $connection): void
    {
        $connection->insert('menus', [
            'id' => 2,
            'lft' => 3,
            'rght' => 4,
            'name' => 'Demandes',
            'url' => '/applicationforms',
            'required_permission_id' => 5,
        ]);
    }
}

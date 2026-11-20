<?php
declare(strict_types=1);

namespace App\Test\TestCase\Controller\Api;

use App\Model\Entity\User;
use Cake\Datasource\ConnectionManager;
use Cake\TestSuite\IntegrationTestTrait;
use Cake\TestSuite\TestCase;
use PHPUnit\Framework\Attributes\DataProvider;

final class PermissionsControllerTest extends TestCase
{
    use IntegrationTestTrait;

    protected array $fixtures = [
        'app.Users',
        'app.Roles',
        'app.Permissions',
        'app.PermissionScopes',
        'app.Role1Permissions',
        'app.Menus',
        'app.RoleMenus',
    ];

    public function testLeCatalogueEstAccessibleAuSuperAdministrateur(): void
    {
        $this->session(['Auth' => new User(['id' => 1, 'issuperuser' => true, 'role_id' => 1])]);
        $this->ajouterLesMenusLiesAuxDemandes();

        $this->get('/api/permissions.json');

        $this->assertResponseOk();
        $payload = (array)json_decode((string)$this->_response->getBody(), true);
        self::assertTrue($payload['success']);
        self::assertEqualsCanonicalizing(range(1, 42), array_column($payload['data'], 'id'));
        $permission = array_column($payload['data'], null, 'code')['applicationforms.read'];
        self::assertSame('applicationforms', $permission['resource']);
        self::assertSame('read', $permission['action']);
        self::assertSame('Consulter les demandes', $permission['label']);
        self::assertEqualsCanonicalizing(
            ['self', 'assigned', 'all'],
            array_column($permission['permission_scopes'], 'scope_type'),
        );
        self::assertArrayHasKey('role_permissions', $permission);
        self::assertSame([], $permission['role_permissions']);
        self::assertEqualsCanonicalizing([2, 3], array_column($permission['menus'], 'id'));
    }

    #[DataProvider('requetesInterdites')]
    public function testUnOperateurNePeutPasConsulterLeCatalogue(string $query): void
    {
        $this->session(['Auth' => new User(['id' => 2, 'issuperuser' => false, 'role_id' => User::ROLE_DEMANDEUR])]);

        $this->get('/api/permissions.json' . $query);

        $this->assertResponseCode(403);
    }

    /** @return array<string, array{string}> */
    public static function requetesInterdites(): array
    {
        return [
            'sans rôle' => [''],
            'avec un rôle existant' => ['?role_id=1'],
            'avec un rôle inconnu' => ['?role_id=9999'],
        ];
    }

    public function testLeCatalogueExposeLesAttributionsDuRoleChoisiEtLesMenusLies(): void
    {
        $this->session(['Auth' => new User(['id' => 1, 'issuperuser' => false, 'role_id' => 1])]);
        $connection = ConnectionManager::get('test');
        $connection->insert('roles', ['id' => 2, 'code' => 'dem', 'name' => 'Demandeur', 'sort' => '2']);
        $connection->insert('role_permissions', ['role_id' => 1, 'permission_id' => 3, 'scope_type' => 'unscoped']);
        $connection->insert('role_permissions', ['role_id' => 2, 'permission_id' => 5, 'scope_type' => 'self']);
        $connection->insert('role_menus', ['role_id' => 2, 'menu_id' => 1]);
        $this->ajouterLesMenusLiesAuxDemandes();

        $this->get('/api/permissions.json?role_id=2');

        $this->assertResponseOk();
        $payload = (array)json_decode((string)$this->_response->getBody(), true);
        self::assertTrue($payload['success']);
        self::assertEqualsCanonicalizing(range(1, 42), array_column($payload['data'], 'id'));
        $permissions = array_column($payload['data'], null, 'code');
        $lectureDemandes = $permissions['applicationforms.read'];
        self::assertEqualsCanonicalizing(
            ['self', 'assigned', 'all'],
            array_column($lectureDemandes['permission_scopes'], 'scope_type'),
        );
        self::assertArrayHasKey('role_permissions', $lectureDemandes);
        self::assertSame([2], array_column($lectureDemandes['role_permissions'], 'role_id'));
        self::assertSame([5], array_column($lectureDemandes['role_permissions'], 'permission_id'));
        self::assertSame(['self'], array_column($lectureDemandes['role_permissions'], 'scope_type'));
        self::assertSame([], $permissions['departments.read']['role_permissions']);
        self::assertSame([], $permissions['departments.read']['menus']);
        $menus = array_column($lectureDemandes['menus'], null, 'id');
        ksort($menus);
        self::assertEquals([
            2 => ['id' => 2, 'name' => 'Demandes', 'url' => '/applicationforms'],
            3 => ['id' => 3, 'name' => 'Archives', 'url' => '/applicationforms/archives'],
        ], $menus);
    }

    #[DataProvider('rolesInvalides')]
    public function testUnRoleInvalideEstRefuse(string $query): void
    {
        $this->session(['Auth' => new User(['id' => 1, 'issuperuser' => true, 'role_id' => 1])]);

        $this->get('/api/permissions.json' . $query);

        $this->assertResponseCode(422);
        $payload = (array)json_decode((string)$this->_response->getBody(), true);
        self::assertFalse($payload['success']);
        self::assertNotEmpty($payload['message']);
        self::assertIsArray($payload['errors']);
    }

    /** @return array<string, array{string}> */
    public static function rolesInvalides(): array
    {
        return [
            'rôle inconnu' => ['?role_id=9999'],
            'valeur vide' => ['?role_id='],
            'identifiant nul' => ['?role_id=0'],
            'valeur non entière' => ['?role_id=1.5'],
            'tableau' => ['?role_id%5B%5D=1'],
        ];
    }

    public function testLeCatalogueWebEstVisibleAuSuperAdministrateur(): void
    {
        $this->session(['Auth' => new User(['id' => 1, 'issuperuser' => true, 'role_id' => 1])]);

        $this->get('/permissions');

        $this->assertResponseOk();
        $this->assertResponseContains('permissions-catalog');
    }

    public function testLeSuperAdministrateurPeutAttribuerUnPerimetreAdmissible(): void
    {
        $this->session(['Auth' => new User(['id' => 1, 'issuperuser' => true, 'role_id' => 1])]);
        $this->enableCsrfToken();
        $this->configRequest(['headers' => ['Accept' => 'application/json']]);

        $this->post('/api/permissions/assign.json', [
            'role_id' => 1,
            'permission_id' => 1,
            'scope_type' => 'assigned',
        ]);

        $this->assertResponseOk();
        self::assertSame(1, $this->getTableLocator()->get('RolePermissions')->find()->where([
            'role_id' => 1,
            'permission_id' => 1,
            'scope_type' => 'assigned',
        ])->count());
    }

    /** Insère deux destinations pour une même permission, dont une désactivée. */
    private function ajouterLesMenusLiesAuxDemandes(): void
    {
        $connection = ConnectionManager::get('test');
        $connection->insert('menus', [
            'id' => 2,
            'lft' => 3,
            'rght' => 4,
            'name' => 'Demandes',
            'url' => '/applicationforms',
            'active' => 1,
            'required_permission_id' => 5,
        ]);
        $connection->insert('menus', [
            'id' => 3,
            'lft' => 5,
            'rght' => 6,
            'name' => 'Archives',
            'url' => '/applicationforms/archives',
            'active' => 0,
            'required_permission_id' => 5,
        ]);
    }
}

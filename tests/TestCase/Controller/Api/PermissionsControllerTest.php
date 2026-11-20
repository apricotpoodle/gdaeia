<?php
declare(strict_types=1);

namespace App\Test\TestCase\Controller\Api;

use App\Model\Entity\User;
use Cake\Datasource\ConnectionManager;
use Cake\TestSuite\IntegrationTestTrait;
use Cake\TestSuite\TestCase;
use DOMDocument;
use DOMXPath;
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
        self::assertArrayNotHasKey('last_page', $payload);
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
            'avec pagination' => ['?role_id=1&page=1&size=20'],
        ];
    }

    public function testLeCataloguePagineTrieEtFiltreLesPermissionsSansOmettreLesNonAttribuees(): void
    {
        $this->session(['Auth' => new User(['id' => 1, 'issuperuser' => true, 'role_id' => 1])]);
        ConnectionManager::get('test')->insert('roles', ['id' => 2, 'code' => 'dem', 'name' => 'Demandeur', 'sort' => '2']);

        $this->get('/api/permissions.json?' . http_build_query([
            'role_id' => 2,
            'page' => 2,
            'size' => 1,
            'sorters' => [['field' => 'code', 'dir' => 'desc']],
            'filters' => [['field' => 'label', 'type' => 'like', 'value' => 'Administrer les']],
        ]));

        $this->assertResponseOk();
        $payload = (array)json_decode((string)$this->_response->getBody(), true);
        self::assertTrue($payload['success']);
        self::assertSame(2, $payload['last_page'] ?? null);
        self::assertSame(['departments.manage'], array_column($payload['data'], 'code'));
        self::assertSame([], $payload['data'][0]['role_permissions']);
    }

    #[DataProvider('parametresPaginationIsoles')]
    public function testUnSeulParametreActiveLaPagination(
        string $query,
        int $lastPage,
        int $rowCount,
        int $firstId,
    ): void {
        $this->session(['Auth' => new User(['id' => 1, 'issuperuser' => true, 'role_id' => 1])]);

        $this->get('/api/permissions.json' . $query);

        $this->assertResponseOk();
        $payload = (array)json_decode((string)$this->_response->getBody(), true);
        self::assertSame($lastPage, $payload['last_page'] ?? null);
        self::assertCount($rowCount, $payload['data']);
        self::assertSame($firstId, $payload['data'][0]['id']);
    }

    /** @return array<string, array{string, int, int, int}> */
    public static function parametresPaginationIsoles(): array
    {
        return [
            'page seule, taille de 20 par défaut' => ['?page=2', 3, 20, 21],
            'taille seule, première page par défaut' => ['?size=4', 11, 4, 1],
        ];
    }

    public function testLesFiltresLiesConserventUneLigneParPermissionEtToutesSesRelations(): void
    {
        $this->session(['Auth' => new User(['id' => 1, 'issuperuser' => true, 'role_id' => 1])]);
        $this->ajouterLesMenusLiesAuxDemandes();

        $this->get('/api/permissions.json?' . http_build_query([
            'role_id' => 1,
            'size' => 1,
            'filters' => [
                ['field' => 'permission_scopes', 'type' => '=', 'value' => 'all'],
                ['field' => 'role_permissions', 'type' => 'like', 'value' => 'assigned'],
                ['field' => 'menus', 'type' => 'like', 'value' => '/applicationforms'],
            ],
        ]));

        $this->assertResponseOk();
        $payload = (array)json_decode((string)$this->_response->getBody(), true);
        self::assertSame(1, $payload['last_page'] ?? null);
        self::assertSame(['applicationforms.read'], array_column($payload['data'], 'code'));
        $permission = $payload['data'][0];
        self::assertEqualsCanonicalizing(['self', 'assigned', 'all'], array_column($permission['permission_scopes'], 'scope_type'));
        self::assertEqualsCanonicalizing(['self', 'assigned'], array_column($permission['role_permissions'], 'scope_type'));
        self::assertEqualsCanonicalizing(['/applicationforms', '/applicationforms/archives'], array_column($permission['menus'], 'url'));
    }

    public function testLeFiltreDesAttributionsIgnoreLesAutresRoles(): void
    {
        $this->session(['Auth' => new User(['id' => 1, 'issuperuser' => true, 'role_id' => 1])]);
        $connection = ConnectionManager::get('test');
        $connection->insert('roles', ['id' => 2, 'code' => 'dem', 'name' => 'Demandeur', 'sort' => '2']);
        $connection->insert('role_permissions', ['role_id' => 2, 'permission_id' => 5, 'scope_type' => 'self']);

        $this->get('/api/permissions.json?' . http_build_query([
            'role_id' => 2,
            'page' => 1,
            'filters' => [['field' => 'role_permissions', 'type' => '=', 'value' => 'assigned']],
        ]));

        $this->assertResponseOk();
        $payload = (array)json_decode((string)$this->_response->getBody(), true);
        self::assertSame(1, $payload['last_page'] ?? null);
        self::assertSame([], $payload['data']);
    }

    public function testLeCataloguePagineIgnoreLesChampsInconnusEtLesFiltresMalFormes(): void
    {
        $this->session(['Auth' => new User(['id' => 1, 'issuperuser' => true, 'role_id' => 1])]);

        $this->get('/api/permissions.json?' . http_build_query([
            'size' => 1,
            'limit' => 4,
            'sort' => ['label'],
            'sorters' => [['field' => 'inconnu', 'dir' => 'asc'], ['field' => 'label', 'dir' => ['asc']]],
            'filters' => [
                ['field' => 'inconnu', 'type' => '=', 'value' => 'a'],
                ['field' => 'menus', 'type' => 'like', 'value' => ['start' => '2026-11-20']],
            ],
        ]));

        $this->assertResponseOk();
        $payload = (array)json_decode((string)$this->_response->getBody(), true);
        self::assertSame(42, $payload['last_page'] ?? null);
        self::assertCount(1, $payload['data']);
        self::assertSame([], $payload['data'][0]['role_permissions']);
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
            'rôle inconnu avec pagination' => ['?role_id=9999&page=1&size=20'],
            'valeur vide' => ['?role_id='],
            'identifiant nul' => ['?role_id=0'],
            'valeur non entière' => ['?role_id=1.5'],
            'tableau' => ['?role_id%5B%5D=1'],
        ];
    }

    public function testLeCatalogueWebProposeLesRolesActifsParNomSansSelectionInitiale(): void
    {
        $this->session(['Auth' => new User(['id' => 1, 'issuperuser' => true, 'role_id' => 1])]);
        $connection = ConnectionManager::get('test');
        $connection->insert('roles', ['id' => 2, 'code' => 'zed', 'name' => 'Zèbre', 'sort' => '1']);
        $connection->insert('roles', ['id' => 3, 'code' => 'adm', 'name' => 'Administrateur', 'sort' => '2']);

        $this->get('/permissions');

        $this->assertResponseOk();
        $document = new DOMDocument();
        $previous = libxml_use_internal_errors(true);
        try {
            self::assertTrue($document->loadHTML((string)$this->_response->getBody()));
        } finally {
            libxml_clear_errors();
            libxml_use_internal_errors($previous);
        }
        $xpath = new DOMXPath($document);
        $options = $xpath->query('//select[@id="permissions-role"]/option');
        self::assertNotFalse($options);
        $values = [];
        foreach ($options as $option) {
            $values[] = $option->attributes?->getNamedItem('value')?->nodeValue;
        }
        self::assertSame(['', '3', '2'], $values);
        self::assertSame(0.0, $xpath->evaluate('count(//select[@id="permissions-role"]/option[@selected and @value!=""])'));
        self::assertSame(1.0, $xpath->evaluate('count(//div[@id="permissions-catalog" and @hidden])'));
    }

    public function testUnOperateurNePeutPasAfficherLeCatalogueWeb(): void
    {
        $this->session(['Auth' => new User(['id' => 2, 'issuperuser' => false, 'role_id' => User::ROLE_DEMANDEUR])]);

        $this->get('/permissions');

        $this->assertResponseCode(403);
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

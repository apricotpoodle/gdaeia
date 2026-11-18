<?php
declare(strict_types=1);

namespace App\Test\TestCase\Controller\Api;

use App\Model\Entity\User;
use Cake\TestSuite\IntegrationTestTrait;
use Cake\TestSuite\TestCase;

final class UserMutationAuthorizationTest extends TestCase
{
    use IntegrationTestTrait;

    protected array $fixtures = [
        'app.FieldDefinitions',
        'app.Users',
        'app.Roles',
        'app.Departments',
        'app.UserDepartments',
    ];

    protected function setUp(): void
    {
        parent::setUp();

        $connection = $this->getTableLocator()->get('Users')->getConnection();
        $connection->delete('user_departments', ['user_id IN' => [2, 30, 31]]);
        $connection->delete('users', ['id IN' => [30, 31]]);
        $connection->update('departments', ['deleted' => null], ['id' => 1]);
        $connection->insert('departments', [
            'id' => 3,
            'parent_id' => null,
            'lft' => 7,
            'rght' => 8,
            'level' => 1,
            'base' => 0,
            'code' => 'DEPARTMENT-OUT-OF-SCOPE',
            'name' => 'Département hors périmètre',
            'sort' => 'C',
            'department_type_id' => 1,
            'cgr_strategy_id' => null,
            'deleted' => null,
            'created' => '2026-07-03 09:01:33',
            'modified' => '2026-07-03 09:01:33',
        ]);
        $connection->insert('departments', [
            'id' => 4,
            'parent_id' => null,
            'lft' => 5,
            'rght' => 6,
            'level' => 1,
            'base' => 0,
            'code' => 'DEPARTMENT-IN-SCOPE',
            'name' => 'Département assigné',
            'sort' => 'D',
            'department_type_id' => 1,
            'cgr_strategy_id' => null,
            'deleted' => null,
            'created' => '2026-07-03 09:01:33',
            'modified' => '2026-07-03 09:01:33',
        ]);
        $connection->insert('users', [
            'id' => 30,
            'username' => 'utilisateur-parent',
            'email' => 'utilisateur-parent@example.test',
            'password' => 'mot-de-passe',
            'firstname' => 'Utilisateur',
            'lastname' => 'Parent',
            'issuperuser' => 0,
            'role_id' => 1,
            'deleted' => null,
            'created' => '2026-07-03 09:01:33',
            'modified' => '2026-07-03 09:01:33',
        ]);
        $connection->insert('users', [
            'id' => 31,
            'username' => 'utilisateur-manageable',
            'email' => 'utilisateur-manageable@example.test',
            'password' => 'mot-de-passe',
            'firstname' => 'Utilisateur',
            'lastname' => 'Gérable',
            'issuperuser' => 0,
            'role_id' => User::ROLE_ADMIN,
            'deleted' => null,
            'created' => '2026-07-03 09:01:33',
            'modified' => '2026-07-03 09:01:33',
        ]);
        foreach (
            [
                ['user_id' => 2, 'department_id' => 2],
                ['user_id' => 2, 'department_id' => 4],
                ['user_id' => 30, 'department_id' => 1],
                ['user_id' => 30, 'department_id' => 2],
                ['user_id' => 31, 'department_id' => 2],
                ['user_id' => 31, 'department_id' => 3],
            ] as $association
        ) {
            $connection->insert('user_departments', $association + [
                'created' => '2026-07-03 09:01:33',
                'modified' => '2026-07-03 09:01:33',
            ]);
        }
    }

    protected function tearDown(): void
    {
        $connection = $this->getTableLocator()->get('Users')->getConnection();
        $connection->delete('user_departments', ['user_id IN' => [2, 30, 31]]);
        $connection->delete('users', ['id IN' => [30, 31]]);
        $connection->delete('departments', ['id' => 4]);
        $connection->delete('departments', ['id' => 3]);

        parent::tearDown();
    }

    public function testLaLectureWebResteAccessiblePourUneCibleVisibleNonGerable(): void
    {
        $this->session(['Auth' => new User(['id' => 2, 'role_id' => User::ROLE_ADMIN])]);

        $this->get('/users/view/30');

        $this->assertResponseOk();
    }

    public function testLEditionWebDirecteRefuseUneCibleVisibleNonGerable(): void
    {
        $this->session(['Auth' => new User(['id' => 2, 'role_id' => User::ROLE_ADMIN])]);
        $this->enableCsrfToken();

        $this->post('/users/edit/30', ['firstname' => 'Modification interdite']);

        $this->assertResponseCode(403);
    }

    public function testLEditionApiDirecteRefuseUneCibleVisibleNonGerable(): void
    {
        $this->session(['Auth' => new User(['id' => 2, 'role_id' => User::ROLE_ADMIN])]);
        $this->enableCsrfToken();
        $this->configRequest(['headers' => ['Accept' => 'application/json']]);

        $this->post('/api/users/edit/30.json', ['firstname' => 'Modification interdite']);

        $this->assertResponseCode(403);
    }

    public function testLaMutationApiDeMasseRefuseUneCibleVisibleNonGerable(): void
    {
        $this->session(['Auth' => new User(['id' => 2, 'role_id' => User::ROLE_ADMIN])]);
        $this->enableCsrfToken();
        $this->configRequest(['headers' => ['Accept' => 'application/json']]);

        $this->post('/api/users/assign-bulk-departments.json', [
            'user_id' => 30,
            'department_ids' => [2],
        ]);

        $this->assertResponseCode(403);
    }

    public function testLEditionApiRefuseUneAffectationDepartementaleHorsPerimetre(): void
    {
        $this->session(['Auth' => new User(['id' => 2, 'role_id' => User::ROLE_ADMIN])]);
        $this->enableCsrfToken();
        $this->configRequest(['headers' => ['Accept' => 'application/json']]);

        $this->post('/api/users/edit/2.json', [
            'user_departments' => [
                ['department_id' => 1],
            ],
        ]);

        $this->assertResponseCode(403);
        $this->assertSame(
            [2, 4],
            $this->getTableLocator()->get('UserDepartments')->find()
                ->select(['department_id'])
                ->where(['user_id' => 2])
                ->orderBy(['department_id' => 'ASC'])
                ->all()
                ->extract('department_id')
                ->map(static fn(mixed $id): int => (int)$id)
                ->toList(),
        );
    }

    public function testLaMutationApiDeMasseRefuseUnDepartementHorsPerimetreSansSupprimerLesAssociations(): void
    {
        $this->session(['Auth' => new User(['id' => 2, 'role_id' => User::ROLE_ADMIN])]);
        $this->enableCsrfToken();
        $this->configRequest(['headers' => ['Accept' => 'application/json']]);

        $this->post('/api/users/bulk-departments.json', [
            'user_ids' => [2],
            'department_ids' => [1],
            'association_mode' => 'replace',
        ]);

        $this->assertResponseCode(403);
        $this->assertSame(
            [2, 4],
            $this->getTableLocator()->get('UserDepartments')->find()
                ->select(['department_id'])
                ->where(['user_id' => 2])
                ->orderBy(['department_id' => 'ASC'])
                ->all()
                ->extract('department_id')
                ->map(static fn(mixed $id): int => (int)$id)
                ->toList(),
        );
    }

    public function testLAssociationApiRequiertSonDroitDedieEtLePerimetreAssigne(): void
    {
        $this->withoutPermissionGrant(User::ROLE_ADMIN, 'users.create', function (): void {
            $this->session(['Auth' => new User(['id' => 2, 'role_id' => User::ROLE_ADMIN])]);
            $this->enableCsrfToken();
            $this->configRequest(['headers' => ['Accept' => 'application/json']]);

            $this->post('/api/users/unassign-bulk-departments.json', [
                'user_id' => 31,
                'department_ids' => [2],
            ]);

            $this->assertResponseOk();
            $this->assertResponseContains('"associations_deleted":1');
        });
    }

    public function testLesMutationsApiDeDepartementsRefusentUnePermissionDedieeAbsente(): void
    {
        $this->withoutPermissionGrant(
            User::ROLE_ADMIN,
            'users.associate_departments',
            function (): void {
                $this->session(['Auth' => new User(['id' => 2, 'role_id' => User::ROLE_ADMIN])]);
                $this->enableCsrfToken();
                $this->configRequest(['headers' => ['Accept' => 'application/json']]);

                $this->post('/api/users/add.json', [
                    'email' => 'association-refusee@example.test',
                    'username' => 'association-refusee',
                    'password' => 'mot-de-passe',
                    'role_id' => User::ROLE_DEMANDEUR,
                    'user_departments' => [['department_id' => 2]],
                ]);
                $this->assertResponseCode(403);

                $this->post('/api/users/edit/2.json', ['user_departments' => []]);
                $this->assertResponseCode(403);

                $this->post('/api/users/bulk-departments.json', [
                    'user_ids' => [31],
                    'department_ids' => [2],
                ]);
                $this->assertResponseCode(403);

                $this->post('/api/users/assign-bulk-departments.json', [
                    'user_id' => 31,
                    'department_ids' => [2],
                ]);
                $this->assertResponseCode(403);

                $this->post('/api/users/unassign-bulk-departments.json', [
                    'user_id' => 31,
                    'department_ids' => [2],
                ]);
                $this->assertResponseCode(403);
            },
        );
    }

    public function testLesMutationsWebDeDepartementsRefusentUnePermissionDedieeAbsente(): void
    {
        $this->withoutPermissionGrant(
            User::ROLE_ADMIN,
            'users.associate_departments',
            function (): void {
                $this->session(['Auth' => new User(['id' => 2, 'role_id' => User::ROLE_ADMIN])]);
                $this->enableCsrfToken();

                $this->post('/users/add', [
                    'email' => 'association-web-refusee@example.test',
                    'username' => 'association-web-refusee',
                    'password' => 'mot-de-passe',
                    'role_id' => User::ROLE_DEMANDEUR,
                    'user_departments' => [['department_id' => 2]],
                ]);
                $this->assertResponseCode(403);

                $this->post('/users/edit/2', ['user_departments' => []]);
                $this->assertResponseCode(403);
            },
        );
    }

    public function testUneEditionSansChangementDeDepartementsNeRequiertPasLeDroitDedie(): void
    {
        $this->withoutPermissionGrant(
            User::ROLE_ADMIN,
            'users.associate_departments',
            function (): void {
                $this->session(['Auth' => new User(['id' => 2, 'role_id' => User::ROLE_ADMIN])]);
                $this->enableCsrfToken();
                $this->configRequest(['headers' => ['Accept' => 'application/json']]);

                $this->post('/api/users/edit/2.json', ['firstname' => 'Modification autorisée']);

                $this->assertResponseOk();
                $this->assertSame(
                    [2, 4],
                    $this->getTableLocator()->get('UserDepartments')->find()
                        ->select(['department_id'])
                        ->where(['user_id' => 2])
                        ->extract('department_id')
                        ->map(static fn(mixed $id): int => (int)$id)
                        ->toList(),
                );
            },
        );
    }

    public function testLEditionApiRefuseUnePermissionAssociationMalScopee(): void
    {
        $this->withPermissionGrantScope(
            User::ROLE_ADMIN,
            'users.associate_departments',
            'unscoped',
            function (): void {
                $this->session(['Auth' => new User(['id' => 2, 'role_id' => User::ROLE_ADMIN])]);
                $this->enableCsrfToken();
                $this->configRequest(['headers' => ['Accept' => 'application/json']]);

                $associationId = $this->departmentAssociationId(2, 2);
                $this->post('/api/users/edit/2.json', [
                    'user_departments' => [['id' => $associationId, 'department_id' => 4]],
                ]);

                $this->assertResponseCode(403);
            },
        );
    }

    public function testLEditionApiRefuseUnPatchParIdSansPermissionAssociation(): void
    {
        $this->withoutPermissionGrant(
            User::ROLE_ADMIN,
            'users.associate_departments',
            function (): void {
                $this->session(['Auth' => new User(['id' => 2, 'role_id' => User::ROLE_ADMIN])]);
                $this->enableCsrfToken();
                $this->configRequest(['headers' => ['Accept' => 'application/json']]);
                $associationId = $this->departmentAssociationId(2, 2);

                $this->post('/api/users/edit/2.json', [
                    'user_departments' => [['id' => $associationId, 'department_id' => 4]],
                ]);

                $this->assertResponseCode(403);
            },
        );
    }

    public function testLEditionApiGardeLeDepartementInitialHorsPerimetreApresUnPatchParId(): void
    {
        $this->session(['Auth' => new User(['id' => 2, 'role_id' => User::ROLE_ADMIN])]);
        $this->enableCsrfToken();
        $this->configRequest(['headers' => ['Accept' => 'application/json']]);
        $associationId = $this->departmentAssociationId(31, 3);

        $this->post('/api/users/edit/31.json', [
            'user_departments' => [['id' => $associationId, 'department_id' => 4]],
        ]);

        $this->assertResponseCode(403);
        $this->assertSame(
            [2, 3],
            $this->getTableLocator()->get('UserDepartments')->find()
                ->select(['department_id'])
                ->where(['user_id' => 31])
                ->orderBy(['department_id' => 'ASC'])
                ->all()
                ->extract('department_id')
                ->map(static fn(mixed $id): int => (int)$id)
                ->toList(),
        );
    }

    public function testLaCreationApiExigeLaPermissionGeneriqueDeCreation(): void
    {
        $this->withoutPermissionGrant(User::ROLE_ADMIN, 'users.create', function (): void {
            $this->session(['Auth' => new User(['id' => 2, 'role_id' => User::ROLE_ADMIN])]);
            $this->enableCsrfToken();
            $this->configRequest(['headers' => ['Accept' => 'application/json']]);

            $this->post('/api/users/add.json', [
                'email' => 'nouvel-utilisateur@example.test',
                'password' => 'mot-de-passe',
                'role_id' => User::ROLE_DEMANDEUR,
            ]);

            $this->assertResponseCode(403);
        });
    }

    public function testLaMutationApiAutoriseUnRoleFonctionnelPourUnAdministrateurLocal(): void
    {
        $this->getTableLocator()->get('Roles')->getConnection()->insert('roles', [
            'id' => User::ROLE_1_VALIDEUR_POLE,
            'base' => 1,
            'code' => 'ROLE_POLE',
            'name' => 'Valideur pôle',
            'sort' => '3',
            'created' => '2026-07-03 09:01:32',
            'modified' => '2026-07-03 09:01:32',
        ]);
        $this->session(['Auth' => new User(['id' => 2, 'role_id' => User::ROLE_ADMIN])]);
        $this->enableCsrfToken();
        $this->configRequest(['headers' => ['Accept' => 'application/json']]);

        $this->post('/api/users/edit/2.json', ['role_id' => User::ROLE_1_VALIDEUR_POLE]);

        $this->assertResponseOk();
        $this->assertSame(
            User::ROLE_1_VALIDEUR_POLE,
            $this->getTableLocator()->get('Users')->get(2)->get(User::FIELD_ROLE_ID),
        );
    }

    public function testLaMutationApiRefuseLeRoleAdminPourUnAdministrateurLocal(): void
    {
        $this->getTableLocator()->get('Users')->getConnection()->update(
            'users',
            ['role_id' => User::ROLE_DEMANDEUR],
            ['id' => 2],
        );
        $this->session(['Auth' => new User(['id' => 2, 'role_id' => User::ROLE_ADMIN])]);
        $this->enableCsrfToken();
        $this->configRequest(['headers' => ['Accept' => 'application/json']]);

        $this->post('/api/users/edit/2.json', ['role_id' => User::ROLE_ADMIN]);

        $this->assertResponseCode(403);
    }

    public function testLaMutationApiRefuseLActivationSuperuserPourUnAdministrateurLocal(): void
    {
        $this->session(['Auth' => new User(['id' => 2, 'role_id' => User::ROLE_ADMIN])]);
        $this->enableCsrfToken();
        $this->configRequest(['headers' => ['Accept' => 'application/json']]);

        $this->post('/api/users/edit/2.json', ['issuperuser' => true]);

        $this->assertResponseCode(403);
    }

    public function testLaMutationApiAutoriseLeRoleAdminPourUnSuperuser(): void
    {
        $this->session(['Auth' => new User(['id' => 1, 'issuperuser' => true, 'role_id' => User::ROLE_ADMIN])]);
        $this->enableCsrfToken();
        $this->configRequest(['headers' => ['Accept' => 'application/json']]);

        $this->post('/api/users/edit/2.json', [
            'role_id' => User::ROLE_ADMIN,
            'issuperuser' => true,
        ]);

        $this->assertResponseOk();
        $user = $this->getTableLocator()->get('Users')->get(2);
        $this->assertSame(User::ROLE_ADMIN, $user->get(User::FIELD_ROLE_ID));
        $this->assertTrue((bool)$user->get(User::FIELD_ISSUPERUSER));
    }

    /** Exécute une requête sans les attributions d’un code, puis les restaure. */
    private function withoutPermissionGrant(int $roleId, string $code, callable $callback): void
    {
        [$resource, $action] = explode('.', $code, 2);
        $permission = $this->getTableLocator()->get('Permissions')->find()
            ->where(['resource' => $resource, 'action' => $action])
            ->firstOrFail();
        $rolePermissions = $this->getTableLocator()->get('RolePermissions');
        $grants = $rolePermissions->find()
            ->where([
                'role_id' => $roleId,
                'permission_id' => $permission->get('id'),
            ])
            ->all()
            ->toList();
        $this->assertNotEmpty($grants);
        $rolePermissions->deleteAll(['id IN' => array_map(
            static fn($grant): int => (int)$grant->get('id'),
            $grants,
        )]);

        try {
            $callback();
        } finally {
            foreach ($grants as $grant) {
                $rolePermissions->saveOrFail($rolePermissions->newEntity([
                    'role_id' => $grant->get('role_id'),
                    'permission_id' => $grant->get('permission_id'),
                    'scope_type' => $grant->get('scope_type'),
                ]));
            }
        }
    }

    /** Exécute une requête avec un scope temporairement incorrect, puis restaure les attributions. */
    private function withPermissionGrantScope(
        int $roleId,
        string $code,
        string $scope,
        callable $callback,
    ): void {
        [$resource, $action] = explode('.', $code, 2);
        $permission = $this->getTableLocator()->get('Permissions')->find()
            ->where(['resource' => $resource, 'action' => $action])
            ->firstOrFail();
        $rolePermissions = $this->getTableLocator()->get('RolePermissions');
        $grants = $rolePermissions->find()
            ->where([
                'role_id' => $roleId,
                'permission_id' => $permission->get('id'),
            ])
            ->all()
            ->toList();
        $this->assertNotEmpty($grants);
        $rolePermissions->deleteAll(['id IN' => array_map(
            static fn($grant): int => (int)$grant->get('id'),
            $grants,
        )]);

        try {
            $rolePermissions->saveOrFail($rolePermissions->newEntity([
                'role_id' => $roleId,
                'permission_id' => $permission->get('id'),
                'scope_type' => $scope,
            ]));
            $callback();
        } finally {
            $rolePermissions->deleteAll([
                'role_id' => $roleId,
                'permission_id' => $permission->get('id'),
                'scope_type' => $scope,
            ]);
            foreach ($grants as $grant) {
                $rolePermissions->saveOrFail($rolePermissions->newEntity([
                    'role_id' => $grant->get('role_id'),
                    'permission_id' => $grant->get('permission_id'),
                    'scope_type' => $grant->get('scope_type'),
                ]));
            }
        }
    }

    private function departmentAssociationId(int $userId, int $departmentId): int
    {
        return (int)$this->getTableLocator()->get('UserDepartments')->find()
            ->select(['id'])
            ->where(['user_id' => $userId, 'department_id' => $departmentId])
            ->firstOrFail()
            ->get('id');
    }
}

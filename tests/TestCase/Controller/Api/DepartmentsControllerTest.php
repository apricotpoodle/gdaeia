<?php
declare(strict_types=1);

namespace App\Test\TestCase\Controller\Api;

use App\Model\Entity\User;
use Cake\Datasource\ConnectionManager;
use Cake\TestSuite\IntegrationTestTrait;
use Cake\TestSuite\TestCase;

final class DepartmentsControllerTest extends TestCase
{
    use IntegrationTestTrait;

    protected array $fixtures = [
        'app.Users',
        'app.Departments',
        'app.CgrCodes',
        'app.DepartmentCgrCodes',
        'app.DepartmentManagers',
        'app.CgrStrategies',
        'app.UserDepartments',
        'app.Applicationforms',
        'app.Validationsequences',
        'app.Permissions',
        'app.PermissionScopes',
        'app.RolePermissions',
    ];

    public function testUnOperateurNePeutPasAccederAAdministrationDesDepartements(): void
    {
        $this->session(['Auth' => new User([
            'id' => 2,
            'issuperuser' => false,
            'role_id' => User::ROLE_DEMANDEUR,
        ])]);

        $this->get('/departments');
        $this->assertResponseCode(403);
        $this->assertHeaderContains('Content-Type', 'text/html');

        $this->get('/api/departments/tree.json');
        $this->assertResponseCode(403);
    }

    public function testUnSuperAdministrateurChargeLesRacinesEtLaPage(): void
    {
        $this->session(['Auth' => new User(['id' => 1, 'issuperuser' => true, 'role_id' => 1])]);
        $this->configRequest(['headers' => ['Accept' => 'application/json']]);

        $this->get('/departments');
        $this->assertResponseOk();
        $this->assertResponseContains('departments-table');

        $this->get('/api/departments/tree.json');
        $this->assertResponseOk();
        $payload = json_decode((string)$this->_response->getBody(), true);
        $this->assertIsArray($payload);
        $this->assertTrue($payload['success']);
        $this->assertArrayHasKey('data', $payload);
    }

    public function testUnAdministrateurSimpleNeVoitQueSesDepartementsDansLarbre(): void
    {
        $this->session(['Auth' => new User([
            'id' => 2,
            'issuperuser' => false,
            'role_id' => User::ROLE_ADMIN,
        ])]);
        $this->configRequest(['headers' => ['Accept' => 'application/json']]);
        $departments = $this->getTableLocator()->get('Departments');
        $departments->updateAll(['deleted' => null], ['id IN' => [1, 2]]);
        $userDepartments = $this->getTableLocator()->get('UserDepartments');
        $userDepartments->saveOrFail($userDepartments->newEntity([
            'user_id' => 2,
            'department_id' => 2,
        ]));

        $this->get('/departments');
        $this->assertResponseOk();

        $this->get('/api/departments/tree.json');
        $this->assertResponseOk();
        $payload = (array)json_decode((string)$this->_response->getBody(), true);

        $this->assertSame([2], array_column($payload['data'], 'id'));
    }

    public function testLarbreInitialContientTouteLaHierarchieEtLesAssociations(): void
    {
        $this->session(['Auth' => new User(['id' => 1, 'issuperuser' => true, 'role_id' => 1])]);
        $this->configRequest(['headers' => ['Accept' => 'application/json']]);
        $departments = $this->getTableLocator()->get('Departments');
        $departments->updateAll(['deleted' => null], ['id' => 1]);
        $managers = $this->getTableLocator()->get('DepartmentManagers');
        $managers->saveOrFail($managers->newEntity(['department_id' => 1, 'user_id' => 2]));

        $this->get('/api/departments/tree.json');

        $payload = (array)json_decode((string)$this->_response->getBody(), true);
        $this->assertResponseOk();
        $this->assertSame([1], array_column($payload['data'], 'id'));
        $this->assertSame([2], array_column($payload['data'][0]['children'], 'id'));
        $this->assertFalse($payload['data'][0]['children'][0]['_tree_placeholder'] ?? false);
        $this->assertSame(['Utilisateur Test'], $payload['data'][0]['managers']);
        $this->assertSame(['Lorem ipsum do'], array_column($payload['data'][0]['cgr_codes'], 'code'));
    }

    public function testLeTriSurLesResponsablesEstTraduitEnTriOrm(): void
    {
        $this->session(['Auth' => new User(['id' => 1, 'issuperuser' => true, 'role_id' => 1])]);
        $this->configRequest(['headers' => ['Accept' => 'application/json']]);
        $this->getTableLocator()->get('Departments')->updateAll(['deleted' => null], ['id' => 1]);

        $this->get('/api/departments/tree.json?' . http_build_query([
            'sorters' => [['field' => 'managers', 'dir' => 'asc']],
        ]));

        $this->assertResponseOk();
        $payload = (array)json_decode((string)$this->_response->getBody(), true);
        $this->assertTrue($payload['success']);
        $this->assertSame([1], array_column($payload['data'], 'id'));
    }

    public function testLeTriSurLesCgrEstTraiteSansErreurSql(): void
    {
        $this->session(['Auth' => new User(['id' => 1, 'issuperuser' => true, 'role_id' => 1])]);
        $this->configRequest(['headers' => ['Accept' => 'application/json']]);
        $this->getTableLocator()->get('Departments')->updateAll(['deleted' => null], ['id' => 1]);

        $this->get('/api/departments/tree.json?' . http_build_query([
            'sorters' => [['field' => 'cgr_codes', 'dir' => 'asc']],
        ]));

        $this->assertResponseOk();
        $payload = (array)json_decode((string)$this->_response->getBody(), true);
        $this->assertTrue($payload['success']);
        $this->assertSame([1], array_column($payload['data'], 'id'));
    }

    public function testLeFiltreDeLarbreInclutLesAncetresEtExclutLesBranchesSansCorrespondance(): void
    {
        $this->session(['Auth' => new User(['id' => 1, 'issuperuser' => true, 'role_id' => 1])]);
        $this->configRequest(['headers' => ['Accept' => 'application/json']]);
        $this->getTableLocator()->get('Departments')->updateAll(['deleted' => null], ['id' => 1]);

        $this->get('/api/departments/tree.json?' . http_build_query([
            'filters' => [['field' => 'name', 'type' => 'like', 'value' => 'Service']],
        ]));

        $payload = (array)json_decode((string)$this->_response->getBody(), true);
        $this->assertSame([1], array_column($payload['data'], 'id'));
        $this->assertSame([2], array_column($payload['data'][0]['children'], 'id'));
        $this->assertFalse($payload['data'][0]['children'][0]['_tree_placeholder'] ?? false);

        $this->get('/api/departments/tree.json?' . http_build_query([
            'parent_id' => 1,
            'filters' => [['field' => 'name', 'type' => 'like', 'value' => 'Service']],
        ]));

        $payload = (array)json_decode((string)$this->_response->getBody(), true);
        $this->assertSame([2], array_column($payload['data'], 'id'));

        $this->get('/api/departments/tree.json?' . http_build_query([
            'filters' => [['field' => 'name', 'type' => 'like', 'value' => 'Inexistant']],
        ]));

        $payload = (array)json_decode((string)$this->_response->getBody(), true);
        $this->assertSame([], $payload['data']);

        $this->get('/api/departments/tree.json?' . http_build_query([
            'parent_id' => 1,
            'filters' => [['field' => 'name', 'type' => 'like', 'value' => 'Inexistant']],
        ]));

        $payload = (array)json_decode((string)$this->_response->getBody(), true);
        $this->assertSame([], $payload['data']);
    }

    public function testLeFiltreResponsableRechercheDansLePrenomEtLeNom(): void
    {
        $this->session(['Auth' => new User(['id' => 1, 'issuperuser' => true, 'role_id' => 1])]);
        $this->configRequest(['headers' => ['Accept' => 'application/json']]);
        $this->getTableLocator()->get('Departments')->updateAll([
            'deleted' => null,
        ], ['id IN' => [1, 2]]);
        $this->getTableLocator()->get('DepartmentManagers')->deleteAll(['department_id' => 2]);
        $table = $this->getTableLocator()->get('DepartmentManagers');
        $table->saveOrFail($table->newEntity([
            'department_id' => 2,
            'user_id' => 2,
        ]));

        $this->get('/api/departments/tree.json?' . http_build_query([
            'filters' => [['field' => 'manager', 'type' => 'like', 'value' => 'Utilisateur']],
        ]));

        $payload = (array)json_decode((string)$this->_response->getBody(), true);
        $this->assertResponseOk();
        $this->assertSame([1], array_column($payload['data'], 'id'));

        $this->get('/api/departments/tree.json?' . http_build_query([
            'parent_id' => 1,
            'filters' => [['field' => 'manager', 'type' => 'like', 'value' => 'Test']],
        ]));

        $payload = (array)json_decode((string)$this->_response->getBody(), true);
        $this->assertResponseOk();
        $this->assertSame([2], array_column($payload['data'], 'id'));
    }

    public function testLeFiltreStrategieCgrRechercheDansLeNomAssocie(): void
    {
        $this->session(['Auth' => new User(['id' => 1, 'issuperuser' => true, 'role_id' => 1])]);
        $this->configRequest(['headers' => ['Accept' => 'application/json']]);
        $this->getTableLocator()->get('Departments')->updateAll([
            'deleted' => null,
            'cgr_strategy_id' => 1,
        ], ['id' => 2]);
        $this->getTableLocator()->get('UserDepartments')->saveOrFail(
            $this->getTableLocator()->get('UserDepartments')->newEntity([
                'user_id' => 2,
                'department_id' => 2,
            ]),
        );
        $this->getTableLocator()->get('Departments')->updateAll(['deleted' => null], ['id' => 1]);

        $this->get('/api/departments/tree.json?' . http_build_query([
            'filters' => [['field' => 'cgr_strategy', 'type' => 'like', 'value' => 'Lorem']],
        ]));

        $payload = (array)json_decode((string)$this->_response->getBody(), true);
        $this->assertResponseOk();
        $this->assertSame([1], array_column($payload['data'], 'id'));
        $this->assertSame([2], array_column($payload['data'][0]['children'], 'id'));
    }

    public function testUnSuperAdministrateurCreeModifieEtDesactiveUnDepartementSansDependance(): void
    {
        $this->session(['Auth' => new User(['id' => 1, 'issuperuser' => true, 'role_id' => 1])]);
        $this->enableCsrfToken();
        $this->configRequest(['headers' => ['Accept' => 'application/json']]);

        $this->post('/api/departments/add.json', [
            'code' => 'NEW-DEPT',
            'name' => 'Nouveau département',
            'sort' => 'Nouveau département',
        ]);
        $this->assertResponseCode(200);
        $payload = (array)json_decode((string)$this->_response->getBody(), true);
        $id = (int)($payload['id'] ?? 0);
        $this->assertGreaterThan(0, $id);
        $this->assertSame(1, (int)$this->getTableLocator()->get('Departments')->get($id)->get('department_type_id'));

        $this->post('/api/departments/' . $id . '.json', ['name' => 'Département modifié']);
        $this->assertResponseOk();
        $this->assertResponseContains('Département modifié');

        $this->post('/api/departments/' . $id . '.json', ['base' => true]);
        $this->assertResponseOk();
        $this->assertTrue((bool)$this->getTableLocator()->get('Departments')->get($id)->get('base'));

        $this->post('/api/departments/' . $id . '.json', ['base' => false]);
        $this->assertResponseOk();

        $this->post('/api/departments/' . $id . '/deactivate.json');
        $this->assertResponseOk();
        $this->assertResponseContains('"success":true');
    }

    /** Vérifie que l'API refuse le nettoyage des dépendances à un administrateur ordinaire. */
    public function testUnAdministrateurOrdinaireNePeutPasNettoyerLesDependances(): void
    {
        $this->session(['Auth' => new User(['id' => 2, 'issuperuser' => false, 'role_id' => 1])]);
        $this->enableCsrfToken();
        $this->configRequest(['headers' => ['Accept' => 'application/json']]);

        $this->post('/api/departments/2/deactivate.json', [
            'cleanup' => ['users', 'validationsequences'],
        ]);

        $this->assertResponseCode(403);
    }

    /** Vérifie que le superadministrateur confirme et exécute le nettoyage sélectionné. */
    public function testLeSuperAdministrateurNettoieLesDependancesSelectionnees(): void
    {
        $connection = ConnectionManager::get('test');
        $now = '2026-10-16 10:00:00';
        $connection->update('departments', ['base' => 0, 'deleted' => null], ['id' => 2]);
        $connection->update('users', ['deleted' => null], ['id' => 1]);
        $connection->insert('user_departments', [
            'id' => 2,
            'user_id' => 1,
            'department_id' => 2,
            'created' => $now,
            'modified' => $now,
        ]);
        $connection->insert('validationsequences', [
            'id' => 2,
            'department_id' => 2,
            'name' => 'Séquence API',
            'role_id' => 1,
            'sequence' => 1,
            'deleted' => null,
            'created' => $now,
            'modified' => $now,
        ]);
        $connection->insert('cgr_codes', [
            'id' => 2,
            'type' => 'test',
            'code' => 'CGR-API',
            'label' => 'Code API',
            'active' => 1,
            'is_system' => 0,
            'created' => $now,
            'modified' => $now,
        ]);
        $connection->insert('department_cgr_codes', [
            'department_id' => 2,
            'cgr_code_id' => 2,
        ]);
        $this->session(['Auth' => new User(['id' => 1, 'issuperuser' => true, 'role_id' => 1])]);
        $this->enableCsrfToken();
        $this->configRequest(['headers' => ['Accept' => 'application/json']]);

        $this->post('/api/departments/2/deactivate.json', [
            'cleanup' => ['users', 'validationsequences', 'cgr_codes'],
        ]);

        $this->assertResponseOk();
        $this->assertResponseContains('"success":true');
        $this->assertResponseContains('"detached_user_ids"');
        $this->assertNotNull($connection->execute(
            'SELECT deleted FROM applicationforms WHERE id = 1',
        )->fetchColumn(0));
    }

    public function testLesCodesCgrSontExposesParLeEndpointImbrique(): void
    {
        $this->session(['Auth' => new User(['id' => 1, 'issuperuser' => true, 'role_id' => 1])]);
        $this->configRequest(['headers' => ['Accept' => 'application/json']]);
        $this->getTableLocator()->get('Departments')->updateAll(['deleted' => null], ['id' => 1]);

        $this->get('/api/departments/1/cgr-codes.json');
        $this->assertResponseOk();
        $this->assertResponseContains('"data"');
        $this->assertResponseContains('Lorem ipsum do');
    }

    public function testLesListesDesResponsablesDisponiblesEtAssociesSontExposees(): void
    {
        $this->session(['Auth' => new User(['id' => 1, 'issuperuser' => true, 'role_id' => 1])]);
        $this->configRequest(['headers' => ['Accept' => 'application/json']]);
        $this->getTableLocator()->get('Departments')->updateAll(['deleted' => null], ['id' => 1]);

        $this->get('/api/departments/1/managers/available.json');
        $available = (array)json_decode((string)$this->_response->getBody(), true);

        $this->assertResponseOk();
        $this->assertSame([2], array_column($available['data'], 'id'));

        $this->get('/api/departments/1/managers/associated.json');
        $associated = (array)json_decode((string)$this->_response->getBody(), true);

        $this->assertResponseOk();
        $this->assertSame([], $associated['data']);
    }

    public function testLesResponsablesVisiblesMaisNonGerablesNeSontPasDisponibles(): void
    {
        $this->session(['Auth' => new User([
            'id' => 2,
            'issuperuser' => false,
            'role_id' => User::ROLE_ADMIN,
        ])]);
        $this->configRequest(['headers' => ['Accept' => 'application/json']]);
        $this->getTableLocator()->get('Departments')->updateAll(['deleted' => null], ['id IN' => [1, 2]]);

        $userDepartments = $this->getTableLocator()->get('UserDepartments');
        $userDepartments->saveOrFail($userDepartments->newEntity([
            'user_id' => 2,
            'department_id' => 2,
        ]));
        ConnectionManager::get('test')->insert('users', [
            'id' => 3,
            'username' => 'responsable-visible-non-gerable',
            'email' => 'responsable-visible-non-gerable@example.test',
            'password' => 'password',
            'firstname' => 'Responsable',
            'lastname' => 'Non gérable',
            'issuperuser' => 0,
            'role_id' => User::ROLE_ADMIN,
            'deleted' => null,
            'created' => '2026-11-04 10:00:00',
            'modified' => '2026-11-04 10:00:00',
        ]);
        foreach ([1, 2] as $departmentId) {
            $userDepartments->saveOrFail($userDepartments->newEntity([
                'user_id' => 3,
                'department_id' => $departmentId,
            ]));
        }

        $this->get('/api/departments/2/managers/available.json');
        $available = (array)json_decode((string)$this->_response->getBody(), true);

        $this->assertResponseOk();
        $this->assertNotContains(3, array_column($available['data'], 'id'));
    }

    public function testLesMutationsDeResponsableRefusentUneCibleVisibleMaisNonGerable(): void
    {
        $this->session(['Auth' => new User([
            'id' => 2,
            'issuperuser' => false,
            'role_id' => User::ROLE_ADMIN,
        ])]);
        $this->enableCsrfToken();
        $this->configRequest(['headers' => ['Accept' => 'application/json']]);
        $this->getTableLocator()->get('Departments')->updateAll(['deleted' => null], ['id IN' => [1, 2]]);

        $userDepartments = $this->getTableLocator()->get('UserDepartments');
        $userDepartments->saveOrFail($userDepartments->newEntity([
            'user_id' => 2,
            'department_id' => 2,
        ]));
        ConnectionManager::get('test')->insert('users', [
            'id' => 3,
            'username' => 'responsable-visible-non-gerable',
            'email' => 'responsable-visible-non-gerable@example.test',
            'password' => 'password',
            'firstname' => 'Responsable',
            'lastname' => 'Non gérable',
            'issuperuser' => 0,
            'role_id' => User::ROLE_ADMIN,
            'deleted' => null,
            'created' => '2026-11-04 10:00:00',
            'modified' => '2026-11-04 10:00:00',
        ]);
        foreach ([1, 2] as $departmentId) {
            $userDepartments->saveOrFail($userDepartments->newEntity([
                'user_id' => 3,
                'department_id' => $departmentId,
            ]));
        }
        $managers = $this->getTableLocator()->get('DepartmentManagers');

        $this->post('/api/departments/2/managers/3/add.json');
        $this->assertResponseCode(403);
        $this->assertSame(0, $managers->find()->where([
            'department_id' => 2,
            'user_id' => 3,
        ])->count());

        $managers->saveOrFail($managers->newEntity([
            'department_id' => 2,
            'user_id' => 3,
        ]));
        $this->post('/api/departments/2/managers/3/remove.json');

        $this->assertResponseCode(403);
        $this->assertSame(1, $managers->find()->where([
            'department_id' => 2,
            'user_id' => 3,
        ])->count());
    }

    public function testLeResumeDUnDepartementEstAccessibleParUneRouteGetExplicite(): void
    {
        $this->session(['Auth' => new User(['id' => 1, 'issuperuser' => true, 'role_id' => 1])]);
        $this->configRequest(['headers' => ['Accept' => 'application/json']]);
        $this->getTableLocator()->get('Departments')->updateAll(['deleted' => null], ['id' => 2]);

        $this->get('/api/departments/2/view.json');

        $this->assertResponseOk();
        $payload = json_decode((string)$this->_response->getBody(), true, 512, JSON_THROW_ON_ERROR);
        $this->assertTrue($payload['success']);
        $this->assertArrayHasKey('managers', $payload['data']);
        $this->assertArrayHasKey('cgr_codes', $payload['data']);
    }

    public function testUnResponsableEstAssocieEtDissocieImmediatement(): void
    {
        $this->session(['Auth' => new User(['id' => 1, 'issuperuser' => true, 'role_id' => 1])]);
        $this->enableCsrfToken();
        $this->configRequest(['headers' => ['Accept' => 'application/json']]);
        $this->getTableLocator()->get('Departments')->updateAll(['deleted' => null], ['id' => 1]);

        $this->post('/api/departments/1/managers/2/add.json');

        $this->assertResponseOk();
        $this->assertSame(1, $this->getTableLocator()->get('DepartmentManagers')->find()
            ->where(['department_id' => 1, 'user_id' => 2])->count());

        $this->post('/api/departments/1/managers/2/remove.json');

        $this->assertResponseOk();
        $this->assertSame(0, $this->getTableLocator()->get('DepartmentManagers')->find()
            ->where(['department_id' => 1, 'user_id' => 2])->count());
    }

    public function testLesResponsablesAssociesHorsPerimetreSontVisiblesEnLectureSeule(): void
    {
        $this->session(['Auth' => new User([
            'id' => 2,
            'issuperuser' => false,
            'role_id' => User::ROLE_ADMIN,
        ])]);
        $this->configRequest(['headers' => ['Accept' => 'application/json']]);
        $this->getTableLocator()->get('Departments')->updateAll(['deleted' => null], ['id' => 2]);
        $userDepartments = $this->getTableLocator()->get('UserDepartments');
        $userDepartments->saveOrFail($userDepartments->newEntity([
            'user_id' => 2,
            'department_id' => 2,
        ]));
        ConnectionManager::get('test')->insert('users', [
            'id' => 3,
            'username' => 'responsable-hors-perimetre',
            'email' => 'responsable-hors-perimetre@example.test',
            'password' => 'password',
            'firstname' => 'Responsable',
            'lastname' => 'Hors périmètre',
            'issuperuser' => 0,
            'role_id' => User::ROLE_ADMIN,
            'deleted' => null,
            'created' => '2026-11-04 10:00:00',
            'modified' => '2026-11-04 10:00:00',
        ]);
        $managers = $this->getTableLocator()->get('DepartmentManagers');
        $managers->saveOrFail($managers->newEntity([
            'department_id' => 2,
            'user_id' => 3,
        ]));

        $this->get('/api/departments/2/managers/available.json');
        $available = (array)json_decode((string)$this->_response->getBody(), true);
        $this->assertResponseOk();
        $this->assertNotContains(3, array_column($available['data'], 'id'));

        $this->get('/api/departments/2/managers/associated.json');
        $associated = (array)json_decode((string)$this->_response->getBody(), true);
        $this->assertResponseOk();
        $row = array_values(array_filter(
            $associated['data'],
            static fn(array $item): bool => (int)$item['id'] === 3,
        ))[0] ?? null;
        $this->assertNotNull($row);
        $this->assertFalse($row['can_dissociate']);
        $this->assertSame(
            'Responsable associé hors de votre périmètre : dissociation non autorisée.',
            $row['association_read_only_message'],
        );
    }

    public function testUnAdministrateurNePeutPasDissocierUnResponsableHorsDeSonPerimetre(): void
    {
        $this->session(['Auth' => new User([
            'id' => 2,
            'issuperuser' => false,
            'role_id' => User::ROLE_ADMIN,
        ])]);
        $this->enableCsrfToken();
        $this->configRequest(['headers' => ['Accept' => 'application/json']]);
        $this->getTableLocator()->get('Departments')->updateAll(['deleted' => null], ['id' => 2]);
        $userDepartments = $this->getTableLocator()->get('UserDepartments');
        $userDepartments->saveOrFail($userDepartments->newEntity([
            'user_id' => 2,
            'department_id' => 2,
        ]));
        ConnectionManager::get('test')->insert('users', [
            'id' => 3,
            'username' => 'responsable-hors-perimetre',
            'email' => 'responsable-hors-perimetre@example.test',
            'password' => 'password',
            'firstname' => 'Responsable',
            'lastname' => 'Hors périmètre',
            'issuperuser' => 0,
            'role_id' => User::ROLE_ADMIN,
            'deleted' => null,
            'created' => '2026-11-04 10:00:00',
            'modified' => '2026-11-04 10:00:00',
        ]);

        $managers = $this->getTableLocator()->get('DepartmentManagers');
        $managers->saveOrFail($managers->newEntity([
            'department_id' => 2,
            'user_id' => 3,
        ]));

        $this->post('/api/departments/2/managers/3/remove.json');

        $this->assertResponseCode(403);
        $this->assertHeaderContains('Content-Type', 'application/json');
        $payload = json_decode((string)$this->_response->getBody(), true, 512, JSON_THROW_ON_ERROR);
        $this->assertFalse($payload['success']);
        $this->assertSame(
            'Vous ne pouvez pas dissocier un responsable hors de votre périmètre.',
            $payload['message'],
        );
        $this->assertSame(1, $this->getTableLocator()->get('DepartmentManagers')->find()
            ->where([
                'department_id' => 2,
                'user_id' => 3,
            ])->count());
    }

    public function testLesListesDesCgrActifsDisponiblesEtAssociesSontExposees(): void
    {
        $this->session(['Auth' => new User(['id' => 1, 'issuperuser' => true, 'role_id' => 1])]);
        $this->configRequest(['headers' => ['Accept' => 'application/json']]);
        $this->getTableLocator()->get('Departments')->updateAll(['deleted' => null], ['id' => 2]);
        $this->getTableLocator()->get('CgrStrategies')->updateAll([
            'definition_json' => json_encode([['type' => ' axe ']], JSON_THROW_ON_ERROR),
        ], ['id' => 1]);
        $this->getTableLocator()->get('Departments')->updateAll(['cgr_strategy_id' => 1], ['id' => 2]);
        $cgrCodes = $this->getTableLocator()->get('CgrCodes');
        $cgrCodes->updateAll(['type' => ' axe '], ['id' => 1]);
        $forbidden = $cgrCodes->newEntity([
            'type' => 'NIVEAU',
            'code' => 'CGR-NIVEAU',
            'label' => 'CGR niveau',
            'active' => true,
            'is_system' => false,
        ]);
        $cgrCodes->saveOrFail($forbidden);

        $this->get('/api/departments/2/cgrs/available.json');
        $available = (array)json_decode((string)$this->_response->getBody(), true);

        $this->assertResponseOk();
        $this->assertSame([1], array_column($available['data'], 'id'));

        $links = $this->getTableLocator()->get('DepartmentCgrCodes');
        $links->saveOrFail($links->newEntity([
            'department_id' => 2,
            'cgr_code_id' => $forbidden->get('id'),
        ]));

        $this->get('/api/departments/2/cgrs/associated.json');
        $associated = (array)json_decode((string)$this->_response->getBody(), true);

        $this->assertResponseOk();
        $this->assertSame([], $associated['data']);

        $this->getTableLocator()->get('Departments')->updateAll(['cgr_strategy_id' => null], ['id' => 2]);
        $this->get('/api/departments/2/cgrs/available.json');
        $withoutStrategy = (array)json_decode((string)$this->_response->getBody(), true);

        $this->assertResponseOk();
        $this->assertSame([], $withoutStrategy['data']);
    }

    public function testUnCgrEstAssocieEtDissocieImmediatement(): void
    {
        $this->session(['Auth' => new User(['id' => 1, 'issuperuser' => true, 'role_id' => 1])]);
        $this->enableCsrfToken();
        $this->configRequest(['headers' => ['Accept' => 'application/json']]);
        $this->getTableLocator()->get('Departments')->updateAll(['deleted' => null], ['id' => 1]);
        $this->getTableLocator()->get('CgrStrategies')->updateAll([
            'definition_json' => json_encode([['type' => 'AXE']], JSON_THROW_ON_ERROR),
        ], ['id' => 1]);
        $this->getTableLocator()->get('Departments')->updateAll(['cgr_strategy_id' => 1], ['id' => 1]);
        $this->getTableLocator()->get('CgrCodes')->updateAll(['type' => 'NIVEAU'], ['id' => 1]);
        $this->getTableLocator()->get('DepartmentCgrCodes')->deleteAll([
            'department_id' => 1,
            'cgr_code_id' => 1,
        ]);

        $this->post('/api/departments/1/cgrs/1/add.json');

        $this->assertResponseCode(409);
        $this->assertSame(0, $this->getTableLocator()->get('DepartmentCgrCodes')->find()
            ->where(['department_id' => 1, 'cgr_code_id' => 1])->count());

        $this->getTableLocator()->get('CgrCodes')->updateAll(['type' => ' axe '], ['id' => 1]);
        $this->post('/api/departments/1/cgrs/1/add.json');

        $this->assertResponseOk();
        $this->assertSame(1, $this->getTableLocator()->get('DepartmentCgrCodes')->find()
            ->where(['department_id' => 1, 'cgr_code_id' => 1])->count());

        $this->post('/api/departments/1/cgrs/1/remove.json');

        $this->assertResponseOk();
        $this->assertSame(0, $this->getTableLocator()->get('DepartmentCgrCodes')->find()
            ->where(['department_id' => 1, 'cgr_code_id' => 1])->count());
    }

    public function testUnAdministrateurPeutAssocierUnCgrVisibleDansLaGrille(): void
    {
        $this->session(['Auth' => new User([
            'id' => 2,
            'issuperuser' => false,
            'role_id' => User::ROLE_ADMIN,
        ])]);
        $this->enableCsrfToken();
        $this->configRequest(['headers' => ['Accept' => 'application/json']]);
        $userDepartments = $this->getTableLocator()->get('UserDepartments');
        $userDepartments->saveOrFail($userDepartments->newEntity([
            'user_id' => 2,
            'department_id' => 2,
        ]));
        $this->getTableLocator()->get('Departments')->updateAll([
            'deleted' => null,
            'cgr_strategy_id' => 1,
        ], ['id' => 2]);
        $this->getTableLocator()->get('CgrStrategies')->updateAll([
            'definition_json' => json_encode([['type' => 'AXE']], JSON_THROW_ON_ERROR),
        ], ['id' => 1]);
        $this->getTableLocator()->get('CgrCodes')->updateAll(['type' => ' axe '], ['id' => 1]);
        $this->getTableLocator()->get('DepartmentCgrCodes')->deleteAll([
            'department_id' => 2,
            'cgr_code_id' => 1,
        ]);

        $this->post('/api/departments/2/cgrs/1/add.json');

        $this->assertResponseOk();
        $this->assertSame(1, $this->getTableLocator()->get('DepartmentCgrCodes')->find()
            ->where(['department_id' => 2, 'cgr_code_id' => 1])->count());
    }

    public function testLeFormulaireWebDeModificationUtiliseLeTemplatePartage(): void
    {
        $this->session(['Auth' => new User(['id' => 1, 'issuperuser' => true, 'role_id' => 1])]);
        $this->getTableLocator()->get('Departments')->updateAll(['deleted' => null], ['id' => 1]);

        $this->get('/departments/edit/1');

        $this->assertResponseOk();
        $this->assertResponseContains('department-cgrs-available-table');
        $this->assertResponseContains('department-cgrs-associated-table');
        $this->assertResponseContains('department-managers-available-table');
        $this->assertResponseContains('department-managers-associated-table');
        $this->assertResponseContains('department-tabs');
        $this->assertResponseNotContains('department-cgr-codes-table');
        $this->assertResponseNotContains('Type de département');
        $this->assertResponseContains('Modifier le département');
        $this->assertResponseContains('Département socle');
    }

    public function testLeFormulaireWebDeCreationExposeLeChampDepartementSocle(): void
    {
        $this->session(['Auth' => new User(['id' => 1, 'issuperuser' => true, 'role_id' => 1])]);

        $this->get('/departments/add');

        $this->assertResponseOk();
        $this->assertResponseContains('Département socle');
    }
}

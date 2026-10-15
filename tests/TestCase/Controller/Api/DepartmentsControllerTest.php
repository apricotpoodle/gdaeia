<?php
declare(strict_types=1);

namespace App\Test\TestCase\Controller\Api;

use App\Model\Entity\User;
use Cake\TestSuite\IntegrationTestTrait;
use Cake\TestSuite\TestCase;

final class DepartmentsControllerTest extends TestCase
{
    use IntegrationTestTrait;

    protected array $fixtures = [
        'app.Users',
        'app.Departments',
        'app.CgrCodes',
        'app.CgrStrategies',
        'app.UserDepartments',
        'app.Applicationforms',
        'app.Validationsequences',
    ];

    public function testUnOperateurNePeutPasAccederAAdministrationDesDepartements(): void
    {
        $this->session(['Auth' => new User(['id' => 2, 'issuperuser' => false, 'role_id' => 1])]);

        $this->get('/departments');
        $this->assertResponseCode(403);

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
        $this->getTableLocator()->get('Departments')->updateAll([
            'current_manager_id' => 2,
        ], ['id' => 2]);

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
            'department_type_id' => 1,
        ]);
        $this->assertResponseCode(200);
        $payload = (array)json_decode((string)$this->_response->getBody(), true);
        $id = (int)($payload['id'] ?? 0);
        $this->assertGreaterThan(0, $id);

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

    public function testLeFormulaireWebDeModificationUtiliseLeTemplatePartage(): void
    {
        $this->session(['Auth' => new User(['id' => 1, 'issuperuser' => true, 'role_id' => 1])]);
        $this->getTableLocator()->get('Departments')->updateAll(['deleted' => null], ['id' => 1]);

        $this->get('/departments/edit/1');

        $this->assertResponseOk();
        $this->assertResponseContains('department-cgr-codes-table');
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

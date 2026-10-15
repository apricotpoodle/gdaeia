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
}

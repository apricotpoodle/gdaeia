<?php
declare(strict_types=1);

namespace App\Test\TestCase\Controller\Api;

use App\Model\Entity\User;
use Cake\TestSuite\IntegrationTestTrait;
use Cake\TestSuite\TestCase;

final class PermissionsControllerTest extends TestCase
{
    use IntegrationTestTrait;

    protected array $fixtures = [
        'app.Users',
        'app.Roles',
        'app.Permissions',
        'app.PermissionScopes',
        'app.RolePermissions',
    ];

    public function testLeCatalogueEstAccessibleAuSuperAdministrateur(): void
    {
        $this->session(['Auth' => new User(['id' => 1, 'issuperuser' => true, 'role_id' => 1])]);
        $this->enableCsrfToken();
        $this->configRequest(['headers' => ['Accept' => 'application/json']]);

        $this->get('/api/permissions.json');

        $this->assertResponseOk();
        $payload = (array)json_decode((string)$this->_response->getBody(), true);
        self::assertTrue($payload['success']);
        self::assertCount(3, $payload['data']);
    }

    public function testUnOperateurNePeutPasConsulterLeCatalogue(): void
    {
        $this->session(['Auth' => new User(['id' => 2, 'issuperuser' => false, 'role_id' => User::ROLE_DEMANDEUR])]);

        $this->get('/api/permissions.json');

        $this->assertResponseCode(403);
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
}

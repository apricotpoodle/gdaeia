<?php
declare(strict_types=1);

namespace App\Test\TestCase\Controller\Api;

use App\Model\Entity\User;
use Cake\TestSuite\IntegrationTestTrait;
use Cake\TestSuite\TestCase;

/**
 * @link \App\Controller\Api\RolesController
 */
class RolesControllerTest extends TestCase
{
    use IntegrationTestTrait;

    /**
     * @var array<string>
     */
    protected array $fixtures = ['app.Roles', 'app.Users'];

    public function testLApiDesRolesRedirigeUnVisiteurVersLaConnexion(): void
    {
        $this->get('/api/roles.json');

        $this->assertRedirectContains('/users/login');
    }

    public function testLeCrudApiDesRolesEstReserveAuSuperAdministrateur(): void
    {
        $this->session(['Auth' => new User(['id' => 1, 'issuperuser' => true, 'role_id' => 1])]);
        $this->enableCsrfToken();
        $this->configRequest(['headers' => ['Accept' => 'application/json']]);

        $this->post('/api/roles.json', [
            'code' => 'GESTIONNAIRE',
            'name' => 'Gestionnaire',
            'sort' => 'gestionnaire',
            'base' => true,
        ]);
        $this->assertResponseOk();
        $this->assertResponseContains('"success":true');

        $roles = $this->getTableLocator()->get('Roles');
        $role = $roles->find()->where(['code' => 'GESTIONNAIRE'])->firstOrFail();
        $this->assertFalse($role->base);

        $this->put('/api/roles/' . $role->id . '.json', [
            'code' => 'GESTIONNAIRE',
            'name' => 'Gestionnaire confirmé',
            'sort' => 'gestionnaire',
        ]);
        $this->assertResponseOk();

        $this->delete('/api/roles/' . $role->id . '.json');
        $this->assertResponseOk();
        $this->assertNotNull($roles->get($role->id)->deleted);
    }

    public function testLaGrilleNeRetourneQueLesRolesActifs(): void
    {
        $this->session(['Auth' => new User(['id' => 1, 'issuperuser' => true, 'role_id' => 1])]);
        $this->getTableLocator()->get('Roles')->updateAll(['deleted' => '2026-09-15 12:00:00'], ['id' => 1]);

        $this->get('/api/roles.json');

        $this->assertResponseOk();
        $this->assertResponseRegExp('/"data"\\s*:\\s*\\[\\]/');
    }
}

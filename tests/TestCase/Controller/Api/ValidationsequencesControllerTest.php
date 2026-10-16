<?php
declare(strict_types=1);

namespace App\Test\TestCase\Controller\Api;

use App\Model\Entity\User;
use Cake\TestSuite\IntegrationTestTrait;
use Cake\TestSuite\TestCase;

/**
 * @link \App\Controller\Api\ValidationsequencesController
 */
class ValidationsequencesControllerTest extends TestCase
{
    use IntegrationTestTrait;

    /**
     * @var array<string>
     */
    protected array $fixtures = [
        'app.Departments',
        'app.Roles',
        'app.Validationsequences',
        'app.Applicationforms',
        'app.Applicationvalidationsteps',
    ];

    public function testUnSuperAdministrateurConfigureUnSousArbreEtSonNumeroDeSequence(): void
    {
        $this->getTableLocator()->get('Departments')->updateAll(['deleted' => null], ['id' => 1]);
        $this->getTableLocator()->get('Roles')->updateAll(['deleted' => null], ['id' => 1]);
        $this->getTableLocator()->get('Roles')->getConnection()->insert('roles', [
            'id' => 2,
            'base' => 0,
            'code' => 'DIRECTION',
            'name' => 'Direction',
            'sort' => '2',
        ]);
        $this->session(['Auth' => new User(['id' => 1, 'issuperuser' => true, 'role_id' => 1])]);
        $this->enableCsrfToken();
        $this->configRequest(['headers' => ['Accept' => 'application/json']]);

        $this->post('/api/validationsequences/assign-role.json', [
            'department_ids' => [1],
            'role_id' => 1,
        ]);

        $this->assertResponseOk();
        $this->assertResponseContains('"associations_created":2');
        $sequences = $this->getTableLocator()->get('Validationsequences');
        $this->assertSame(1, $sequences->find()->where(['department_id' => 2, 'role_id' => 1, 'name' => '', 'sequence' => 1])->count());

        $this->post('/api/validationsequences/assign-role.json', [
            'department_ids' => [1],
            'role_id' => 2,
        ]);

        $this->assertResponseOk();
        $this->assertSame(4, $sequences->find()->where(['sequence' => 1])->count());

        $this->post('/api/validationsequences/update-sequence.json', [
            'department_ids' => [1],
            'role_id' => 2,
            'sequence' => 3,
        ]);

        $this->assertResponseCode(400);
        $this->assertHeaderContains('Content-Type', 'application/json');
        $this->assertResponseContains('Chaque département doit disposer d’une séquence continue');
        $this->assertSame(2, $sequences->find()->where(['role_id' => 2, 'sequence' => 1])->count());

        $this->post('/api/validationsequences/update-sequence.json', [
            'department_ids' => [1],
            'role_id' => 2,
            'sequence' => 2,
        ]);

        $this->assertResponseOk();
        $this->assertSame(2, $sequences->find()->where(['role_id' => 2, 'sequence' => 2])->count());

        $this->get('/api/validationsequences/assigned-roles.json?department_ids[]=1');

        $this->assertResponseOk();
        $this->assertResponseRegExp('/"sequence"\\s*:\\s*2/');

        $this->post('/api/validationsequences/unassign-role.json', [
            'department_ids' => [1],
            'role_id' => 1,
        ]);

        $this->assertResponseCode(400);
        $this->assertSame(2, $sequences->find()->where(['role_id' => 1, 'sequence' => 1])->count());

        $this->post('/api/validationsequences/unassign-role.json', [
            'department_ids' => [1],
            'role_id' => 2,
        ]);

        $this->assertResponseOk();

        $this->post('/api/validationsequences/unassign-role.json', [
            'department_ids' => [1],
            'role_id' => 1,
        ]);

        $this->assertResponseOk();
        $this->assertSame(0, $sequences->find()->where([
            'department_id IN' => [1, 2],
            'deleted IS' => null,
        ])->count());
    }

    public function testLEcranEstRefuseAUnOperateurNonAutorise(): void
    {
        $this->session(['Auth' => new User(['id' => 1, 'issuperuser' => false, 'role_id' => 1])]);

        $this->get('/validationsequences');

        $this->assertResponseCode(403);
    }

    public function testLaDesactivationConserveUneSequenceReferenceeParUneEtape(): void
    {
        $connection = $this->getTableLocator()->get('Validationsequences')->getConnection();
        $connection->update('departments', ['deleted' => null], ['id' => 1]);
        $connection->update('roles', ['deleted' => null], ['id' => 1]);
        $connection->update('validationsequences', ['deleted' => null], ['id' => 1]);
        $connection->update('applicationforms', ['deleted' => null], ['id' => 1]);
        $connection->update('applicationvalidationsteps', ['deleted' => null], ['id' => 1]);
        $connection->delete('validationsequences', ['department_id' => 1, 'role_id' => 1]);
        $connection->insert('validationsequences', [
            'id' => 900,
            'department_id' => 1,
            'name' => '',
            'description' => null,
            'role_id' => 1,
            'sequence' => 1,
            'deleted' => null,
            'modified' => date('Y-m-d H:i:s'),
            'created' => date('Y-m-d H:i:s'),
        ]);
        $connection->update('applicationvalidationsteps', ['validationsequence_id' => 900], ['id' => 1]);

        $this->session(['Auth' => new User(['id' => 1, 'issuperuser' => true, 'role_id' => 1])]);
        $this->enableCsrfToken();
        $this->configRequest(['headers' => ['Accept' => 'application/json']]);

        $this->post('/api/validationsequences/unassign-role.json', [
            'department_ids' => [1],
            'role_id' => 1,
        ]);

        $this->assertResponseOk();
        $sequence = $connection->execute(
            'SELECT id, deleted FROM validationsequences WHERE id = 900',
        )->fetch('assoc');
        $this->assertSame(900, (int)$sequence['id']);
        $this->assertNotNull($sequence['deleted']);

        $this->post('/api/validationsequences/assign-role.json', [
            'department_ids' => [1],
            'role_id' => 1,
            'sequence' => 1,
        ]);

        $this->assertResponseOk();
        $sequence = $connection->execute(
            'SELECT id, deleted FROM validationsequences WHERE id = 900',
        )->fetch('assoc');
        $this->assertSame(900, (int)$sequence['id']);
        $this->assertNull($sequence['deleted']);
    }
}

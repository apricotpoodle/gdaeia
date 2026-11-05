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
        $connection->delete('user_departments', ['user_id IN' => [2, 30]]);
        $connection->delete('users', ['id' => 30]);
        $connection->update('departments', ['deleted' => null], ['id' => 1]);
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
        foreach (
            [
                ['user_id' => 2, 'department_id' => 2],
                ['user_id' => 30, 'department_id' => 1],
                ['user_id' => 30, 'department_id' => 2],
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
        $connection->delete('user_departments', ['user_id IN' => [2, 30]]);
        $connection->delete('users', ['id' => 30]);

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
            [2],
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
            [2],
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
}

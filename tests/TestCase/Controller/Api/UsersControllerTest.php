<?php
declare(strict_types=1);

namespace App\Test\TestCase\Controller\Api;

use App\Model\Entity\User;
use Cake\TestSuite\IntegrationTestTrait;
use Cake\TestSuite\TestCase;

class UsersControllerTest extends TestCase
{
    use IntegrationTestTrait;

    /** @var array<string> */
    protected array $fixtures = [
        'app.Users',
        'app.Departments',
        'app.UserDepartments',
    ];

    public function testLApiDesUtilisateursRedirigeUnVisiteurVersLaConnexion(): void
    {
        $this->get('/api/users.json');

        $this->assertRedirectContains('/users/login');
    }

    public function testLApiDesUtilisateursRetourneDuJsonPourUnOperateurConnecte(): void
    {
        $this->session(['Auth' => new User(['id' => 1, 'issuperuser' => true, 'role_id' => 1])]);
        $this->get('/api/users.json');

        $this->assertResponseOk();
        $this->assertHeaderContains('Content-Type', 'application/json');
    }

    public function testLApiAssocieUnPerimetreExpliciteAPlusieursUtilisateurs(): void
    {
        $this->session(['Auth' => new User(['id' => 1, 'issuperuser' => true, 'role_id' => 1])]);
        $this->enableCsrfToken();
        $this->configRequest([
            'headers' => [
                'Accept' => 'application/json',
            ],
        ]);

        $this->post('/api/users/bulk-departments.json', [
            'user_ids' => [1, 2],
            'department_ids' => [2],
        ]);

        $this->assertResponseOk();
        $this->assertResponseContains('"associations_created":2');

        $userDepartments = $this->getTableLocator()->get('UserDepartments');
        $this->assertSame(2, $userDepartments->find()->where(['department_id' => 2])->count());
    }

    public function testLApiAssocieEtRetireUnUtilisateurDesDepartementsSelectionnes(): void
    {
        $this->session(['Auth' => new User(['id' => 1, 'issuperuser' => true, 'role_id' => 1])]);
        $this->enableCsrfToken();
        $this->configRequest(['headers' => ['Accept' => 'application/json']]);

        $this->post('/api/users/assign-bulk-departments.json', [
            'user_id' => 2,
            'department_ids' => [2],
        ]);

        $this->assertResponseOk();
        $this->assertResponseContains('"associations_created":1');

        $this->post('/api/users/unassign-bulk-departments.json', [
            'user_id' => 2,
            'department_ids' => [2],
        ]);

        $this->assertResponseOk();
        $this->assertResponseContains('"associations_deleted":1');
    }

    public function testLEcranDAssociationMultipleEstAccessibleParUnSuperAdministrateur(): void
    {
        $this->session(['Auth' => new User(['id' => 1, 'issuperuser' => true, 'role_id' => 1])]);

        $this->get('/users/bulk-departments');

        $this->assertResponseOk();
        $this->assertResponseContains('Associer des départements à plusieurs utilisateurs');
        $this->assertResponseContains('id="bulk-departments-table"');
        $this->assertResponseContains('id="bulk-available-users-table"');
        $this->assertResponseContains('id="bulk-selected-users-table"');
        $this->assertResponseNotContains('bulk-departments-replace');
    }

    public function testLeLienDAssociationDesDepartementsEstMasqueSansDroitDeCreation(): void
    {
        $this->session(['Auth' => new User(['id' => 2, 'issuperuser' => false, 'role_id' => 2])]);

        $this->get('/users');

        $this->assertResponseOk();
        $this->assertResponseNotContains('Associer des départements');
    }

    public function testLeLienDAssociationDesDepartementsEstAfficheAvecLeDroitDeCreation(): void
    {
        $this->session(['Auth' => new User(['id' => 1, 'issuperuser' => true, 'role_id' => 1])]);

        $this->get('/users');

        $this->assertResponseOk();
        $this->assertResponseContains('Associer des départements');
    }

    public function testLesLiensPublicsDAuthentificationSontRendusSansSession(): void
    {
        $this->get('/users/login');
        $this->assertResponseOk();
        $this->assertResponseContains('Mot de passe oublié ?');

        $this->get('/users/forgot-password');
        $this->assertResponseOk();
        $this->assertResponseContains('Retour à la connexion');
    }
}

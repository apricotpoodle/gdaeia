<?php
declare(strict_types=1);

namespace App\Test\TestCase\Controller\Api;

use App\Model\Entity\User;
use Cake\TestSuite\IntegrationTestTrait;
use Cake\TestSuite\TestCase;

/**
 * @link \App\Controller\Api\UsersController
 */
class UsersControllerTest extends TestCase
{
    use IntegrationTestTrait;

    /**
     * @var array<string>
     */
    protected array $fixtures = [
        'app.Users',
        'app.Roles',
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

    public function testLApiRetourneLesUtilisateursDisponiblesPourLAssociationDeDepartements(): void
    {
        $this->session(['Auth' => new User(['id' => 1, 'issuperuser' => true, 'role_id' => 1])]);

        $this->get('/api/users/bulk-departments-users.json');

        $this->assertResponseOk();
        $this->assertResponseContains('"data"');
        $this->assertResponseContains('"last_page"');
    }

    public function testLApiRetourneUneListePagineeVideDesUtilisateursAssociesSansSelection(): void
    {
        $this->session(['Auth' => new User(['id' => 1, 'issuperuser' => true, 'role_id' => 1])]);

        $this->get('/api/users/bulk-departments-assigned-users.json?page=1&size=20');

        $this->assertResponseOk();
        $this->assertResponseRegExp('/"data"\\s*:\\s*\\[\\]/');
        $this->assertResponseContains('"last_page"');
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

    public function testLApiEtendUnDepartementParentAChacunDeSesEnfantsLorsDeLAssociation(): void
    {
        $this->getTableLocator()->get('Departments')->updateAll(['deleted' => null], ['id' => 1]);
        $this->session(['Auth' => new User(['id' => 1, 'issuperuser' => true, 'role_id' => 1])]);
        $this->enableCsrfToken();
        $this->configRequest(['headers' => ['Accept' => 'application/json']]);

        $this->post('/api/users/assign-bulk-departments.json', [
            'user_id' => 2,
            'department_ids' => [1],
        ]);

        $this->assertResponseOk();
        $this->assertResponseContains('"associations_created":2');
        $this->assertSame(2, $this->getTableLocator()->get('UserDepartments')->find()
            ->where(['user_id' => 2])
            ->count());

        $this->get('/api/users/bulk-departments-assigned-users.json?department_ids[]=1');

        $this->assertResponseOk();
        $this->assertResponseContains('utilisateur-de-test@example.test');
        $this->assertResponseContains('"last_page"');

        $this->get('/api/users/bulk-departments-users.json?department_ids[]=1');

        $this->assertResponseOk();
        $this->assertResponseNotContains('utilisateur-de-test@example.test');
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

    public function testLeRetourDUsurpationSansSessionDUsurpationRevientALIndex(): void
    {
        $this->session(['Auth' => new User(['id' => 1, 'issuperuser' => true, 'role_id' => 1])]);

        $this->get('/users/revert_identity');

        $this->assertRedirectContains('/users');
    }

    public function testLaConnexionNeSuitPasUnRetourDUsurpationSansSessionDUsurpation(): void
    {
        $this->get('/users/revert_identity');

        $this->assertRedirectContains('/users/login?redirect=%2Fusers%2Frevert_identity');

        $this->session(['Auth' => new User(['id' => 1, 'issuperuser' => true, 'role_id' => 1])]);
        $this->get('/users/login?redirect=%2Fusers%2Frevert_identity');

        $this->assertRedirect('/users/index');
    }

    public function testLaConnexionConserveUnRetourInterneApplicable(): void
    {
        $this->get('/users/bulk-departments');

        $this->assertRedirectContains('/users/login?redirect=%2Fusers%2Fbulk-departments');

        $this->session(['Auth' => new User(['id' => 1, 'issuperuser' => true, 'role_id' => 1])]);
        $this->get('/users/login?redirect=%2Fusers%2Fbulk-departments');

        $this->assertRedirect('/users/bulk-departments');
    }

    public function testLaConnexionIgnoreUnRetourExterne(): void
    {
        $this->session(['Auth' => new User(['id' => 1, 'issuperuser' => true, 'role_id' => 1])]);

        $this->get('/users/login?redirect=https%3A%2F%2Fexample.test%2Fconnexion');

        $this->assertRedirect('/users/index');
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

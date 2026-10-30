<?php
declare(strict_types=1);

namespace App\Test\TestCase\Controller;

use App\Model\Entity\User;
use Cake\Datasource\ConnectionManager;
use Cake\TestSuite\IntegrationTestTrait;
use Cake\TestSuite\TestCase;

/** Vérifie la route Web et le contenu minimal de l'export PDF des DAE. */
class ApplicationformsControllerTest extends TestCase
{
    use IntegrationTestTrait;

    protected array $fixtures = [
        'app.Applicationforms',
        'app.Departments',
        'app.Users',
        'app.Contracttypes',
        'app.Hiringreasons',
        'app.Budgetfeatures',
        'app.Professionalcategories',
        'app.Worktimes',
        'app.Periods',
        'app.Yesnos',
        'app.UserDepartments',
        'app.Roles',
        'app.ValidationWorkflowRuns',
        'app.Applicationvalidationsteps',
        'app.Validations',
        'app.Validationstatuses',
    ];

    public function testLePdfEstRetournePourUnOperateurAutorise(): void
    {
        $this->session(['Auth' => new User(['id' => 1, 'issuperuser' => true, 'role_id' => User::ROLE_ADMIN])]);

        $this->get('/applicationforms/viewpdf/1');

        $this->assertResponseOk();
        $this->assertHeaderContains('Content-Type', 'application/pdf');
        $this->assertHeaderContains('Content-Disposition', 'inline; filename="dae-1.pdf"');
        $this->assertStringStartsWith('%PDF', (string)$this->_response->getBody());
    }

    public function testLePdfRefuseUneDemandeHorsPerimetre(): void
    {
        $this->session(['Auth' => new User(['id' => 2, 'issuperuser' => false, 'role_id' => User::ROLE_DEMANDEUR])]);

        $this->get('/applicationforms/viewpdf/1');

        $this->assertResponseCode(403);
    }

    public function testLaCreationPreselectionneLOperateurCommeAssistante(): void
    {
        $this->session(['Auth' => new User(['id' => 1, 'issuperuser' => true, 'role_id' => User::ROLE_ADMIN])]);

        $this->get('/applicationforms/add');

        $this->assertResponseOk();
        $this->assertMatchesRegularExpression(
            '/<select[^>]+name="assistant_id"[^>]*>.*<option(?=[^>]*value="1")(?=[^>]*selected)[^>]*>/s',
            (string)$this->_response->getBody(),
        );
    }

    public function testLEditionUtiliseLeDemandeurCommeAssistanteParDefaut(): void
    {
        ConnectionManager::get('test')->update('applicationforms', ['assistant_id' => null], ['id' => 1]);
        $this->session(['Auth' => new User(['id' => 1, 'issuperuser' => true, 'role_id' => User::ROLE_ADMIN])]);

        $this->get('/applicationforms/edit/1');

        $this->assertResponseOk();
        $this->assertMatchesRegularExpression(
            '/<select[^>]+name="assistant_id"[^>]*>.*<option(?=[^>]*value="1")(?=[^>]*selected)[^>]*>/s',
            (string)$this->_response->getBody(),
        );
    }

    public function testLEditionAfficheLesJoursOuvresPresDeLaDateDeFin(): void
    {
        $this->session(['Auth' => new User(['id' => 1, 'issuperuser' => true, 'role_id' => User::ROLE_ADMIN])]);

        $this->get('/applicationforms/edit/1');

        $this->assertResponseOk();
        $body = (string)$this->_response->getBody();
        $endAtPosition = strpos($body, 'id="end-at"');
        $workingDaysPosition = strpos($body, 'id="working-days"');

        $this->assertNotFalse($endAtPosition);
        $this->assertNotFalse($workingDaysPosition);
        $this->assertLessThan($workingDaysPosition, $endAtPosition);
        $this->assertStringContainsString('views/Applicationforms/zone-contract.js', $body);
    }

    public function testLaFicheAfficheLAbsenceDeChefDeServiceSansChampDeSaisie(): void
    {
        $this->session(['Auth' => new User(['id' => 1, 'issuperuser' => true, 'role_id' => User::ROLE_ADMIN])]);

        $this->get('/applicationforms/edit/1');

        $this->assertResponseOk();
        $this->assertResponseContains('Aucun chef de service associé');
        $this->assertStringNotContainsString('name="manager_id"', (string)$this->_response->getBody());
    }

    public function testLaNavigationAfficheLeFuseauHoraireApplicatif(): void
    {
        $this->session(['Auth' => new User(['id' => 1, 'issuperuser' => true, 'role_id' => User::ROLE_ADMIN])]);

        $this->get('/applicationforms/edit/1');

        $this->assertResponseOk();
        $this->assertResponseContains('id="main-navbar-timezone"');
        $this->assertResponseContains('Europe/Paris');
    }

    public function testLaFicheAfficheLeChefDeServiceDuDepartement(): void
    {
        ConnectionManager::get('test')->update('departments', ['current_manager_id' => 1], ['id' => 1]);
        $this->session(['Auth' => new User(['id' => 1, 'issuperuser' => true, 'role_id' => User::ROLE_ADMIN])]);

        $this->get('/applicationforms/edit/1');

        $this->assertResponseOk();
        $this->assertResponseContains('Chef de service');
        $this->assertStringNotContainsString('name="manager_id"', (string)$this->_response->getBody());
    }

    public function testLaSoumissionAvecDesListesAssistantEtManagerVidesNeProduitPasDErreurDExistence(): void
    {
        $this->enableCsrfToken();
        $this->session(['Auth' => new User(['id' => 1, 'issuperuser' => true, 'role_id' => User::ROLE_ADMIN])]);
        $this->post('/applicationforms/edit/1', [
            'assistant_id' => '',
            'manager_id' => '',
            'jobtitle' => 'Poste modifié',
        ]);

        $this->assertRedirect(['controller' => 'Applicationforms', 'action' => 'index']);
    }
}

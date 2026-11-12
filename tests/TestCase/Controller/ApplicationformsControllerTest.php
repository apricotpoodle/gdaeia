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
        'app.DepartmentManagers',
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

    public function testLaConsultationOrganiseLesDetailsEnQuatreZonesEnLectureSeule(): void
    {
        $this->session(['Auth' => new User(['id' => 1, 'issuperuser' => true, 'role_id' => User::ROLE_ADMIN])]);

        $this->get('/applicationforms/view/1');

        $this->assertResponseOk();
        $body = (string)$this->_response->getBody();
        $labels = [
            'Zone 1 : Administration & Candidat',
            '2. Caractéristiques du Contrat',
            '3. Temps de travail & Rémunération',
            '4. Champs Réservés Administration/RH',
        ];

        $positions = [];
        foreach ($labels as $label) {
            $this->assertStringContainsString($label, $body);
            $positions[] = strpos($body, $label);
        }

        $this->assertSame($positions, array_values(array_filter($positions, static fn($position): bool => $position !== false)));
        $this->assertStringNotContainsString('name="jobtitle"', $body);
        $this->assertStringNotContainsString('name="contracttype_id"', $body);
        $this->assertStringNotContainsString('name="grossremuneration"', $body);
        $this->assertStringNotContainsString('name="hr_description"', $body);
        $this->assertStringContainsString('03/07/2026', $body);
    }

    public function testLaZoneReserveeNestPasExposeeSansDroitDeConsultation(): void
    {
        $this->session(['Auth' => new User([
            'id' => 1,
            'issuperuser' => false,
            'role_id' => User::ROLE_DEMANDEUR,
        ])]);

        $this->get('/applicationforms/view/1');

        $this->assertResponseOk();
        $this->assertStringNotContainsString('4. Champs Réservés Administration/RH', (string)$this->_response->getBody());
    }

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

    public function testLaCreationDesactiveLesAssistantesSansDepartementEtAfficheLeDepartementEnPremier(): void
    {
        $this->session(['Auth' => new User(['id' => 1, 'issuperuser' => true, 'role_id' => User::ROLE_ADMIN])]);

        $this->get('/applicationforms/add');

        $this->assertResponseOk();
        $body = (string)$this->_response->getBody();
        $departmentPosition = strpos($body, 'id="department-tree-select"');
        $assistantPosition = strpos($body, 'name="assistant_id"');

        $this->assertNotFalse($departmentPosition);
        $this->assertNotFalse($assistantPosition);
        $this->assertLessThan($assistantPosition, $departmentPosition);
        $this->assertStringContainsString('data-can-load="true"', $body);
        $this->assertStringContainsString('data-managers-url="/api/applicationforms/managers-for-department.json"', $body);
        $this->assertMatchesRegularExpression('/<select(?=[^>]*name="assistant_id")(?=[^>]*disabled)[^>]*>/', $body);
        $this->assertStringContainsString('Aucune assistante éligible pour ce département.', $body);
        $this->assertDoesNotMatchRegularExpression(
            '/<select[^>]+name="assistant_id"[^>]*>.*<option(?=[^>]*value="1")(?=[^>]*selected)[^>]*>/s',
            $body,
        );
    }

    public function testLEditionPreselectionneLOperateurSeulementSilEstEligible(): void
    {
        $connection = ConnectionManager::get('test');
        $connection->update('applicationforms', [
            'assistant_id' => null,
            'department_id' => 2,
        ], ['id' => 1]);
        $connection->update('departments', ['deleted' => null], ['id' => 2]);
        $connection->update('users', [
            'role_id' => User::ROLE_DEMANDEUR,
            'deleted' => null,
        ], ['id' => 1]);
        $connection->update('roles', ['deleted' => null], ['id' => User::ROLE_ADMIN]);
        $connection->insert('roles', [
            'id' => User::ROLE_DEMANDEUR,
            'base' => 1,
            'code' => 'DEMANDEUR',
            'name' => 'Demandeur',
            'sort' => 'Demandeur',
            'deleted' => null,
            'created' => '2026-07-03 09:01:32',
            'modified' => '2026-07-03 09:01:32',
        ]);
        $userDepartments = $this->getTableLocator()->get('UserDepartments');
        $userDepartments->saveOrFail($userDepartments->newEntity([
            'user_id' => 1,
            'department_id' => 2,
        ]));
        $this->session(['Auth' => new User(['id' => 1, 'issuperuser' => true, 'role_id' => User::ROLE_DEMANDEUR])]);

        $this->get('/applicationforms/edit/1');

        $this->assertResponseOk();
        $this->assertMatchesRegularExpression(
            '/<select[^>]+name="assistant_id"[^>]*>.*<option(?=[^>]*value="1")(?=[^>]*selected)[^>]*>/s',
            (string)$this->_response->getBody(),
        );
    }

    public function testUneSoumissionWebRefuseUneAssistanteNonDemandeuse(): void
    {
        $this->session(['Auth' => new User(['id' => 1, 'issuperuser' => true, 'role_id' => User::ROLE_ADMIN])]);
        $this->enableCsrfToken();

        $this->post('/applicationforms/edit/1', ['assistant_id' => 2]);

        $this->assertResponseOk();
        $this->assertResponseContains('appartenir à ce département');
    }

    public function testUneSoumissionWebRefuseUneAssistanteDunDepartementHorsPerimetre(): void
    {
        $this->seedAssistantOutsideOperatorScope();
        $this->session(['Auth' => new User([
            'id' => 2,
            'issuperuser' => false,
            'role_id' => User::ROLE_ADMIN,
        ])]);
        $this->enableCsrfToken();

        $this->post('/applicationforms/edit/1', [
            'department_id' => 3,
            'assistant_id' => 3,
        ]);

        $this->assertResponseOk();
        $this->assertResponseContains('options visibles pour ce département');
        $applicationform = $this->getTableLocator()->get('Applicationforms')->get(1);
        $this->assertSame(2, (int)$applicationform->get('department_id'));
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
        $this->assertResponseContains('Aucun responsable associé');
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
        $table = $this->getTableLocator()->get('DepartmentManagers');
        $table->saveOrFail($table->newEntity([
            'department_id' => 1,
            'user_id' => 1,
        ]));
        $this->session(['Auth' => new User(['id' => 1, 'issuperuser' => true, 'role_id' => User::ROLE_ADMIN])]);

        $this->get('/applicationforms/edit/1');

        $this->assertResponseOk();
        $this->assertResponseContains('Responsables du département');
        $this->assertResponseContains('data-can-load="true"');
        $this->assertStringNotContainsString('name="manager_id"', (string)$this->_response->getBody());
    }

    public function testLaSoumissionAvecDesListesAssistantEtManagerVidesNeProduitPasDErreurDExistence(): void
    {
        $this->enableCsrfToken();
        $this->session(['Auth' => new User(['id' => 1, 'issuperuser' => true, 'role_id' => User::ROLE_ADMIN])]);
        $this->post('/applicationforms/edit/1', [
            'assistant_id' => '',
            'jobtitle' => 'Poste modifié',
        ]);

        $this->assertRedirect(['controller' => 'Applicationforms', 'action' => 'index']);
    }

    public function testLEditionVerrouilleLeContratEtLeDepartementApresUnCycleTermine(): void
    {
        ConnectionManager::get('test')->insert('validation_workflow_runs', [
            'applicationform_id' => 1, 'started_by_user_id' => 1, 'state' => 'acceptee',
            'started_at' => '2026-11-06 10:00:00', 'created' => '2026-11-06 10:00:00', 'modified' => '2026-11-06 10:00:00',
        ]);
        $this->session(['Auth' => new User(['id' => 1, 'issuperuser' => true, 'role_id' => User::ROLE_ADMIN])]);

        $this->get('/applicationforms/edit/1');

        $this->assertResponseOk();
        $body = (string)$this->_response->getBody();
        $this->assertMatchesRegularExpression('/<select(?=[^>]*name="contracttype_id")(?=[^>]*disabled)[^>]*>/', $body);
        $this->assertStringNotContainsString('id="department-tree-select"', $body);
        $this->assertStringContainsString('data-can-load="false"', $body);
        $this->assertStringContainsString('Le contrat et le département sont verrouillés', $body);
    }

    public function testUneSoumissionWebNeChangePasLeDepartementDUnCycleTermine(): void
    {
        ConnectionManager::get('test')->insert('validation_workflow_runs', [
            'applicationform_id' => 1, 'started_by_user_id' => 1, 'state' => 'refusee',
            'started_at' => '2026-11-06 10:00:00', 'created' => '2026-11-06 10:00:00', 'modified' => '2026-11-06 10:00:00',
        ]);
        $this->session(['Auth' => new User(['id' => 1, 'issuperuser' => true, 'role_id' => User::ROLE_ADMIN])]);
        $this->enableCsrfToken();

        $this->post('/applicationforms/edit/1', ['department_id' => 2]);

        $this->assertResponseOk();
        $this->assertSame(1, (int)$this->getTableLocator()->get('Applicationforms')->get(1)->department_id);
        $this->assertResponseContains('verrouillé');
    }

    private function seedAssistantOutsideOperatorScope(): void
    {
        $connection = ConnectionManager::get('test');
        $connection->update('applicationforms', [
            'deleted' => null,
            'user_id' => 2,
            'department_id' => 2,
        ], ['id' => 1]);
        $connection->update('departments', ['deleted' => null], ['id' => 2]);
        $connection->insert('departments', [
            'id' => 3,
            'parent_id' => null,
            'lft' => 5,
            'rght' => 6,
            'level' => 1,
            'base' => 1,
            'code' => 'AUTRE-SERVICE',
            'name' => 'Autre service',
            'sort' => 'Autre service',
            'department_type_id' => 1,
            'deleted' => null,
        ]);
        $connection->update('roles', ['deleted' => null], ['id' => User::ROLE_ADMIN]);
        $connection->insert('roles', [
            'id' => User::ROLE_DEMANDEUR,
            'base' => 1,
            'code' => 'DEMANDEUR',
            'name' => 'Demandeur',
            'sort' => 'Demandeur',
            'deleted' => null,
            'created' => '2026-07-03 09:01:32',
            'modified' => '2026-07-03 09:01:32',
        ]);
        $connection->insert('users', [
            'id' => 3,
            'username' => 'assistante-hors-perimetre',
            'email' => 'assistante-hors-perimetre@example.test',
            'password' => 'mot-de-passe',
            'firstname' => 'Assistante',
            'lastname' => 'Hors périmètre',
            'issuperuser' => 0,
            'role_id' => User::ROLE_DEMANDEUR,
            'deleted' => null,
            'created' => '2026-07-03 09:01:33',
            'modified' => '2026-07-03 09:01:33',
        ]);
        $userDepartments = $this->getTableLocator()->get('UserDepartments');
        $userDepartments->saveManyOrFail($userDepartments->newEntities([
            ['user_id' => 2, 'department_id' => 2],
            ['user_id' => 3, 'department_id' => 3],
        ]));
    }
}

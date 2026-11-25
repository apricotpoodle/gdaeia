<?php
declare(strict_types=1);

namespace App\Test\TestCase\Controller;

use App\Model\Entity\Period;
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
        'app.Permissions',
        'app.PermissionScopes',
        'app.Role1Permissions',
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
        $this->grantPermission(User::ROLE_DEMANDEUR, 'applicationforms', 'read', 'self');
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
        $this->assertStringContainsString('id="assistant-select-wrapper"', $body);
        $this->assertStringContainsString('id="assistant-options-single"', $body);
        $this->assertMatchesRegularExpression(
            '/<input(?=[^>]*type="hidden")(?=[^>]*name="applicantname")[^>]*>/s',
            $body,
        );
        $this->assertMatchesRegularExpression(
            '/<input(?=[^>]*name="applicant_first_name")[^>]*>/s',
            $body,
        );
        $this->assertMatchesRegularExpression(
            '/<input(?=[^>]*name="applicant_last_name")[^>]*>/s',
            $body,
        );
        $this->assertDoesNotMatchRegularExpression(
            '/<input(?=[^>]*type="text")(?=[^>]*name="applicantname")[^>]*>/s',
            $body,
        );
        $this->assertMatchesRegularExpression(
            '/<input(?=[^>]*type="hidden")(?=[^>]*name="assistant_id")(?=[^>]*id="assistant-single-id")(?=[^>]*disabled)[^>]*>/',
            $body,
        );
        $this->assertMatchesRegularExpression('/<select(?=[^>]*name="assistant_id")(?=[^>]*disabled)[^>]*>/', $body);
        $this->assertStringContainsString('Aucune assistante éligible pour ce département.', $body);
        $this->assertDoesNotMatchRegularExpression(
            '/<select[^>]+name="assistant_id"[^>]*>.*<option(?=[^>]*value="1")(?=[^>]*selected)[^>]*>/s',
            $body,
        );
    }

    public function testLaCreationNeProposePasLaPeriodeHoraire(): void
    {
        ConnectionManager::get('test')->update('periods', [
            'code' => Period::CODE_EUR_PER_HOUR,
            'deleted' => null,
        ], ['id' => 1]);
        $this->session(['Auth' => new User(['id' => 1, 'issuperuser' => true, 'role_id' => User::ROLE_ADMIN])]);

        $this->get('/applicationforms/add');

        $this->assertResponseOk();
        preg_match('/<select[^>]*id="period-id"[^>]*>(.*?)<\/select>/s', (string)$this->_response->getBody(), $matches);
        $this->assertArrayHasKey(1, $matches);
        $this->assertDoesNotMatchRegularExpression('/<option[^>]*value="1"/', $matches[1]);
    }

    public function testLEditionGardeLaPeriodeHoraireHistoriqueVisibleCommeTelle(): void
    {
        $connection = ConnectionManager::get('test');
        $connection->update('periods', [
            'code' => Period::CODE_EUR_PER_HOUR,
            'deleted' => null,
        ], ['id' => 1]);
        $connection->update('applicationforms', ['period_id' => 1, 'deleted' => null], ['id' => 1]);
        $this->session(['Auth' => new User(['id' => 1, 'issuperuser' => true, 'role_id' => User::ROLE_ADMIN])]);

        $this->get('/applicationforms/edit/1');

        $this->assertResponseOk();
        $this->assertResponseContains('€/heure — historique, choisir une unité prise en charge pour modifier');
    }

    public function testLaCreationDesactiveUnChampAbsentDeLaMatriceAcl(): void
    {
        $connection = ConnectionManager::get('test');
        $authorizationId = 900_100;
        $connection->insert('field_authorizations', [
            'id' => $authorizationId,
            'role_id' => User::ROLE_ADMIN,
            'resource' => 'Applicationforms',
            'target_type' => 'field',
            'target' => 'jobtitle',
            'access_level' => 'EDIT',
            'created' => '2026-11-12 10:00:00',
        ]);
        $this->session(['Auth' => new User([
            'id' => 1,
            'issuperuser' => false,
            'role_id' => User::ROLE_ADMIN,
        ])]);

        try {
            $this->get('/applicationforms/add');

            $this->assertResponseOk();
            $this->assertMatchesRegularExpression(
                '/<input(?=[^>]*name="working_days")(?=[^>]*disabled)[^>]*>/s',
                (string)$this->_response->getBody(),
            );
        } finally {
            $connection->delete('field_authorizations', ['id' => $authorizationId]);
        }
    }

    public function testUnDemandeurPeutCreerEtModifierLeNomDuCandidat(): void
    {
        $connection = ConnectionManager::get('test');
        $now = '2026-11-18 10:00:00';
        $connection->insert('roles', [
            'id' => User::ROLE_DEMANDEUR,
            'base' => 1,
            'code' => 'DEMANDEUR',
            'name' => 'Demandeur',
            'sort' => 'Demandeur',
            'deleted' => null,
            'created' => $now,
            'modified' => $now,
        ]);
        $this->grantPermission(User::ROLE_DEMANDEUR, 'applicationforms', 'create', 'self');
        $this->grantPermission(User::ROLE_DEMANDEUR, 'applicationforms', 'read', 'self');
        $this->grantPermission(User::ROLE_DEMANDEUR, 'applicationforms', 'update', 'self');
        $connection->update('users', ['role_id' => User::ROLE_DEMANDEUR], ['id' => 2]);
        $connection->insert('user_departments', [
            'id' => 2,
            'user_id' => 2,
            'department_id' => 2,
            'created' => $now,
            'modified' => $now,
        ]);
        foreach (['contracttypes', 'professionalcategories', 'periods'] as $table) {
            $connection->update($table, ['deleted' => null], ['id' => 1]);
        }
        $businessData = [
            'department_id' => 2,
            'contracttype_id' => 1,
            'hiringreason_id' => 1,
            'jobtitle' => 'Poste créé par le demandeur HTTP',
            'professionalcategory_id' => 1,
            'worktime_id' => 1,
            'grossremuneration' => '2500.00',
            'period_id' => 1,
            'applicantname' => '',
            'applicant_first_name' => 'Camille',
            'applicant_last_name' => 'Martin',
        ];
        $authorizationIds = [];
        try {
            foreach (array_keys($businessData) as $index => $field) {
                $authorizationId = 900_200 + $index;
                $connection->insert('field_authorizations', [
                    'id' => $authorizationId,
                    'role_id' => User::ROLE_DEMANDEUR,
                    'resource' => 'Applicationforms',
                    'target_type' => 'field',
                    'target' => $field,
                    'access_level' => 'EDIT',
                    'created' => $now,
                ]);
                $authorizationIds[] = $authorizationId;
            }
            $this->session(['Auth' => new User([
                'id' => 2,
                'issuperuser' => false,
                'role_id' => User::ROLE_DEMANDEUR,
            ])]);

            $this->get('/applicationforms/add');

            $this->assertResponseOk();
            $this->assertMatchesRegularExpression(
                '/<input(?=[^>]*name="jobtitle")(?![^>]*disabled)[^>]*>/s',
                (string)$this->_response->getBody(),
            );
            $applicationforms = $this->getTableLocator()->get('Applicationforms');
            $countBefore = $applicationforms->find()->count();
            $this->enableCsrfToken();

            $this->post('/applicationforms/add', $businessData + [
                'user_id' => 1,
                'hr_description' => 'Description RH injectée',
                'control_management_validation' => 'Validation injectée',
                'final_argument' => 'Argument final injecté',
                'budgetfeature_id' => 1,
                'yesno_id' => 1,
                'qualification' => 'Qualification injectée',
            ]);

            $this->assertRedirect(['controller' => 'Applicationforms', 'action' => 'index']);
            $this->assertSame($countBefore + 1, $applicationforms->find()->count());
            $created = $applicationforms->find()
                ->where(['jobtitle' => 'Poste créé par le demandeur HTTP'])
                ->firstOrFail();
            $this->assertSame(2, (int)$created->get('user_id'));
            $this->assertSame(2, (int)$created->get('department_id'));
            $this->assertSame('Camille', $created->get('applicant_first_name'));
            $this->assertSame('Martin', $created->get('applicant_last_name'));
            $this->assertSame('Camille Martin', $created->get('applicantname'));
            $this->assertNull($created->get('hr_description'));
            $this->assertNull($created->get('control_management_validation'));
            $this->assertNull($created->get('final_argument'));
            $this->assertNull($created->get('budgetfeature_id'));
            $this->assertNull($created->get('yesno_id'));
            $this->assertNull($created->get('qualification'));

            $createdId = (int)$created->get('id');
            $this->post('/applicationforms/edit/' . $createdId, [
                'applicantname' => '',
                'applicant_first_name' => 'Alice',
                'applicant_last_name' => 'Bernard',
            ]);

            $this->assertRedirect(['controller' => 'Applicationforms', 'action' => 'index']);
            $edited = $applicationforms->get($createdId);
            $this->assertSame('Alice', $edited->get('applicant_first_name'));
            $this->assertSame('Bernard', $edited->get('applicant_last_name'));
            $this->assertSame('Alice Bernard', $edited->get('applicantname'));

            $connection->update(
                'field_authorizations',
                ['access_level' => 'VIEW'],
                [
                    'role_id' => User::ROLE_DEMANDEUR,
                    'resource' => 'Applicationforms',
                    'target_type' => 'field',
                    'target IN' => ['applicantname', 'applicant_first_name', 'applicant_last_name'],
                ],
            );
            $this->post('/applicationforms/edit/' . $createdId, [
                'applicantname' => 'Nom falsifié',
                'applicant_first_name' => 'Nom',
                'applicant_last_name' => 'Falsifié',
            ]);

            $this->assertRedirect(['controller' => 'Applicationforms', 'action' => 'index']);
            $unchanged = $applicationforms->get($createdId);
            $this->assertSame('Alice', $unchanged->get('applicant_first_name'));
            $this->assertSame('Bernard', $unchanged->get('applicant_last_name'));
            $this->assertSame('Alice Bernard', $unchanged->get('applicantname'));
        } finally {
            if ($authorizationIds !== []) {
                $connection->delete('field_authorizations', ['id IN' => $authorizationIds]);
            }
        }
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
        $this->assertResponseContains('data-default-assistant-id="1"');
        $this->assertMatchesRegularExpression(
            '/<select[^>]+name="assistant_id"[^>]*>.*<option(?=[^>]*value="1")(?=[^>]*selected)[^>]*>/s',
            (string)$this->_response->getBody(),
        );
    }

    public function testUnValidateurPeutEditerSansDroitDeCreationEtConserverLeCgr(): void
    {
        $this->seedActiveValidatorForEdit();
        $this->grantPermission(User::ROLE_1_VALIDEUR_POLE, 'applicationforms', 'read', 'assigned');
        $this->grantPermission(User::ROLE_1_VALIDEUR_POLE, 'applicationforms', 'update', 'assigned');
        $this->session(['Auth' => new User([
            'id' => 2,
            'issuperuser' => false,
            'role_id' => User::ROLE_1_VALIDEUR_POLE,
        ])]);

        $this->get('/applicationforms/edit/1');

        $this->assertResponseOk();
        $body = (string)$this->_response->getBody();
        $this->assertStringContainsString('data-can-load-cgr-config="false"', $body);
        $this->assertMatchesRegularExpression(
            '/<input(?=[^>]*name="cgr")(?=[^>]*value="AAN02")(?=[^>]*readonly)[^>]*>/s',
            $body,
        );
    }

    public function testLEditionNePreselectionnePasUneAssistanteIneligible(): void
    {
        ConnectionManager::get('test')->update('applicationforms', ['assistant_id' => null], ['id' => 1]);
        $this->session(['Auth' => new User(['id' => 1, 'issuperuser' => true, 'role_id' => User::ROLE_ADMIN])]);

        $this->get('/applicationforms/edit/1');

        $this->assertResponseOk();
        $this->assertDoesNotMatchRegularExpression(
            '/<select(?=[^>]*name="assistant_id")(?:(?!<\/select>).)*<option(?=[^>]*value="1")(?=[^>]*selected)[^>]*>.*?<\/select>/s',
            (string)$this->_response->getBody(),
        );
    }

    public function testUneSoumissionWebRefuseUneAssistanteNonDemandeuse(): void
    {
        $this->session(['Auth' => new User(['id' => 1, 'issuperuser' => true, 'role_id' => User::ROLE_ADMIN])]);
        $this->enableCsrfToken();

        $this->post('/applicationforms/edit/1', ['assistant_id' => 2]);

        $this->assertResponseOk();
        $this->assertResponseContains('être rattachée au département sélectionné');
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

    public function testLEditionConserveLaSaisieUniqueDesAnciennesDemandes(): void
    {
        $this->session(['Auth' => new User(['id' => 1, 'issuperuser' => true, 'role_id' => User::ROLE_ADMIN])]);

        $this->get('/applicationforms/edit/1');

        $this->assertResponseOk();
        $body = (string)$this->_response->getBody();
        $this->assertMatchesRegularExpression(
            '/<input(?=[^>]*type="text")(?=[^>]*name="applicantname")(?=[^>]*value="Lorem ipsum dolor sit amet")[^>]*>/s',
            $body,
        );
        $this->assertStringNotContainsString('name="applicant_first_name"', $body);
        $this->assertStringNotContainsString('name="applicant_last_name"', $body);
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

    private function seedActiveValidatorForEdit(): void
    {
        $connection = ConnectionManager::get('test');
        $now = '2026-11-12 10:00:00';
        $connection->update('applicationforms', [
            'deleted' => null,
            'user_id' => 1,
            'department_id' => 1,
            'cgr' => 'AAN02',
        ], ['id' => 1]);
        $connection->update('departments', ['deleted' => null], ['id' => 1]);
        $connection->update('users', [
            'role_id' => User::ROLE_1_VALIDEUR_POLE,
            'deleted' => null,
        ], ['id' => 2]);
        $connection->insert('roles', [
            'id' => User::ROLE_1_VALIDEUR_POLE,
            'base' => 1,
            'code' => 'val_pole',
            'name' => 'Valideur de pôle',
            'sort' => 'Valideur de pôle',
            'deleted' => null,
            'created' => $now,
            'modified' => $now,
        ]);
        $connection->insert('user_departments', [
            'id' => 2,
            'user_id' => 2,
            'department_id' => 1,
            'created' => $now,
            'modified' => $now,
        ]);
        $connection->insert('validation_workflow_runs', [
            'id' => 900_003,
            'applicationform_id' => 1,
            'started_by_user_id' => 1,
            'state' => 'en_attente',
            'started_at' => $now,
            'created' => $now,
            'modified' => $now,
        ]);
        $connection->update('applicationvalidationsteps', [
            'validation_workflow_run_id' => 900_003,
            'role_id' => User::ROLE_1_VALIDEUR_POLE,
            'state' => 'en_attente',
            'deleted' => null,
        ], ['id' => 1]);
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

    private function grantPermission(int $roleId, string $resource, string $action, string $scope): void
    {
        $connection = ConnectionManager::get('test');
        if (!$this->getTableLocator()->get('Roles')->exists(['id' => $roleId])) {
            $connection->insert('roles', [
                'id' => $roleId,
                'base' => 0,
                'code' => 'TEST_ROLE_' . $roleId,
                'name' => 'Rôle de test ' . $roleId,
                'sort' => (string)$roleId,
                'deleted' => null,
                'created' => '2026-11-19 10:00:00',
                'modified' => '2026-11-19 10:00:00',
            ]);
        }
        $permission = $this->getTableLocator()->get('Permissions')->find()
            ->where(['resource' => $resource, 'action' => $action])
            ->firstOrFail();
        $this->getTableLocator()->get('RolePermissions')->saveOrFail(
            $this->getTableLocator()->get('RolePermissions')->newEntity([
                'role_id' => $roleId,
                'permission_id' => $permission->get('id'),
                'scope_type' => $scope,
            ]),
        );
    }
}

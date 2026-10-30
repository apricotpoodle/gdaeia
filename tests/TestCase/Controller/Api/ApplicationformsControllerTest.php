<?php
declare(strict_types=1);

namespace App\Test\TestCase\Controller\Api;

use App\Model\Entity\Period;
use App\Model\Entity\User;
use App\Service\Workflow\ApplicationformValidationWorkflow;
use Cake\Datasource\ConnectionManager;
use Cake\ORM\TableRegistry;
use Cake\TestSuite\IntegrationTestTrait;
use Cake\TestSuite\TestCase;

/**
 * @link \App\Controller\Api\ApplicationformsController
 */
class ApplicationformsControllerTest extends TestCase
{
    use IntegrationTestTrait;

    private const WORKFLOW_TEST_ID = 900_001;
    private const RESET_TEST_RUN_ID = 900_002;

    protected array $fixtures = [
        'app.FieldDefinitions',
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
        'app.Validationsequences',
        'app.Validationstatuses',
        'app.ValidationWorkflowRuns',
        'app.Applicationvalidationsteps',
        'app.Validations',
        'app.Comments',
        'app.WorkflowSettings',
        'app.ValidationCommentTemplates',
    ];

    public function testLApiDesDemandesSignaleUnePerteDeSessionEnJson(): void
    {
        $this->get('/api/applicationforms.json');

        $this->assertResponseCode(401);
        $this->assertHeaderContains('Content-Type', 'application/json');
        $this->assertResponseContains('Votre session a expiré. Veuillez vous reconnecter.');
    }

    public function testLApiDesDemandesRetourneDuJsonPourUnOperateurConnecte(): void
    {
        $this->session(['Auth' => new User(['id' => 1, 'issuperuser' => true, 'role_id' => 1])]);
        $this->get('/api/applicationforms.json');

        $this->assertResponseOk();
        $this->assertHeaderContains('Content-Type', 'application/json');
    }

    public function testLApiDesDemandesExposeLesReferentielsNecessairesAuxColonnes(): void
    {
        ConnectionManager::get('test')->update('applicationforms', ['deleted' => null], ['id' => 1]);
        $this->session(['Auth' => new User(['id' => 1, 'issuperuser' => true, 'role_id' => 1])]);
        $this->get('/api/applicationforms.json');

        $this->assertResponseOk();
        $body = json_decode((string)$this->_response->getBody(), true, 512, JSON_THROW_ON_ERROR);
        $row = $body['data'][0] ?? [];

        $this->assertArrayHasKey('budgetfeature', $row);
        $this->assertArrayHasKey('period', $row);
        $this->assertArrayHasKey('final_argument', $row);
        $this->assertArrayHasKey('working_days', $row);
        $this->assertArrayHasKey('begin_at', $row);
        $this->assertArrayHasKey('end_at', $row);
    }

    public function testLApiCalculeLeSalaireDeBaseManquantPourLaGrille(): void
    {
        ConnectionManager::get('test')->update('periods', ['code' => Period::CODE_EUR_PER_MONTH], ['id' => 1]);
        ConnectionManager::get('test')->update('applicationforms', [
            'deleted' => null,
            'grossremuneration' => '2450.75',
            'period_id' => 1,
            'salary_base' => null,
        ], ['id' => 1]);
        $this->session(['Auth' => new User(['id' => 1, 'issuperuser' => true, 'role_id' => 1])]);
        $this->get('/api/applicationforms.json');

        $this->assertResponseOk();
        $body = json_decode((string)$this->_response->getBody(), true, 512, JSON_THROW_ON_ERROR);
        $this->assertSame('2450.7500', $body['data'][0]['salary_base']);
    }

    public function testLApiEstimeLesJoursJournaliersManquantsPourLaGrille(): void
    {
        ConnectionManager::get('test')->update('periods', ['code' => Period::CODE_EUR_PER_DAY], ['id' => 1]);
        ConnectionManager::get('test')->update('applicationforms', [
            'deleted' => null,
            'grossremuneration' => '100.00',
            'period_id' => 1,
            'working_days' => null,
            'begin_at' => '2026-10-01',
            'end_at' => '2026-10-10',
            'salary_base' => null,
        ], ['id' => 1]);
        $this->session(['Auth' => new User(['id' => 1, 'issuperuser' => true, 'role_id' => 1])]);
        $this->get('/api/applicationforms.json');

        $this->assertResponseOk();
        $body = json_decode((string)$this->_response->getBody(), true, 512, JSON_THROW_ON_ERROR);
        $this->assertSame('700.0000', $body['data'][0]['salary_base']);
    }

    public function testLApiRecalculeLaRemunerationJournaliereVide(): void
    {
        ConnectionManager::get('test')->update('periods', ['code' => Period::CODE_EUR_PER_DAY], ['id' => 1]);
        ConnectionManager::get('test')->update('applicationforms', [
            'deleted' => null,
            'grossremuneration' => '2.00',
            'period_id' => 1,
            'working_days' => 2,
            'salary_base' => null,
        ], ['id' => 1]);
        $this->session(['Auth' => new User(['id' => 1, 'issuperuser' => true, 'role_id' => 1])]);
        $this->get('/api/applicationforms.json');

        $this->assertResponseOk();
        $body = json_decode((string)$this->_response->getBody(), true, 512, JSON_THROW_ON_ERROR);
        $this->assertSame('4.0000', $body['data'][0]['salary_base']);
    }

    public function testLApiFiltreEtTrieLesDemandesSurLeStatutDeValidation(): void
    {
        $connection = ConnectionManager::get('test');
        $connection->update('applicationforms', ['deleted' => null], ['id' => 1]);
        foreach ([2, 3, 4, 5] as $id) {
            $connection->insert('applicationforms', [
                'id' => $id,
                'department_id' => 1,
                'user_id' => 1,
                'cgr' => 'CGR ' . $id,
                'contracttype_id' => 1,
                'hiringreason_id' => 1,
                'reasonforreplacement' => 'Remplacement',
                'budgetfeature_id' => 1,
                'jobtitle' => 'Poste ' . $id,
                'professionalcategory_id' => 1,
                'worktime_id' => 1,
                'workingtimedistribution' => 'Temps plein',
                'grossremuneration' => 1.5,
                'period_id' => 1,
                'qualification' => 'Qualification',
                'begin_at' => '2026-07-03',
                'end_at' => '2026-07-03',
                'applicantname' => 'Demandeur ' . $id,
                'yesno_id' => 1,
                'deleted' => null,
                'created' => '2026-07-03 09:00:0' . $id,
                'modified' => '2026-07-03 09:00:0' . $id,
            ]);
        }
        $states = [2 => 'en_attente', 3 => 'acceptee', 4 => 'refusee', 5 => 'annulee'];
        foreach ($states as $applicationformId => $state) {
            $connection->insert('validation_workflow_runs', [
                'id' => 910_000 + $applicationformId,
                'applicationform_id' => $applicationformId,
                'started_by_user_id' => 1,
                'state' => $state,
                'started_at' => '2026-10-01 09:00:00',
                'finished_at' => $state === 'en_attente' ? null : '2026-10-02 09:00:00',
                'created' => '2026-10-01 09:00:00',
                'modified' => '2026-10-02 09:00:00',
            ]);
        }
        $this->session(['Auth' => new User(['id' => 1, 'issuperuser' => true, 'role_id' => 1])]);

        $expectedIdsByFilter = [
            '' => [1, 2, 3, 4, 5],
            '1' => [1],
            '2' => [2],
            '4' => [3],
            '5' => [4],
            '6' => [5],
        ];
        foreach ($expectedIdsByFilter as $value => $expectedIds) {
            $query = http_build_query([
                'filters' => [[
                    'field' => 'validation_status',
                    'type' => '=',
                    'value' => $value,
                ]],
            ]);
            $this->get('/api/applicationforms.json?' . $query);

            $this->assertResponseOk();
            $body = json_decode((string)$this->_response->getBody(), true, 512, JSON_THROW_ON_ERROR);
            $this->assertSame($expectedIds, array_column($body['data'], 'id'), 'Filtre ' . ($value ?: 'Toutes'));
        }

        $query = http_build_query([
            'sorters' => [[
                'field' => 'validation_status',
                'dir' => 'desc',
            ]],
        ]);
        $this->get('/api/applicationforms.json?' . $query);

        $this->assertResponseOk();
        $body = json_decode((string)$this->_response->getBody(), true, 512, JSON_THROW_ON_ERROR);
        $this->assertSame([4, 3, 2, 1, 5], array_column($body['data'], 'id'));

        $query = http_build_query([
            'sorters' => [[
                'field' => 'validation_status',
                'dir' => 'asc',
            ]],
        ]);
        $this->get('/api/applicationforms.json?' . $query);

        $this->assertResponseOk();
        $body = json_decode((string)$this->_response->getBody(), true, 512, JSON_THROW_ON_ERROR);
        $this->assertSame([1, 5, 2, 3, 4], array_column($body['data'], 'id'));
    }

    public function testLApiRefuseUnePlageDeDatesInverseeAvecUneErreurJson(): void
    {
        $this->session(['Auth' => new User(['id' => 1, 'issuperuser' => true, 'role_id' => 1])]);
        $query = http_build_query([
            'filters' => [[
                'field' => 'begin_at',
                'type' => '=',
                'value' => ['start' => '2024-10-01', 'end' => '2023-01-01'],
            ]],
        ]);

        $this->get('/api/applicationforms.json?' . $query);

        $this->assertResponseCode(422);
        $this->assertHeaderContains('Content-Type', 'application/json');
        $body = json_decode((string)$this->_response->getBody(), true);
        $this->assertSame([
            'success' => false,
            'message' => 'La date de début doit être antérieure ou égale à la date de fin.',
            'details' => [],
            'errors' => ['La date de début doit être antérieure ou égale à la date de fin.'],
        ], $body);
    }

    public function testUnOperateurSansDepartementNeVoitAucuneDemande(): void
    {
        $this->session(['Auth' => new User(['id' => 2, 'issuperuser' => false, 'role_id' => 2])]);
        $this->get('/api/applicationforms.json');

        $this->assertResponseOk();
        $this->assertResponseRegExp('/"data"\\s*:\\s*\\[\\]/');
    }

    public function testLeLienDeCreationEstRenduParLaCommandeDeDomaine(): void
    {
        $this->session(['Auth' => new User(['id' => 2, 'issuperuser' => false, 'role_id' => 2])]);

        $this->get('/');

        $this->assertResponseOk();
        $this->assertResponseContains('Nouvelle demande');
    }

    public function testLeFormulaireWebAfficheLeChampInvalideDeLaDemande(): void
    {
        $this->session(['Auth' => new User(['id' => 1, 'issuperuser' => true, 'role_id' => 1])]);
        $this->enableCsrfToken();

        $this->post('/applicationforms/edit/1', ['jobtitle' => '']);

        $this->assertResponseOk();
        $this->assertResponseContains('Champ « Intitulé du poste » :');
    }

    public function testLApiPresenteLesErreursDeValidationDeLaDemande(): void
    {
        $this->session(['Auth' => new User(['id' => 1, 'issuperuser' => true, 'role_id' => 1])]);
        $this->enableCsrfToken();

        $this->post('/api/applicationforms/edit/1.json', ['jobtitle' => '']);

        $this->assertResponseCode(422);
        $this->assertResponseContains('"success":false');
        $this->assertResponseContains('Champ « Intitulé du poste » :');
        $this->assertResponseContains('"field":"jobtitle","label":"Intitulé du poste","reason":');
    }

    public function testLEditionIgnoreLesValeursNullesEncodeesZeroPourAssistantEtManager(): void
    {
        $this->session(['Auth' => new User(['id' => 1, 'issuperuser' => true, 'role_id' => 1])]);
        $this->enableCsrfToken();

        $this->post('/api/applicationforms/edit/1.json', [
            'assistant_id' => '0',
            'manager_id' => '0',
            'jobtitle' => 'Poste conservé',
        ]);

        $this->assertResponseOk();
    }

    public function testLaCreationApiRetourneLesErreursExploitablesParLeFormulaire(): void
    {
        $this->session(['Auth' => new User(['id' => 1, 'issuperuser' => true, 'role_id' => 1])]);
        $this->enableCsrfToken();

        $this->post('/api/applicationforms/add.json', ['jobtitle' => '']);

        $this->assertResponseCode(422);
        $this->assertResponseContains('"success":false');
        $this->assertResponseContains('"message":"Champ «');
        $this->assertResponseContains('"field":"jobtitle","label":"Intitulé du poste","reason":');
    }

    public function testLApiCalculeLeSalaireDeBaseAvecUneVirgule(): void
    {
        $this->session(['Auth' => new User(['id' => 1, 'issuperuser' => true, 'role_id' => 1])]);
        $this->enableCsrfToken();

        $this->post('/api/applicationforms/calculate-salary.json', [
            'period_code' => Period::CODE_EUR_PER_DAY,
            'grossremuneration' => '125,50',
            'working_days' => 4,
        ]);

        $this->assertResponseOk();
        $this->assertSame([
            'success' => true,
            'message' => 'Salaire de base calculé.',
            'salary_base' => '502.0000',
        ], json_decode((string)$this->_response->getBody(), true));
    }

    public function testLEditionApiRefuseUnNombreDeJoursSuperieurALaPeriode(): void
    {
        $this->session(['Auth' => new User(['id' => 1, 'issuperuser' => true, 'role_id' => 1])]);
        $this->enableCsrfToken();

        $this->post('/api/applicationforms/edit/1.json', [
            'begin_at' => '2026-10-05',
            'end_at' => '2026-10-07',
            'working_days' => 4,
        ]);

        $this->assertResponseCode(422);
        $this->assertResponseContains('"success":false');
        $this->assertResponseContains('Le nombre de jours travaillés ne peut pas dépasser la durée de la période.');
    }

    public function testLEditionApiAccepteUnNombreDeJoursDansLaPeriode(): void
    {
        $this->session(['Auth' => new User(['id' => 1, 'issuperuser' => true, 'role_id' => 1])]);
        $this->enableCsrfToken();

        $this->post('/api/applicationforms/edit/1.json', [
            'begin_at' => '2026-10-05',
            'end_at' => '2026-10-07',
            'working_days' => 3,
        ]);

        $this->assertResponseOk();
    }

    public function testUnAdministrateurArchiveUnePigeOuUneFacture(): void
    {
        ConnectionManager::get('test')->update('contracttypes', ['code' => 'PIGE'], ['id' => 1]);
        ConnectionManager::get('test')->update('applicationforms', ['archived' => null], ['id' => 1]);
        $this->session(['Auth' => new User(['id' => 1, 'issuperuser' => false, 'role_id' => User::ROLE_ADMIN])]);
        $this->enableCsrfToken();

        $this->post('/api/applicationforms/1/archive.json');

        $this->assertResponseOk();
        $this->assertResponseContains('"success":true');
        $archived = ConnectionManager::get('test')->execute(
            'SELECT archived FROM applicationforms WHERE id = 1',
        )->fetchColumn(0);
        $this->assertNotNull($archived);
    }

    /** Vérifie qu'une duplication crée une DAE brouillon sans réutiliser la source. */
    public function testLaDuplicationCreeUneNouvelleDaePourLeDemandeurCourant(): void
    {
        $this->session(['Auth' => new User(['id' => 1, 'issuperuser' => true, 'role_id' => 1])]);
        $this->enableCsrfToken();

        $this->post('/api/applicationforms/1/duplicate.json');

        $this->assertResponseOk();
        $this->assertResponseContains('"success":true');
        $details = json_decode((string)$this->_response->getBody(), true)['details'];
        $this->assertNotSame(1, $details['id']);

        $connection = ConnectionManager::get('test');
        $copy = $connection->execute(
            'SELECT user_id, jobtitle, deleted, archived FROM applicationforms WHERE id = ' . (int)$details['id'],
        )->fetch('assoc');
        $source = $connection->execute('SELECT jobtitle FROM applicationforms WHERE id = 1')->fetch('assoc');
        $this->assertSame('1', (string)$copy['user_id']);
        $this->assertSame($source['jobtitle'], $copy['jobtitle']);
        $this->assertNull($copy['deleted']);
        $this->assertNull($copy['archived']);
    }

    /** Vérifie que l'API refuse la duplication d'une DAE hors périmètre. */
    public function testLaDuplicationEstRefuseeHorsDuPerimetreVisible(): void
    {
        $this->session(['Auth' => new User(['id' => 2, 'issuperuser' => false, 'role_id' => 2])]);
        $this->enableCsrfToken();

        $this->post('/api/applicationforms/1/duplicate.json');

        $this->assertResponseCode(403);
    }

    /** Vérifie que le créateur peut lancer un cycle configuré. */
    public function testLeCreateurPeutLancerLeCycleDeValidation(): void
    {
        $this->configureStartableWorkflow();
        $this->session(['Auth' => new User(['id' => 1, 'issuperuser' => true, 'role_id' => 1])]);
        $this->enableCsrfToken();

        $this->post('/api/applicationforms/1/validation/start.json');

        $this->assertResponseOk();
        $this->assertResponseContains('"success":true');
        $this->assertResponseContains('"message":"Le cycle de validation est lanc');
        $this->assertSame(1, (int)ConnectionManager::get('test')->execute(
            "SELECT COUNT(*) FROM validation_workflow_runs WHERE applicationform_id = 1 AND state = 'en_attente'",
        )->fetchColumn(0));
    }

    /** Vérifie qu'un administrateur ayant accès au département peut lancer le cycle. */
    public function testUnAdministrateurVisiblePeutLancerLeCycleDeValidation(): void
    {
        $this->configureStartableWorkflow();
        $connection = ConnectionManager::get('test');
        $connection->update('users', ['role_id' => User::ROLE_ADMIN], ['id' => 2]);
        $connection->insert('user_departments', [
            'id' => 2,
            'user_id' => 2,
            'department_id' => 1,
            'created' => '2026-09-18 10:00:00',
            'modified' => '2026-09-18 10:00:00',
        ]);
        $this->session(['Auth' => new User(['id' => 2, 'issuperuser' => false, 'role_id' => User::ROLE_ADMIN])]);
        $this->enableCsrfToken();

        $this->post('/api/applicationforms/1/validation/start.json');

        $this->assertResponseOk();
        $this->assertResponseContains('"success":true');
    }

    /** Vérifie que l'API refuse le lancement à un opérateur hors de son périmètre. */
    public function testLeLancementEstRefuseAUnOperateurHorsDuPerimetreVisible(): void
    {
        $this->session(['Auth' => new User(['id' => 2, 'issuperuser' => false, 'role_id' => 2])]);
        $this->enableCsrfToken();

        $this->post('/api/applicationforms/1/validation/start.json');

        $this->assertResponseCode(403);
    }

    /** Vérifie le contrat JSON des préconditions qui empêchent le lancement. */
    public function testLeLancementRetourneLesErreursDePreconditionAuFormatApi(): void
    {
        $connection = ConnectionManager::get('test');
        $connection->update('applicationforms', ['deleted' => null], ['id' => 1]);
        $this->session(['Auth' => new User(['id' => 1, 'issuperuser' => true, 'role_id' => 1])]);
        $this->enableCsrfToken();

        $this->post('/api/applicationforms/1/validation/start.json');

        $this->assertResponseCode(422);
        $this->assertHeaderContains('Content-Type', 'application/json');
        $this->assertResponseContains('"success":false');
        $this->assertResponseContains('"errors":["Aucune s');
    }

    /** Vérifie que le validateur peut enregistrer son acceptation par l'API. */
    public function testUnValidateurPeutAccepterLEtapeQuiLuiEstAssignee(): void
    {
        $this->configureVotableWorkflow();
        $stepId = $this->startVotableWorkflow();

        $this->session(['Auth' => new User(['id' => 2, 'issuperuser' => false, 'role_id' => 2])]);
        $this->enableCsrfToken();
        $this->post('/api/applicationforms/1/validation/vote.json', [
            'step_id' => $stepId,
            'decision' => 'accepter',
            'comment' => 'Accord.',
        ]);

        $this->assertResponseOk();
        $this->assertResponseContains('"success":true');
        $this->assertResponseContains('"final":true');
    }

    /** Vérifie qu'un Admin peut outrepasser une étape et obtient un commentaire d'audit. */
    public function testUnAdministrateurPeutOutrepasserUneEtapeAvecUnCommentaireAutomatique(): void
    {
        $this->configureVotableWorkflow();
        $stepId = $this->startVotableWorkflow();

        $this->session(['Auth' => new User([
            'id' => 1,
            'email' => 'admin@example.test',
            'issuperuser' => true,
            'role_id' => User::ROLE_ADMIN,
        ])]);
        $this->enableCsrfToken();
        $this->post('/api/applicationforms/1/validation/vote.json', [
            'step_id' => $stepId,
            'decision' => 'accepter',
            'comment' => '',
            'override' => true,
        ]);

        $this->assertResponseOk();
        $this->assertResponseContains('"success":true');
        $validation = ConnectionManager::get('test')->execute(
            'SELECT role_id, is_proxy, obs FROM validations WHERE applicationvalidationstep_id = ' . $stepId,
        )->fetch('assoc');
        $this->assertSame(2, (int)$validation['role_id']);
        $this->assertSame(1, (int)$validation['is_proxy']);
        $this->assertMatchesRegularExpression(
            '/^outrepassé par admin@example\.test à \d{2}\/\d{2}\/\d{4} \d{2}:\d{2}$/',
            (string)$validation['obs'],
        );
    }

    /** Vérifie qu'une étape à venir outrepassée n'active pas la suite du cycle. */
    public function testLeDepassementDUneEtapeBloqueeNeDeclenchePasUneChaine(): void
    {
        $this->configureVisibilityWorkflow();
        $connection = ConnectionManager::get('test');
        $connection->update('applicationvalidationsteps', ['state' => 'a_venir'], ['id' => self::WORKFLOW_TEST_ID + 1]);

        $this->session(['Auth' => new User([
            'id' => 1,
            'email' => 'admin@example.test',
            'issuperuser' => true,
            'role_id' => User::ROLE_ADMIN,
        ])]);
        $this->enableCsrfToken();
        $this->post('/api/applicationforms/1/validation/vote.json', [
            'step_id' => self::WORKFLOW_TEST_ID + 1,
            'decision' => 'accepter',
            'override' => true,
        ]);

        $this->assertResponseOk();
        $this->assertSame('en_attente', $connection->execute(
            'SELECT state FROM validation_workflow_runs WHERE id = ' . self::WORKFLOW_TEST_ID,
        )->fetchColumn(0));
        $this->assertSame('en_attente', $connection->execute(
            'SELECT state FROM applicationvalidationsteps WHERE id = ' . self::WORKFLOW_TEST_ID,
        )->fetchColumn(0));
        $this->assertSame('acceptee', $connection->execute(
            'SELECT state FROM applicationvalidationsteps WHERE id = ' . (self::WORKFLOW_TEST_ID + 1),
        )->fetchColumn(0));
    }

    /** Vérifie que l'API refuse un refus dépourvu de commentaire. */
    public function testLApiExigeUnCommentairePourUnRefus(): void
    {
        $this->configureVotableWorkflow();
        $stepId = $this->startVotableWorkflow();

        $this->session(['Auth' => new User(['id' => 2, 'issuperuser' => false, 'role_id' => 2])]);
        $this->enableCsrfToken();
        $this->post('/api/applicationforms/1/validation/vote.json', [
            'step_id' => $stepId,
            'decision' => 'refuser',
            'comment' => '',
        ]);

        $this->assertResponseCode(422);
        $this->assertResponseContains('"success":false');
        $this->assertResponseContains('"errors":["Un commentaire est obligatoire');
    }

    /** Un validateur ordinaire ne voit que l'étape correspondant à son rôle. */
    public function testLEtatDeValidationEstLimiteALEtapeDuValidateur(): void
    {
        $this->configureVisibilityWorkflow();
        $this->session(['Auth' => new User(['id' => 2, 'issuperuser' => false, 'role_id' => 2])]);

        $this->get('/api/applicationforms/1/validation.json');

        $this->assertResponseOk();
        $this->assertResponseContains('Validateur de test');
        $this->assertResponseNotContains('Second validateur');
    }

    /** Un super-utilisateur conserve la vision de toutes les étapes du cycle. */
    public function testLeSuperUtilisateurVoitToutesLesEtapesDeValidation(): void
    {
        $this->configureVisibilityWorkflow();
        $this->session(['Auth' => new User(['id' => 1, 'issuperuser' => true, 'role_id' => 1])]);

        $this->get('/api/applicationforms/1/validation.json');

        $this->assertResponseOk();
        $this->assertResponseContains('Validateur de test');
        $this->assertResponseContains('Second validateur');
    }

    /** Un administrateur simple ne voit pas les étapes qui ne sont pas encore activées. */
    public function testLAdministrateurSimpleNeVoitPasLesEtapesAVenir(): void
    {
        $this->configureVisibilityWorkflow();
        ConnectionManager::get('test')->update(
            'applicationvalidationsteps',
            ['state' => 'a_venir'],
            ['id' => self::WORKFLOW_TEST_ID + 1],
        );
        $this->session(['Auth' => new User(['id' => 1, 'issuperuser' => false, 'role_id' => User::ROLE_ADMIN])]);

        $this->get('/api/applicationforms/1/validation.json');

        $this->assertResponseOk();
        $this->assertResponseContains('Validateur de test');
        $this->assertResponseNotContains('Second validateur');
    }

    /** Vérifie qu'un rôle du cycle peut modifier la demande et laisse une trace d'audit. */
    public function testUnValidateurDuCyclePeutModifierLaDemandeAvecUnCommentaireDAudit(): void
    {
        $this->configureActiveWorkflowForEdit();
        $this->session(['Auth' => new User(['id' => 2, 'issuperuser' => false, 'role_id' => 2])]);
        $this->enableCsrfToken();

        $this->put('/api/applicationforms/1.json', ['jobtitle' => 'Poste modifié pendant le cycle']);

        $this->assertResponseOk();
        $this->assertResponseContains('"success":true');
        $connection = ConnectionManager::get('test');
        $this->assertSame('Poste modifié pendant le cycle', $connection->execute(
            'SELECT jobtitle FROM applicationforms WHERE id = 1',
        )->fetchColumn(0));
        $this->assertSame(1, (int)$connection->execute(
            "SELECT COUNT(*) FROM comments WHERE foreign_key = 1 AND type = 'WORKFLOW_EDIT_AUDIT' AND user_id = 2",
        )->fetchColumn(0));
    }

    /** Vérifie que le département reste immuable pendant un cycle actif. */
    public function testUnValidateurDuCycleNePeutPasModifierLeDepartement(): void
    {
        $this->configureActiveWorkflowForEdit();
        $this->session(['Auth' => new User(['id' => 2, 'issuperuser' => false, 'role_id' => 2])]);
        $this->enableCsrfToken();

        $this->put('/api/applicationforms/1.json', ['department_id' => 2]);

        $this->assertResponseCode(422);
        $this->assertResponseContains('"errors":["Le d');
    }

    /** Vérifie que le demandeur reste verrouillé dès la création du cycle. */
    public function testLeDemandeurNePeutPasModifierUneDemandeAyantUnCycle(): void
    {
        $this->configureActiveWorkflowForEdit();
        $this->session(['Auth' => new User(['id' => 1, 'issuperuser' => false, 'role_id' => User::ROLE_DEMANDEUR])]);
        $this->enableCsrfToken();

        $this->put('/api/applicationforms/1.json', ['jobtitle' => 'Modification interdite']);

        $this->assertResponseCode(403);
    }

    /** Vérifie qu'un Admin visible ne bénéficie d'aucun droit d'édition général. */
    public function testUnAdministrateurVisibleHorsEtapeActiveNePeutPasModifier(): void
    {
        $this->configureActiveWorkflowForEdit();
        ConnectionManager::get('test')->update('users', ['role_id' => User::ROLE_ADMIN], ['id' => 2]);
        $this->session(['Auth' => new User(['id' => 2, 'issuperuser' => false, 'role_id' => User::ROLE_ADMIN])]);
        $this->enableCsrfToken();

        $this->put('/api/applicationforms/1.json', ['jobtitle' => 'Modification interdite']);

        $this->assertResponseCode(403);
    }

    /** Vérifie qu'un Super Administrateur supprime atomiquement l'AF et son cycle. */
    public function testLeSuperAdministrateurSupprimeLaDemandeEtSonCycle(): void
    {
        $this->configureActiveWorkflowForEdit();
        $connection = ConnectionManager::get('test');
        $connection->insert('validations', [
            'id' => self::WORKFLOW_TEST_ID,
            'applicationform_id' => 1,
            'applicationvalidationstep_id' => self::WORKFLOW_TEST_ID,
            'user_id' => 2,
            'role_id' => 2,
            'validationstatus_id' => null,
            'validated' => '2026-09-18 10:00:00',
            'is_proxy' => 0,
            'created' => '2026-09-18 10:00:00',
            'modified' => '2026-09-18 10:00:00',
        ]);
        $this->session(['Auth' => new User(['id' => 1, 'issuperuser' => true, 'role_id' => User::ROLE_ADMIN])]);
        $this->enableCsrfToken();

        $this->delete('/api/applicationforms/1.json');

        $this->assertResponseOk();
        $this->assertResponseContains('"success":true');
        $this->assertSame(0, (int)$connection->execute('SELECT COUNT(*) FROM applicationforms WHERE id = 1')->fetchColumn(0));
        $this->assertSame(0, (int)$connection->execute('SELECT COUNT(*) FROM validation_workflow_runs WHERE applicationform_id = 1')->fetchColumn(0));
        $this->assertSame(0, (int)$connection->execute('SELECT COUNT(*) FROM applicationvalidationsteps WHERE applicationform_id = 1')->fetchColumn(0));
        $this->assertSame(0, (int)$connection->execute('SELECT COUNT(*) FROM validations WHERE applicationform_id = 1')->fetchColumn(0));
        $this->assertSame(0, (int)$connection->execute('SELECT COUNT(*) FROM applicationvalidationsteps WHERE id = ' . self::WORKFLOW_TEST_ID)->fetchColumn(0));
        $this->assertSame(0, (int)$connection->execute('SELECT COUNT(*) FROM validations WHERE id = ' . self::WORKFLOW_TEST_ID)->fetchColumn(0));
    }

    public function testUnAdministrateurPeutRemettreAZeroUnCycleExistant(): void
    {
        $connection = ConnectionManager::get('test');
        $now = '2026-09-17 12:00:00';
        $connection->insert('validation_workflow_runs', [
            'id' => self::RESET_TEST_RUN_ID,
            'applicationform_id' => 1,
            'started_by_user_id' => 1,
            'state' => 'en_attente',
            'started_at' => $now,
            'created' => $now,
            'modified' => $now,
        ]);

        try {
            $this->session(['Auth' => new User(['id' => 1, 'issuperuser' => true, 'role_id' => 1])]);
            $this->enableCsrfToken();
            $this->post('/api/applicationforms/1/validation/reset.json');

            $this->assertResponseOk();
            $this->assertResponseContains('"success":true');
            $this->assertSame(0, (int)$connection->execute('SELECT COUNT(*) FROM validation_workflow_runs WHERE id = ' . self::RESET_TEST_RUN_ID)->fetchColumn(0));
        } finally {
            $connection->delete('validation_workflow_runs', ['id' => self::RESET_TEST_RUN_ID]);
        }
    }

    /** Vérifie qu'un opérateur ordinaire ne peut pas déclencher une relance. */
    public function testUnOperateurNePeutPasRelancerUnCycleBloque(): void
    {
        $this->session(['Auth' => new User(['id' => 2, 'issuperuser' => false, 'role_id' => User::ROLE_DEMANDEUR])]);
        $this->enableCsrfToken();

        $this->post('/api/applicationforms/1/validation/remind.json');

        $this->assertResponseCode(403);
    }

    /** Vérifie que l'administrateur relance les validateurs du cycle bloqué. */
    public function testUnAdministrateurPeutRelancerLesValidateursDUnCycleBloque(): void
    {
        $this->configureVotableWorkflow();
        $stepId = $this->startVotableWorkflow();
        $connection = ConnectionManager::get('test');
        $connection->update(
            'applicationvalidationsteps',
            ['activated_at' => '2026-09-10 10:00:00'],
            ['id' => $stepId],
        );
        $this->session(['Auth' => new User(['id' => 1, 'issuperuser' => true, 'role_id' => User::ROLE_ADMIN])]);
        $this->enableCsrfToken();

        $this->post('/api/applicationforms/1/validation/remind.json');

        $this->assertResponseOk();
        $this->assertResponseContains('"recipients":1');
    }

    /** Prépare une demande, une séquence et un validateur actifs pour le lancement. */
    private function configureStartableWorkflow(): void
    {
        $connection = ConnectionManager::get('test');
        $connection->update('applicationforms', ['deleted' => null], ['id' => 1]);
        $connection->update('users', ['deleted' => null], ['id' => 1]);
        $connection->update('roles', ['deleted' => null, 'code' => 'adm'], ['id' => 1]);
        $connection->update('validationsequences', [
            'deleted' => null,
            'role_id' => 1,
            'sequence' => 1,
            'reminder_delay_hours' => 24,
        ], ['id' => 1]);
    }

    /** Prépare une étape de validation attribuée à un validateur non administrateur. */
    private function configureVotableWorkflow(): void
    {
        $this->configureStartableWorkflow();
        $connection = ConnectionManager::get('test');
        $now = '2026-09-18 10:00:00';
        $connection->insert('roles', [
            'id' => 2,
            'base' => 0,
            'code' => 'val',
            'name' => 'Validateur de test',
            'sort' => 'Validateur de test',
            'deleted' => null,
            'created' => $now,
            'modified' => $now,
        ]);
        $connection->update('users', ['role_id' => 2, 'deleted' => null], ['id' => 2]);
        $connection->insert('user_departments', [
            'id' => 2,
            'user_id' => 2,
            'department_id' => 1,
            'created' => $now,
            'modified' => $now,
        ]);
        foreach ([3, 5] as $id) {
            $connection->insert('validationstatuses', [
                'id' => $id,
                'code' => 'test-' . $id,
                'name' => 'Statut de test ' . $id,
                'deleted' => null,
                'created' => $now,
                'modified' => $now,
            ]);
        }
        $connection->update('validationsequences', ['role_id' => 2], ['id' => 1]);
    }

    /** Prépare deux étapes afin de vérifier le filtrage de l'état par utilisateur. */
    private function configureVisibilityWorkflow(): void
    {
        $this->configureVotableWorkflow();
        $connection = ConnectionManager::get('test');
        $now = '2026-09-18 10:00:00';
        $connection->insert('roles', [
            'id' => 3,
            'base' => 0,
            'code' => 'val-2',
            'name' => 'Second validateur',
            'sort' => 'Second validateur',
            'deleted' => null,
            'created' => $now,
            'modified' => $now,
        ]);
        $connection->insert('users', [
            'id' => 3,
            'username' => 'second-validateur',
            'email' => 'second-validateur@example.test',
            'password' => 'mot-de-passe-de-test',
            'firstname' => 'Second',
            'lastname' => 'Validateur',
            'token' => null,
            'issuperuser' => 0,
            'role_id' => 3,
            'token_expires' => null,
            'deleted' => null,
            'created' => $now,
            'modified' => $now,
        ]);
        $connection->insert('user_departments', [
            'id' => 3,
            'user_id' => 3,
            'department_id' => 1,
            'created' => $now,
            'modified' => $now,
        ]);
        $connection->insert('validationsequences', [
            'id' => 2,
            'department_id' => 1,
            'role_id' => 3,
            'sequence' => 2,
            'reminder_delay_hours' => 12,
            'deleted' => null,
            'created' => $now,
            'modified' => $now,
        ]);
        $connection->insert('validation_workflow_runs', [
            'id' => self::WORKFLOW_TEST_ID,
            'applicationform_id' => 1,
            'started_by_user_id' => 1,
            'state' => 'en_attente',
            'started_at' => $now,
            'created' => $now,
            'modified' => $now,
        ]);
        foreach (
            [
                [self::WORKFLOW_TEST_ID, 1, 2],
                [self::WORKFLOW_TEST_ID + 1, 2, 3],
            ] as [$id, $sequenceId, $roleId]
        ) {
            $connection->insert('applicationvalidationsteps', [
                'id' => $id,
                'applicationform_id' => 1,
                'validation_workflow_run_id' => self::WORKFLOW_TEST_ID,
                'validationsequence_id' => $sequenceId,
                'role_id' => $roleId,
                'sequence_number' => $roleId === 2 ? 1 : 2,
                'state' => 'en_attente',
                'reminder_count' => 0,
            ]);
        }
    }

    /** Lance directement le cycle préparé afin d'isoler la requête HTTP de vote. */
    private function startVotableWorkflow(): int
    {
        /** @var \App\Model\Entity\Applicationform $applicationform */
        $applicationform = TableRegistry::getTableLocator()->get('Applicationforms')->get(1);
        /** @var \App\Model\Entity\User $actor */
        $actor = TableRegistry::getTableLocator()->get('Users')->get(1);
        $result = (new ApplicationformValidationWorkflow())->start($applicationform, $actor);

        return (int)TableRegistry::getTableLocator()->get('Applicationvalidationsteps')->find()
            ->select(['id'])
            ->where(['validation_workflow_run_id' => $result['run']->id, 'state' => 'en_attente'])
            ->firstOrFail()
            ->id;
    }

    /** Prépare une exécution active pour vérifier l'édition pendant le cycle. */
    private function configureActiveWorkflowForEdit(): void
    {
        $this->configureVotableWorkflow();
        $connection = ConnectionManager::get('test');
        $now = '2026-09-18 10:00:00';
        $connection->insert('validation_workflow_runs', [
            'id' => self::WORKFLOW_TEST_ID,
            'applicationform_id' => 1,
            'started_by_user_id' => 1,
            'state' => 'en_attente',
            'started_at' => $now,
            'created' => $now,
            'modified' => $now,
        ]);
        $connection->insert('applicationvalidationsteps', [
            'id' => self::WORKFLOW_TEST_ID,
            'applicationform_id' => 1,
            'validation_workflow_run_id' => self::WORKFLOW_TEST_ID,
            'validationsequence_id' => 1,
            'role_id' => 2,
            'sequence_number' => 1,
            'state' => 'en_attente',
            'reminder_count' => 0,
        ]);
    }
}

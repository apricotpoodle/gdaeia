<?php
declare(strict_types=1);

namespace App\Test\TestCase\Controller\Api;

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
        'app.Validationsequences',
        'app.Validationstatuses',
        'app.ValidationWorkflowRuns',
        'app.Applicationvalidationsteps',
        'app.Validations',
        'app.Comments',
    ];

    public function testLApiDesDemandesRedirigeUnVisiteurVersLaConnexion(): void
    {
        $this->get('/api/applicationforms.json');

        $this->assertRedirectContains('/users/login');
    }

    public function testLApiDesDemandesRetourneDuJsonPourUnOperateurConnecte(): void
    {
        $this->session(['Auth' => new User(['id' => 1, 'issuperuser' => true, 'role_id' => 1])]);
        $this->get('/api/applicationforms.json');

        $this->assertResponseOk();
        $this->assertHeaderContains('Content-Type', 'application/json');
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

    public function testUnAdministrateurPeutRemettreAZeroUnCycleExistant(): void
    {
        $connection = ConnectionManager::get('test');
        $now = '2026-09-17 12:00:00';
        $connection->insert('validation_workflow_runs', [
            'id' => 901,
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
            $this->assertSame(0, (int)$connection->execute('SELECT COUNT(*) FROM validation_workflow_runs WHERE id = 901')->fetchColumn(0));
        } finally {
            $connection->delete('validation_workflow_runs', ['id' => 901]);
        }
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
            'id' => 900,
            'applicationform_id' => 1,
            'started_by_user_id' => 1,
            'state' => 'en_attente',
            'started_at' => $now,
            'created' => $now,
            'modified' => $now,
        ]);
        $connection->insert('applicationvalidationsteps', [
            'id' => 900,
            'applicationform_id' => 1,
            'validation_workflow_run_id' => 900,
            'validationsequence_id' => 1,
            'role_id' => 2,
            'sequence_number' => 1,
            'state' => 'en_attente',
            'reminder_count' => 0,
        ]);
    }

    /** Nettoie les tables du workflow non couvertes par les fixtures HTTP. */
    protected function tearDown(): void
    {
        $connection = ConnectionManager::get('test');
        $connection->delete('validations', ['applicationform_id' => 1]);
        $connection->delete('applicationvalidationsteps', ['applicationform_id' => 1]);
        $connection->delete('validation_workflow_runs', ['applicationform_id' => 1]);

        parent::tearDown();
    }
}

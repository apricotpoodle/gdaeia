<?php
declare(strict_types=1);

namespace App\Test\TestCase\Controller\Api;

use App\Model\Entity\User;
use Cake\TestSuite\IntegrationTestTrait;
use Cake\TestSuite\TestCase;

/** Vérifie l’écran et l’API du paramétrage global du workflow. */
class WorkflowSettingsControllerTest extends TestCase
{
    use IntegrationTestTrait;

    /**
     * @var array<string>
     */
    protected array $fixtures = [
        'app.Users',
        'app.Roles',
        'app.Permissions',
        'app.PermissionScopes',
        'app.Role1Permissions',
        'app.FieldDefinitions',
        'app.FieldAuthorizations',
        'app.WorkflowSettings',
        'app.ValidationCommentTemplates',
    ];

    public function testLEcranEtLApiSontRefusesAUnOperateurNonAutorise(): void
    {
        $this->session(['Auth' => new User(['id' => 1, 'issuperuser' => false, 'role_id' => 1])]);

        $this->get('/workflow-settings');
        $this->assertResponseCode(403);

        $this->get('/api/workflow-settings/comment-templates.json');
        $this->assertResponseCode(403);
    }

    public function testUnSuperAdministrateurConfigureLeDelaiEtLesCommentairesDepuisLaNouvelleApi(): void
    {
        $this->session(['Auth' => new User(['id' => 1, 'issuperuser' => true, 'role_id' => 1])]);
        $this->enableCsrfToken();
        $this->configRequest(['headers' => ['Accept' => 'application/json']]);

        $this->post('/api/workflow-settings/default-due-hours.json', ['default_due_hours' => 48]);
        $this->assertResponseOk();
        $this->assertResponseContains('48');
        $settings = $this->getTableLocator()->get('WorkflowSettings');
        $this->assertSame('48', $settings->find()->where(['name' => 'validation.default_due_hours'])->firstOrFail()->value);
        $this->get('/api/workflow-settings/default-due-hours.json');
        $this->assertResponseOk();
        $this->assertResponseContains('48');

        $this->post('/api/workflow-settings/k-coefficient.json', ['k_coefficient' => '18,25']);
        $this->assertResponseOk();
        $this->assertResponseContains('18.25');
        $this->assertSame(
            '18.2500',
            $settings->find()->where(['name' => 'salary.k_coefficient'])->firstOrFail()->value,
        );

        $this->post('/api/workflow-settings/comment-templates/create.json', [
            'decision' => 'refuser', 'label' => 'Budget', 'content' => 'Budget insuffisant.', 'position' => 1, 'active' => true,
        ]);
        $this->assertResponseOk();
        $templates = $this->getTableLocator()->get('ValidationCommentTemplates');
        $template = $templates->find()->where(['label' => 'Budget'])->firstOrFail();

        $this->post('/api/workflow-settings/comment-templates/' . $template->id . '.json', [
            'decision' => 'refuser', 'label' => 'Budget', 'content' => 'Budget insuffisant après arbitrage.', 'position' => 2, 'active' => false,
        ]);
        $this->assertResponseOk();
        $this->assertFalse((bool)$templates->get($template->id)->active);

        $this->get('/api/workflow-settings/comment-templates.json?size=1&page=1&sorters[0][field]=position&sorters[0][dir]=desc');
        $this->assertResponseOk();
        $this->assertResponseRegExp('/"last_page"\s*:\s*1/');
        $this->assertResponseContains('Budget insuffisant apr');

        $this->post('/api/workflow-settings/comment-templates/' . $template->id . '/delete.json');
        $this->assertResponseOk();
        $this->assertSame(0, $templates->find()->count());
    }

    public function testUnSuperAdministrateurConfigureLObligationDeCommentaireParDecision(): void
    {
        $this->session(['Auth' => new User(['id' => 1, 'issuperuser' => true, 'role_id' => 1])]);
        $this->enableCsrfToken();
        $this->configRequest(['headers' => ['Accept' => 'application/json']]);

        $this->get('/api/workflow-settings/comment-requirements.json');
        $this->assertResponseOk();
        $this->assertResponseContains('"accepter":false');
        $this->assertResponseContains('"refuser":true');

        $this->post('/api/workflow-settings/comment-requirements.json', [
            'accepter' => true,
            'refuser' => false,
        ]);
        $this->assertResponseOk();
        $this->assertResponseContains('"accepter":true');
        $this->assertResponseContains('"refuser":false');

        $settings = $this->getTableLocator()->get('WorkflowSettings');
        $this->assertSame('1', $settings->find()->where(['name' => 'validation.comment_required.accept'])->firstOrFail()->value);
        $this->assertSame('0', $settings->find()->where(['name' => 'validation.comment_required.reject'])->firstOrFail()->value);
    }

    public function testUneModificationNeChangePasUneObligationDeCommentaireNonAutorisee(): void
    {
        $this->grant('update');
        $this->session(['Auth' => new User(['id' => 1, 'issuperuser' => false, 'role_id' => 1])]);
        $this->enableCsrfToken();
        $this->configRequest(['headers' => ['Accept' => 'application/json']]);

        $settings = $this->getTableLocator()->get('WorkflowSettings');
        $rejectRequirement = $settings->find()
            ->where(['name' => 'validation.comment_required.reject'])->firstOrFail();
        $rejectRequirement->set('value', '0');
        $settings->saveOrFail($rejectRequirement);
        $this->getTableLocator()->get('FieldAuthorizations')->getConnection()->execute(
            "INSERT INTO field_authorizations (role_id, resource, target_type, target, access_level, created, modified)
                VALUES (1, 'WorkflowSettings', 'field', 'accepter', 'EDIT', NOW(), NOW())",
        );

        $this->post('/api/workflow-settings/comment-requirements.json', [
            'accepter' => true,
        ]);

        $this->assertResponseOk();
        $this->assertSame(
            '0',
            $settings->find()->where(['name' => 'validation.comment_required.reject'])->firstOrFail()->value,
        );

        $this->post('/api/workflow-settings/comment-requirements.json', ['refuser' => true]);

        $this->assertResponseCode(403);
        $this->assertSame(
            '0',
            $settings->find()->where(['name' => 'validation.comment_required.reject'])->firstOrFail()->value,
        );
    }

    public function testUnSuperAdministrateurAccedeALecranDeParametrageGlobal(): void
    {
        $this->session(['Auth' => new User(['id' => 1, 'issuperuser' => true, 'role_id' => 1])]);

        $this->get('/workflow-settings');

        $this->assertResponseOk();
        $this->assertResponseContains('validation-comment-templates-grid');
        $this->assertResponseContains('id="workflow-settings-tabs"');
        $this->assertResponseContains('id="workflow-settings-validation-tab"');
        $this->assertResponseContains('id="workflow-settings-comments-tab"');
        $this->assertResponseContains('id="workflow-settings-applicationforms-tab"');
        $this->assertResponseContains('role="tablist"');
        $this->assertResponseContains('workflow-duplication-settings-title');
        $this->assertResponseContains('id="workflow-validation-settings-grid"');
        $this->assertResponseContains('id="workflow-applicationforms-settings-grid"');
        $this->assertResponseContains('name="position"');
        $this->assertResponseContains('placeholder="Position"');
        $this->assertResponseContains('class="content workflow-settings-page flex-grow-1 overflow-y-auto" style="min-height: 0;"');
        $responseBody = (string)$this->_response->getBody();
        $this->assertLessThan(
            strpos($responseBody, 'validation-comment-templates-grid'),
            strpos($responseBody, 'validation-comment-template-form'),
            'Le formulaire de création doit précéder la grille pour rester visible dans le layout verrouillé.',
        );
    }

    public function testLesEditeursRespectentLesAutorisationsIndividuellesDesChamps(): void
    {
        $this->grant('read');
        $this->grant('update');
        $this->session(['Auth' => new User(['id' => 1, 'issuperuser' => false, 'role_id' => 1])]);
        $this->getTableLocator()->get('FieldAuthorizations')->getConnection()->execute(
            "INSERT INTO field_authorizations (role_id, resource, target_type, target, access_level, created, modified)
                VALUES (1, 'WorkflowSettings', 'field', 'accepter', 'EDIT', NOW(), NOW())",
        );

        $this->get('/workflow-settings');

        $this->assertResponseOk();
        $this->assertResponseContains('data-can-edit-default-due-hours="false"');
        $this->assertResponseContains('data-can-edit-blocked-after-business-days="false"');
        $this->assertResponseContains('data-can-edit-comment-accept="true"');
        $this->assertResponseContains('data-can-edit-comment-reject="false"');
        $this->assertResponseContains('data-can-manage="false"');
    }

    public function testLesParametresInvalidesRetournentLeContratDErreurApi(): void
    {
        $this->session(['Auth' => new User(['id' => 1, 'issuperuser' => true, 'role_id' => 1])]);
        $this->enableCsrfToken();
        $this->configRequest(['headers' => ['Accept' => 'application/json']]);

        $this->post('/api/workflow-settings/comment-templates/create.json', [
            'decision' => 'invalide', 'label' => '', 'content' => '', 'position' => -1, 'active' => true,
        ]);

        $this->assertResponseCode(422);
        $this->assertResponseContains('"success":false');
        $this->assertResponseContains('Champ « Décision » :');
        $this->assertResponseContains('"field":"decision","label":"Décision","reason":');
    }

    public function testLaModificationDUnCommentairePredefiniRetourneUneErreurExploitable(): void
    {
        $this->session(['Auth' => new User(['id' => 1, 'issuperuser' => true, 'role_id' => 1])]);
        $this->enableCsrfToken();
        $template = $this->getTableLocator()->get('ValidationCommentTemplates')->newEntity([
            'decision' => 'refuser',
            'label' => 'Budget',
            'content' => 'Budget insuffisant.',
            'position' => 1,
            'active' => true,
        ]);
        $this->getTableLocator()->get('ValidationCommentTemplates')->saveOrFail($template);

        $this->post('/api/workflow-settings/comment-templates/' . $template->id . '.json', ['label' => '']);

        $this->assertResponseCode(422);
        $this->assertResponseContains('"message":"Champ « Libellé » :');
        $this->assertResponseContains('"field":"label","label":"Libellé","reason":');
    }

    public function testLaListeDesChampsDeDuplicationEstVersionneeEtNonModifiable(): void
    {
        $this->session(['Auth' => new User(['id' => 1, 'issuperuser' => true, 'role_id' => 1])]);
        $this->enableCsrfToken();
        $this->configRequest(['headers' => ['Accept' => 'application/json']]);

        $this->get('/api/workflow-settings/duplicate-reset-fields.json');
        $this->assertResponseOk();
        $this->assertResponseContains('"hr_description"');
        $this->assertResponseContains('"deprecated":true');

        $this->post('/api/workflow-settings/duplicate-reset-fields.json', ['fields' => ['jobtitle']]);
        $this->assertResponseCode(422);
        $this->assertResponseContains('"success":false');
        $this->assertResponseContains('liste est désormais versionnée');
    }

    public function testLaPermissionDeLectureAutoriseTousLesGetSansAutoriserLaModification(): void
    {
        $this->grant('read');
        $this->session(['Auth' => new User(['id' => 1, 'role_id' => 1])]);
        $this->enableCsrfToken();
        $this->configRequest(['headers' => ['Accept' => 'application/json']]);
        $actions = [
            'default-due-hours',
            'blocked-after-business-days',
            'comment-requirements',
            'k-coefficient',
            'duplicate-reset-fields',
        ];
        foreach ($actions as $action) {
            $this->get('/api/workflow-settings/' . $action . '.json');
            $this->assertResponseOk();
        }
        $settings = $this->getTableLocator()->get('WorkflowSettings');
        $connection = $settings->getConnection();
        $before = $connection->execute('SELECT * FROM workflow_settings ORDER BY id')->fetchAll('assoc');
        $mutations = [
            ['/api/workflow-settings/default-due-hours.json', ['default_due_hours' => 48]],
            ['/api/workflow-settings/blocked-after-business-days.json', ['blocked_after_business_days' => 5]],
            ['/api/workflow-settings/comment-requirements.json', ['accepter' => true, 'refuser' => false]],
            ['/api/workflow-settings/k-coefficient.json', ['k_coefficient' => 18.25]],
            ['/api/workflow-settings/duplicate-reset-fields.json', ['fields' => ['jobtitle']]],
        ];
        foreach ($mutations as [$url, $payload]) {
            $this->post($url, $payload);
            $this->assertResponseCode(403);
        }
        $after = $connection->execute('SELECT * FROM workflow_settings ORDER BY id')->fetchAll('assoc');
        $this->assertSame($before, $after);
    }

    public function testLaPermissionDeModificationNeDonnePasImplicitementLaLecture(): void
    {
        $this->grant('update');
        $this->session(['Auth' => new User(['id' => 1, 'role_id' => 1])]);
        $this->get('/api/workflow-settings/default-due-hours.json');
        $this->assertResponseCode(403);
    }

    public function testUnePermissionGeneriqueNePermetPasDeModifierLeCoefficientK(): void
    {
        $this->grant('update');
        $this->session(['Auth' => new User([
            'id' => 1,
            'issuperuser' => false,
            'role_id' => User::ROLE_ADMIN,
        ])]);
        $this->enableCsrfToken();
        $this->configRequest(['headers' => ['Accept' => 'application/json']]);

        $this->post('/api/workflow-settings/k-coefficient.json', ['k_coefficient' => 18.25]);

        $this->assertResponseCode(403);
        $this->assertSame(
            0,
            $this->getTableLocator()->get('WorkflowSettings')->find()
                ->where(['name' => 'salary.k_coefficient'])->count(),
        );
    }

    private function grant(string $action): void
    {
        $permission = $this->getTableLocator()->get('Permissions')->find()
            ->where(['resource' => 'workflowsettings', 'action' => $action])->firstOrFail();
        $grants = $this->getTableLocator()->get('RolePermissions');
        $grants->saveOrFail($grants->newEntity([
            'role_id' => 1, 'permission_id' => $permission->get('id'), 'scope_type' => 'unscoped',
        ]));
    }
}

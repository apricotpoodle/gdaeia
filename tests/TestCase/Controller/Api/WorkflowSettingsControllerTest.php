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

    public function testUnSuperAdministrateurAccedeALecranDeParametrageGlobal(): void
    {
        $this->session(['Auth' => new User(['id' => 1, 'issuperuser' => true, 'role_id' => 1])]);

        $this->get('/workflow-settings');

        $this->assertResponseOk();
        $this->assertResponseContains('validation-comment-templates-grid');
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
        $this->assertResponseContains('"errors"');
    }
}

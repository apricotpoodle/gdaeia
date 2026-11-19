<?php
declare(strict_types=1);

namespace App\Test\TestCase\Controller\Api;

use App\Model\Entity\User;
use Cake\TestSuite\IntegrationTestTrait;
use Cake\TestSuite\TestCase;
use DOMDocument;
use DOMXPath;

/** Vérifie les périmètres locaux et l'atomicité de l'administration HTTP. */
final class ValidationCircuitRulesControllerTest extends TestCase
{
    use IntegrationTestTrait;

    protected array $fixtures = [
        'app.Departments', 'app.Roles', 'app.Contracttypes', 'app.Users',
        'app.UserDepartments', 'app.Validationsequences', 'app.ValidationCircuitRules',
        'app.ValidationCircuitManagers',
    ];

    protected function setUp(): void
    {
        parent::setUp();
        $connection = $this->getTableLocator()->get('Departments')->getConnection();
        $connection->update('departments', ['deleted' => null], ['id' => 1]);
        $connection->update('roles', ['deleted' => null], ['id' => 1]);
        $connection->update('contracttypes', ['deleted' => null], ['id' => 1]);
        $connection->update('users', ['deleted' => null, 'issuperuser' => 0], ['id' => 1]);
        $connection->update('validationsequences', ['deleted' => null], ['id' => 1]);
        $connection->insert('user_departments', ['user_id' => 1, 'department_id' => 2]);
        $connection->insert('validationsequences', [
            'department_id' => 2, 'role_id' => 1, 'sequence' => 1, 'name' => '',
        ]);
        $this->login(false);
        $this->enableCsrfToken();
        $this->configRequest(['headers' => ['Accept' => 'application/json']]);
    }

    public function testUnAdministrateurSansHabilitationNePeutPasLireLesRegles(): void
    {
        $this->get('/api/validation-circuit-rules.json');
        $this->assertResponseCode(403);
        $this->get('/validation-circuit-rules');
        $this->assertResponseCode(403);
    }

    public function testUneHabilitationEtUnRattachementSontTousDeuxNecessaires(): void
    {
        $this->grant(1);
        $this->getTableLocator()->get('UserDepartments')->deleteAll(['user_id' => 1, 'department_id' => 1]);
        $this->get('/api/validation-circuit-rules.json');
        $this->assertResponseCode(403);

        $this->getTableLocator()->get('UserDepartments')->getConnection()->insert(
            'user_departments',
            ['user_id' => 1, 'department_id' => 1],
        );
        $this->get('/api/validation-circuit-rules/departments-tree.json');
        $this->assertResponseOk();
        $tree = json_decode((string)$this->_response->getBody(), true)['data'];
        $this->assertCount(1, $tree);
        $this->assertSame(1, $tree[0]['id']);
        $this->assertEmpty($tree[0]['children'] ?? []);
    }

    public function testUneCreationGroupeeHorsPerimetreNecritRien(): void
    {
        $this->grant(1);
        $this->post('/api/validation-circuit-rules/add.json', $this->ruleData([1, 2]));
        $this->assertResponseCode(403);
        $this->assertSame(0, $this->getTableLocator()->get('ValidationCircuitRules')->find()->count());
    }

    public function testUnDoublonAnnuleToutLeLotSansRemplacerLaRegle(): void
    {
        $this->grant(1);
        $this->grant(2);
        $this->post('/api/validation-circuit-rules/add.json', $this->ruleData([2]));
        $this->assertResponseOk();

        $this->post('/api/validation-circuit-rules/add.json', $this->ruleData([1, 2]));
        $this->assertResponseCode(422);
        $this->assertResponseContains('"success":false');
        $this->assertSame(1, $this->getTableLocator()->get('ValidationCircuitRules')->find()->count());
    }

    public function testUnGestionnaireLocalCreeModifieEtRetireUneRegleDeSonService(): void
    {
        $this->grant(2);
        $this->post('/api/validation-circuit-rules/add.json', $this->ruleData([2, 2]));
        $this->assertResponseOk();
        $table = $this->getTableLocator()->get('ValidationCircuitRules');
        $rule = $table->find()->firstOrFail();
        $this->assertSame(2, (int)$rule->get('department_id'));
        $this->assertSame(1, $table->find()->count());

        $this->post('/api/validation-circuit-rules/' . $rule->get('id') . '.json', ['final_role_id' => 1]);
        $this->assertResponseOk();
        $this->post('/api/validation-circuit-rules/' . $rule->get('id') . '.json', ['department_id' => 1]);
        $this->assertResponseCode(403);
        $this->assertSame(2, (int)$table->get($rule->get('id'))->get('department_id'));

        $this->get('/api/validation-circuit-rules.json?size=1');
        $this->assertResponseOk();
        $payload = json_decode((string)$this->_response->getBody(), true);
        $this->assertSame(1, $payload['last_page']);
        $this->assertTrue($payload['data'][0]['grid_rights']['actions']['edit']);
        $this->post('/api/validation-circuit-rules/' . $rule->get('id') . '/delete.json');
        $this->assertResponseOk();
        $this->assertSame(0, $table->find()->count());
    }

    public function testLesHabilitationsSontReserveesAuSuperAdministrateur(): void
    {
        $this->grant(1);
        $this->post('/api/validation-circuit-rules/managers/assign.json', ['department_ids' => [2], 'role_id' => 1]);
        $this->assertResponseCode(403);
        $this->get('/api/validation-circuit-rules/managers.json');
        $this->assertResponseCode(403);

        $this->login(true);
        $this->get('/validation-circuit-rules');
        $this->assertResponseOk();
        $xpath = $this->responseXPath();
        $this->assertHtmlNodeCount($xpath, '//*[@role="tab"]', 2);
        $this->assertHtmlNodeCount($xpath, '//*[@id="validation-circuit-rules-tab" and @aria-selected="true"]', 1);
        $this->assertHtmlNodeCount($xpath, '//*[@id="validation-circuit-rules-panel"]//*[@id="validation-circuit-rule-form"]', 1);
        $this->assertHtmlNodeCount($xpath, '//*[@id="validation-circuit-rules-card"]', 1);
        $this->assertHtmlNodeCount($xpath, '//*[@id="validation-circuit-editor-column" and contains(concat(" ", normalize-space(@class), " "), " col-12 ") and contains(concat(" ", normalize-space(@class), " "), " col-lg-6 ")]', 1);
        $this->assertHtmlNodeCount($xpath, '//*[@id="validation-circuit-rules-grid-column" and contains(concat(" ", normalize-space(@class), " "), " col-12 ") and contains(concat(" ", normalize-space(@class), " "), " col-lg-6 ")]', 1);
        $this->assertHtmlNodeCount($xpath, '//*[@id="validation-circuit-rules-card"]//*[@id="validation-circuit-editor-column"]//*[@id="validation-circuit-rule-form"]', 1);
        $this->assertHtmlNodeCount($xpath, '//*[@id="validation-circuit-rules-card"]//*[@id="validation-circuit-rules-grid-column"]//*[@id="validation-circuit-rules-grid"]', 1);
        $this->assertHtmlNodeCount($xpath, '//*[@id="validation-circuit-departments-column" and contains(concat(" ", normalize-space(@class), " "), " col-12 ")]//*[@id="validation-circuit-departments"]', 1);
        $this->assertHtmlNodeCount($xpath, '//*[@id="validation-circuit-contracttype-column" and contains(concat(" ", normalize-space(@class), " "), " col-md-6 ")]/ancestor::div[contains(concat(" ", normalize-space(@class), " "), " row ")][1]//*[@id="validation-circuit-final-role-column" and contains(concat(" ", normalize-space(@class), " "), " col-md-6 ")]', 1);
        $this->assertHtmlNodeCount($xpath, '//*[@id="validation-circuit-editor-column"]//*[@id="validation-circuit-preview"]', 1);
        $this->assertHtmlNodeCount($xpath, '//*[@id="validation-circuit-managers-panel"]//*[@id="validation-circuit-manager-form"]', 1);
        $this->assertHtmlNodeCount(
            $xpath,
            '//*[@id="validation-circuit-manager-form-column"]//button[@id="assign-validation-circuit-managers"]',
            1,
        );
        $this->assertHtmlNodeCount(
            $xpath,
            '//*[@id="assign-validation-circuit-managers"]/following::select[@id="validation-circuit-manager-role"]'
                . '[ancestor::*[@id="validation-circuit-manager-form-column"]]',
            1,
        );
        $this->assertHtmlNodeCount(
            $xpath,
            '//*[@id="validation-circuit-manager-role"]/following::*[@id="validation-circuit-manager-departments"]'
                . '[ancestor::*[@id="validation-circuit-manager-form-column"]]',
            1,
        );
        $this->assertHtmlNodeCount(
            $xpath,
            '//*[@id="validation-circuit-managers-grid-column"]//h4[@id="validation-circuit-managers-grid-title"]',
            1,
        );
        $this->assertHtmlNodeCount(
            $xpath,
            '//*[@id="validation-circuit-managers-grid-column"]//*[@id="validation-circuit-managers-grid"]',
            1,
        );
        $this->post('/api/validation-circuit-rules/managers/assign.json', ['department_ids' => [2], 'role_id' => 1]);
        $this->assertResponseOk();
        $this->get('/api/validation-circuit-rules/managers.json?size=1');
        $this->assertResponseOk();
        $payload = json_decode((string)$this->_response->getBody(), true);
        $this->assertSame(2, $payload['last_page']);
        $manager = $this->getTableLocator()->get('ValidationCircuitManagers')->find()
            ->where(['department_id' => 2])->firstOrFail();
        $this->post('/api/validation-circuit-rules/managers/' . $manager->get('id') . '/revoke.json');
        $this->assertResponseOk();
        $this->assertSame(1, $this->getTableLocator()->get('ValidationCircuitManagers')->find()->count());
    }

    public function testUneConfigurationSansValidateurNestPasEnregistreeEtSonApercuExpliqueLeProbleme(): void
    {
        $this->grant(2);
        $this->getTableLocator()->get('UserDepartments')->deleteAll(['department_id' => 2]);
        $this->login(true);
        $this->post('/api/validation-circuit-rules/preview.json', $this->ruleData([2]));
        $this->assertResponseOk();
        $payload = json_decode((string)$this->_response->getBody(), true);
        $this->assertNotEmpty($payload['data'][0]['issues']);
        $this->post('/api/validation-circuit-rules/add.json', $this->ruleData([2]));
        $this->assertResponseCode(422);
        $this->assertSame(0, $this->getTableLocator()->get('ValidationCircuitRules')->find()->count());
    }

    public function testLesListesEtLeTriAssocieRestentDansLePerimetre(): void
    {
        $this->grant(2);
        $connection = $this->getTableLocator()->get('ValidationCircuitRules')->getConnection();
        foreach ([1, 2] as $departmentId) {
            $connection->insert('validation_circuit_rules', [
                'department_id' => $departmentId, 'contracttype_id' => 1, 'final_role_id' => 1,
                'created' => '2026-11-06 10:00:00', 'modified' => '2026-11-06 10:00:00',
            ]);
        }
        $this->get('/api/validation-circuit-rules.json?' . http_build_query([
            'sorters' => [['field' => 'department.name', 'dir' => 'asc']],
            'filters' => [['field' => 'contracttype.name', 'type' => 'like', 'value' => 'Lorem']],
        ]));
        $this->assertResponseOk();
        $payload = json_decode((string)$this->_response->getBody(), true);
        $this->assertCount(1, $payload['data']);
        $this->assertSame(2, $payload['data'][0]['department_id']);
    }

    public function testUneRegleDevenueInvalideResteVisibleAvecSonDiagnostic(): void
    {
        $this->login(true);
        $this->post('/api/validation-circuit-rules/add.json', $this->ruleData([2]));
        $this->assertResponseOk();
        $this->getTableLocator()->get('Roles')->getConnection()->update(
            'roles',
            ['deleted' => '2026-11-06 12:00:00'],
            ['id' => 1],
        );
        $this->get('/api/validation-circuit-rules.json');
        $this->assertResponseOk();
        $payload = json_decode((string)$this->_response->getBody(), true);
        $this->assertCount(1, $payload['data']);
        $this->assertNotEmpty($payload['data'][0]['configuration_issues'] ?? []);
        $this->assertStringContainsString('inactif', $payload['data'][0]['configuration_issues'][0]);
    }

    public function testUneIdentiteUsurpeeNheritePasDesHabilitationsDuSuperAdministrateur(): void
    {
        $this->session([
            'Auth' => new User(['id' => 2, 'role_id' => 2, 'issuperuser' => false]),
            'AuthImpersonate' => new User(['id' => 1, 'role_id' => 1, 'issuperuser' => true]),
        ]);
        $this->get('/api/validation-circuit-rules.json');
        $this->assertResponseCode(403);
        $this->get('/validation-circuit-rules');
        $this->assertResponseCode(403);
        $this->post('/api/validation-circuit-rules/managers/assign.json', ['department_ids' => [2], 'role_id' => 2]);
        $this->assertResponseCode(403);
    }

    public function testUnRoleNonAdministrateurDesignePeutGererSonDepartement(): void
    {
        $this->getTableLocator()->get('Roles')->getConnection()->insert('roles', [
            'id' => 2, 'base' => 0, 'code' => 'gestion', 'name' => 'Gestionnaire local', 'sort' => 'gestion',
            'deleted' => null, 'created' => '2026-11-06 10:00:00', 'modified' => '2026-11-06 10:00:00',
        ]);
        $this->grant(2, 2);
        $this->session(['Auth' => new User(['id' => 1, 'role_id' => 2, 'issuperuser' => false])]);
        $this->get('/validation-circuit-rules');
        $this->assertResponseOk();
        $xpath = $this->responseXPath();
        $this->assertHtmlNodeCount($xpath, '//*[@role="tab"]', 1);
        $this->assertHtmlNodeCount($xpath, '//*[@id="validation-circuit-rules-tab" and @aria-selected="true"]', 1);
        $this->assertHtmlNodeCount($xpath, '//*[@id="validation-circuit-rules-panel"]//*[@id="validation-circuit-rule-form"]', 1);
        $this->assertHtmlNodeCount($xpath, '//*[@id="validation-circuit-managers-tab"]', 0);
        $this->assertHtmlNodeCount($xpath, '//*[@id="validation-circuit-managers-panel"]', 0);
        $this->assertHtmlNodeCount($xpath, '//*[@id="validation-circuit-manager-form"]', 0);
        $this->post('/api/validation-circuit-rules/add.json', $this->ruleData([2]));
        $this->assertResponseOk();
        $this->get('/api/validation-circuit-rules.json');
        $this->assertResponseOk();
        $payload = json_decode((string)$this->_response->getBody(), true);
        $this->assertCount(1, $payload['data']);
        $this->assertSame(2, $payload['data'][0]['department_id']);
    }

    /** Identité effective, sans héritage d'un éventuel administrateur d'origine. */
    private function login(bool $superuser): void
    {
        $this->session(['Auth' => new User(['id' => 1, 'role_id' => 1, 'issuperuser' => $superuser])]);
    }

    /** Ajoute une habilitation explicite indépendante du rôle final. */
    private function grant(int $departmentId, int $roleId = 1): void
    {
        $this->getTableLocator()->get('ValidationCircuitManagers')->getConnection()->insert(
            'validation_circuit_managers',
            [
                'department_id' => $departmentId, 'role_id' => $roleId,
                'created' => '2026-11-06 10:00:00', 'modified' => '2026-11-06 10:00:00',
            ],
        );
    }

    private function assertHtmlNodeCount(DOMXPath $xpath, string $query, int $expected): void
    {
        $nodes = $xpath->query($query);
        $this->assertNotFalse($nodes);
        $this->assertSame($expected, $nodes->length);
    }

    private function responseXPath(): DOMXPath
    {
        $document = new DOMDocument();
        $previous = libxml_use_internal_errors(true);
        try {
            $this->assertTrue($document->loadHTML((string)$this->_response->getBody()));
        } finally {
            libxml_clear_errors();
            libxml_use_internal_errors($previous);
        }

        return new DOMXPath($document);
    }

    /** @param list<int> $departmentIds @return array<string, mixed> */
    private function ruleData(array $departmentIds): array
    {
        return ['department_ids' => $departmentIds, 'contracttype_id' => 1, 'final_role_id' => 1];
    }
}

<?php
declare(strict_types=1);

namespace App\Test\TestCase\Model\Table;

use App\Model\Entity\FieldAuthorization;
use App\Model\Table\FieldAuthorizationsTable;
use Cake\TestSuite\TestCase;

/**
 * App\Model\Table\FieldAuthorizationsTable Test Case
 */
class FieldAuthorizationsTableTest extends TestCase
{
    /**
     * Test subject
     *
     * @var \App\Model\Table\FieldAuthorizationsTable
     */
    protected $FieldAuthorizations;

    /**
     * Fixtures
     *
     * @var array<string>
     */
    protected array $fixtures = [
        'app.FieldAuthorizations',
        'app.Roles',
        'app.FieldDefinitions',
        'app.AuthorizationTargets',
    ];

    /**
     * setUp method
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();
        $config = $this->getTableLocator()->exists('FieldAuthorizations') ? [] : ['className' => FieldAuthorizationsTable::class];
        $this->FieldAuthorizations = $this->getTableLocator()->get('FieldAuthorizations', $config);
    }

    /**
     * tearDown method
     *
     * @return void
     */
    protected function tearDown(): void
    {
        unset($this->FieldAuthorizations);

        parent::tearDown();
    }

    /**
     * Test validationDefault method
     *
     * @return void
     * @link \App\Model\Table\FieldAuthorizationsTable::validationDefault()
     */
    public function testValidationParDefaut(): void
    {
        $authorization = $this->FieldAuthorizations->newEntity([
            'role_id' => -1,
            'resource' => '',
            'target_type' => 'field',
            'target' => str_repeat('a', 81),
            'access_level' => '',
        ]);

        $this->assertArrayHasKey('role_id', $authorization->getErrors());
        $this->assertArrayHasKey('resource', $authorization->getErrors());
        $this->assertArrayHasKey('target', $authorization->getErrors());
        $this->assertArrayHasKey('access_level', $authorization->getErrors());
    }

    public function testLeNiveauDAccesEstNormaliseEnMajuscules(): void
    {
        $authorization = $this->FieldAuthorizations->newEntity([
            'role_id' => 1,
            'resource' => 'Applicationforms',
            'target_type' => 'field',
            'target' => 'email',
            'access_level' => 'view',
        ]);

        $this->assertSame(FieldAuthorization::ACCESS_LEVEL_VIEW, $authorization->access_level);
        $this->assertArrayNotHasKey('access_level', $authorization->getErrors());
    }

    /**
     * Test buildRules method
     *
     * @return void
     * @link \App\Model\Table\FieldAuthorizationsTable::buildRules()
     */
    public function testReglesIntegrite(): void
    {
        $authorization = $this->FieldAuthorizations->newEntity([
            'role_id' => 99999,
            'resource' => 'Applicationforms',
            'target_type' => 'field',
            'target' => 'jobtitle',
            'access_level' => 'EDIT',
        ]);

        $this->assertFalse($this->FieldAuthorizations->save($authorization));
        $this->assertArrayHasKey('role_id', $authorization->getErrors());
    }

    public function testUnDoublonDAutorisationRecoitUnMotifFrancais(): void
    {
        $authorization = $this->FieldAuthorizations->newEntity([
            'role_id' => 1,
            'resource' => 'Applicationforms',
            'target_type' => 'field',
            'target' => 'jobtitle',
            'access_level' => 'EDIT',
        ]);

        $this->assertFalse($this->FieldAuthorizations->save($authorization));
        $this->assertContains(
            'Une autorisation existe déjà pour ce rôle, cette ressource et cette cible.',
            $authorization->getError('role_id'),
        );
    }

    public function testUneCibleInconnueEstRefusee(): void
    {
        $authorization = $this->FieldAuthorizations->newEntity([
            'role_id' => 1,
            'resource' => 'Applicationforms',
            'target_type' => 'field',
            'target' => 'inconnu',
            'access_level' => 'EDIT',
        ]);

        $this->assertFalse($this->FieldAuthorizations->save($authorization));
        $this->assertArrayHasKey('target', $authorization->getErrors());
    }

    public function testUnChampOrmActifEstAccepte(): void
    {
        $authorization = $this->FieldAuthorizations->newEntity([
            'role_id' => 1,
            'resource' => 'Applicationforms',
            'target_type' => 'field',
            'target' => 'department_id',
            'access_level' => 'EDIT',
        ]);

        $savedAuthorization = $this->FieldAuthorizations->save($authorization);

        $this->assertNotFalse($savedAuthorization);
        $this->assertSame('department_id', $savedAuthorization->target);
    }

    public function testUneSectionFonctionnelleActiveEstAcceptee(): void
    {
        $authorization = $this->FieldAuthorizations->newEntity([
            'role_id' => 1,
            'resource' => 'Applicationforms',
            'target_type' => 'section',
            'target' => 'job_section',
            'access_level' => 'EDIT',
        ]);

        $savedAuthorization = $this->FieldAuthorizations->save($authorization);

        $this->assertNotFalse($savedAuthorization);
        $this->assertSame('job_section', $savedAuthorization->target);
    }

    public function testLeFiltreDeCibleDistingueLesRessourcesEtLesTypesAvecUneMemeCle(): void
    {
        $definitions = $this->getTableLocator()->get('FieldDefinitions');
        $definitions->saveOrFail($definitions->newEntity([
            'resource' => 'Applicationforms',
            'field' => 'job_section',
            'label' => 'Détail du poste',
            'active' => true,
            'position' => 100,
        ]));
        $sections = $this->getTableLocator()->get('AuthorizationTargets');
        $sections->saveOrFail($sections->newEntity([
            'resource' => 'Roles',
            'target_type' => 'section',
            'target' => 'job_section',
            'label' => 'Zone des rôles',
            'active' => true,
            'position' => 100,
        ]));

        $targets = [
            ['Roles', 'field', 'name'],
            ['Menus', 'field', 'name'],
            ['Applicationforms', 'field', 'job_section'],
            ['Applicationforms', 'section', 'job_section'],
            ['Roles', 'section', 'job_section'],
        ];
        foreach ($targets as [$resource, $type, $target]) {
            $this->FieldAuthorizations->saveOrFail($this->FieldAuthorizations->newEntity([
                'role_id' => 1,
                'resource' => $resource,
                'target_type' => $type,
                'target' => $target,
                'access_level' => 'VIEW',
            ]));
        }

        $expectedMatches = [
            'menu' => ['resource' => 'Menus', 'target_type' => 'field', 'target' => 'name'],
            'métier' => ['resource' => 'Applicationforms', 'target_type' => 'section', 'target' => 'job_section'],
            'rôles' => ['resource' => 'Roles', 'target_type' => 'section', 'target' => 'job_section'],
            'Détail' => ['resource' => 'Applicationforms', 'target_type' => 'field', 'target' => 'job_section'],
        ];
        foreach ($expectedMatches as $term => $expectedMatch) {
            $condition = $this->FieldAuthorizations->resolveTargetFilter($term, 'like');
            $this->assertNotNull($condition);
            $matches = $this->FieldAuthorizations->find()
                ->select(['resource', 'target_type', 'target'])
                ->where($condition)
                ->disableHydration()
                ->toArray();

            $this->assertSame([$expectedMatch], $matches, 'Résultat du filtre « ' . $term . ' ».');
        }
    }

    public function testUneSectionFonctionnelleInconnueEstRefusee(): void
    {
        $authorization = $this->FieldAuthorizations->newEntity([
            'role_id' => 1,
            'resource' => 'Applicationforms',
            'target_type' => 'section',
            'target' => 'unknown_section',
            'access_level' => 'EDIT',
        ]);

        $this->assertFalse($this->FieldAuthorizations->save($authorization));
        $this->assertArrayHasKey('target', $authorization->getErrors());
    }

    public function testUnChampOrmInactifEstRefusePourUneNouvelleAutorisation(): void
    {
        $fieldDefinitions = $this->getTableLocator()->get('FieldDefinitions');
        $fieldDefinitions->updateAll(['active' => false], ['field' => 'department_id']);

        $authorization = $this->FieldAuthorizations->newEntity([
            'role_id' => 1,
            'resource' => 'Applicationforms',
            'target_type' => 'field',
            'target' => 'department_id',
            'access_level' => 'EDIT',
        ]);

        $this->assertFalse($this->FieldAuthorizations->save($authorization));
        $this->assertArrayHasKey('target', $authorization->getErrors());
    }

    public function testUneSectionFonctionnelleInactiveEstRefuseePourUneNouvelleAutorisation(): void
    {
        $authorizationTargets = $this->getTableLocator()->get('AuthorizationTargets');
        $authorizationTargets->updateAll(['active' => false], ['target' => 'job_section']);

        $authorization = $this->FieldAuthorizations->newEntity([
            'role_id' => 1,
            'resource' => 'Applicationforms',
            'target_type' => 'section',
            'target' => 'job_section',
            'access_level' => 'EDIT',
        ]);

        $this->assertFalse($this->FieldAuthorizations->save($authorization));
        $this->assertArrayHasKey('target', $authorization->getErrors());
    }

    public function testUneAutorisationDeChampExistanteResteModifiableApresDesactivation(): void
    {
        $fieldDefinitions = $this->getTableLocator()->get('FieldDefinitions');
        $fieldDefinitions->updateAll(['active' => false], ['field' => 'jobtitle']);
        $authorization = $this->FieldAuthorizations->get(1);
        $this->assertSame('jobtitle', $authorization->target);
        $this->assertSame('field', $authorization->target_type);
        $this->FieldAuthorizations->patchEntity($authorization, ['access_level' => 'EDIT']);

        $savedAuthorization = $this->FieldAuthorizations->save($authorization);

        $this->assertNotFalse($savedAuthorization);
        $this->assertSame('EDIT', $savedAuthorization->access_level);
    }

    public function testModificationVersUnChampInactifEstRefusee(): void
    {
        $fieldDefinitions = $this->getTableLocator()->get('FieldDefinitions');
        $fieldDefinitions->updateAll(
            ['active' => false],
            ['resource' => 'Applicationforms', 'field' => 'working_days'],
        );
        $authorization = $this->FieldAuthorizations->get(1);
        $this->assertSame('jobtitle', $authorization->target);
        $this->assertSame('field', $authorization->target_type);
        $this->FieldAuthorizations->patchEntity($authorization, ['target' => 'working_days']);

        $savedAuthorization = $this->FieldAuthorizations->save($authorization);

        $this->assertFalse($savedAuthorization);
        $this->assertArrayHasKey('target', $authorization->getErrors());
    }

    public function testUneAutorisationDeSectionExistanteResteModifiableApresDesactivation(): void
    {
        $authorization = $this->FieldAuthorizations->newEntity([
            'role_id' => 1,
            'resource' => 'Applicationforms',
            'target_type' => 'section',
            'target' => 'job_section',
            'access_level' => 'EDIT',
        ]);
        $savedAuthorization = $this->FieldAuthorizations->save($authorization);
        $this->assertNotFalse($savedAuthorization);

        $authorizationTargets = $this->getTableLocator()->get('AuthorizationTargets');
        $authorizationTargets->updateAll(['active' => false], ['target' => 'job_section']);
        $inactiveSection = $authorizationTargets->find()->where([
            'resource' => 'Applicationforms',
            'target_type' => 'section',
            'target' => 'job_section',
        ])->first();
        $this->assertNotNull($inactiveSection);
        $this->assertFalse($inactiveSection->active);
        $authorization = $this->FieldAuthorizations->get($savedAuthorization->id);
        $this->assertFalse($authorization->isNew());
        $this->assertSame('Applicationforms', $authorization->resource);
        $this->assertSame('job_section', $authorization->target);
        $this->assertSame('section', $authorization->target_type);
        $this->assertSame('Applicationforms', $authorization->getOriginal('resource'));
        $this->assertSame('section', $authorization->getOriginal('target_type'));
        $this->assertSame('job_section', $authorization->getOriginal('target'));
        $this->FieldAuthorizations->patchEntity($authorization, ['access_level' => 'VIEW']);
        $this->assertFalse($authorization->isNew());
        $this->assertSame('Applicationforms', $authorization->resource);
        $this->assertSame('section', $authorization->target_type);
        $this->assertSame('job_section', $authorization->target);
        $this->assertSame('Applicationforms', $authorization->getOriginal('resource'));
        $this->assertSame('section', $authorization->getOriginal('target_type'));
        $this->assertSame('job_section', $authorization->getOriginal('target'));

        $savedAuthorization = $this->FieldAuthorizations->save($authorization);

        $this->assertNotFalse($savedAuthorization);
        $this->assertSame('VIEW', $savedAuthorization->access_level);
    }
}

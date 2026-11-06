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
}

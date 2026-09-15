<?php
declare(strict_types=1);

namespace App\Test\TestCase\Model\Table;

use App\Model\Entity\User;
use App\Model\Table\RolesTable;
use Cake\TestSuite\TestCase;

/**
 * App\Model\Table\RolesTable Test Case
 */
class RolesTableTest extends TestCase
{
    /**
     * Test subject
     *
     * @var \App\Model\Table\RolesTable
     */
    protected $Roles;

    /**
     * Fixtures
     *
     * @var array<string>
     */
    protected array $fixtures = [
        'app.Roles',
        'app.Applicationvalidationsteps',
        'app.FieldAuthorizations',
        'app.RoleMenus',
        'app.Users',
        'app.Validations',
        'app.Validationsequences',
    ];

    /**
     * setUp method
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();
        $config = $this->getTableLocator()->exists('Roles') ? [] : ['className' => RolesTable::class];
        $this->Roles = $this->getTableLocator()->get('Roles', $config);
    }

    /**
     * tearDown method
     *
     * @return void
     */
    protected function tearDown(): void
    {
        unset($this->Roles);

        parent::tearDown();
    }

    /**
     * Test validationDefault method
     *
     * @return void
     * @link \App\Model\Table\RolesTable::validationDefault()
     */
    public function testValidationParDefaut(): void
    {
        $role = $this->Roles->newEntity([
            'base' => 'invalide',
            'code' => str_repeat('a', 17),
            'name' => '',
            'sort' => '',
        ]);

        $this->assertArrayHasKey('base', $role->getErrors());
        $this->assertArrayHasKey('code', $role->getErrors());
        $this->assertArrayHasKey('name', $role->getErrors());
        $this->assertArrayHasKey('sort', $role->getErrors());
    }

    /**
     * Test buildRules method
     *
     * @return void
     * @link \App\Model\Table\RolesTable::buildRules()
     */
    public function testReglesIntegrite(): void
    {
        $role = $this->Roles->newEntity([
            'base' => false,
            'code' => 'Lorem ipsum do',
            'name' => 'Nouveau role',
            'sort' => 'nouveau-role',
        ]);

        $this->assertFalse($this->Roles->save($role));
        $this->assertArrayHasKey('code', $role->getErrors());
    }

    /** Vérifie que le finder transversal ne retourne jamais un rôle désactivé. */
    public function testLeFinderVisibleToExclutLesRolesDesactives(): void
    {
        $this->Roles->updateAll(['deleted' => '2026-09-15 12:00:00'], ['id' => 1]);
        $operator = new User(['id' => 1, 'issuperuser' => true]);

        $this->assertSame(0, $this->Roles->find('visibleTo', user: $operator)->count());
    }

    /** Vérifie que la suppression métier reste une désactivation logique. */
    public function testSoftDeleteConserveLenregistrementEtRenseigneDeleted(): void
    {
        $role = $this->Roles->newEntity([
            'base' => false,
            'code' => 'TEMPORAIRE',
            'name' => 'Rôle temporaire',
            'sort' => 'temporaire',
        ]);
        $this->assertNotFalse($this->Roles->save($role));

        $this->assertTrue($this->Roles->softDelete($role));
        $this->assertNotNull($this->Roles->get($role->id)->deleted);
    }
}

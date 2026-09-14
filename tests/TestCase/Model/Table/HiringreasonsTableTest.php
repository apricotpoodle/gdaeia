<?php
declare(strict_types=1);

namespace App\Test\TestCase\Model\Table;

use App\Model\Table\HiringreasonsTable;
use Cake\TestSuite\TestCase;

/**
 * App\Model\Table\HiringreasonsTable Test Case
 */
class HiringreasonsTableTest extends TestCase
{
    /**
     * Test subject
     *
     * @var \App\Model\Table\HiringreasonsTable
     */
    protected $Hiringreasons;

    /**
     * Fixtures
     *
     * @var array<string>
     */
    protected array $fixtures = [
        'app.Hiringreasons',
        'app.Applicationforms',
    ];

    /**
     * setUp method
     *
     * @return void
     */
    protected function setUp(): void
    {
        parent::setUp();
        $config = $this->getTableLocator()->exists('Hiringreasons') ? [] : ['className' => HiringreasonsTable::class];
        $this->Hiringreasons = $this->getTableLocator()->get('Hiringreasons', $config);
    }

    /**
     * tearDown method
     *
     * @return void
     */
    protected function tearDown(): void
    {
        unset($this->Hiringreasons);

        parent::tearDown();
    }

    /**
     * Test validationDefault method
     *
     * @return void
     * @link \App\Model\Table\HiringreasonsTable::validationDefault()
     */
    public function testValidationParDefaut(): void
    {
        $entity = $this->Hiringreasons->newEntity(['base' => 'invalide', 'code' => str_repeat('a', 17), 'name' => '', 'sort' => '']);
        $this->assertArrayHasKey('base', $entity->getErrors());
        $this->assertArrayHasKey('code', $entity->getErrors());
        $this->assertArrayHasKey('name', $entity->getErrors());
        $this->assertArrayHasKey('sort', $entity->getErrors());
    }

    /**
     * Test buildRules method
     *
     * @return void
     * @link \App\Model\Table\HiringreasonsTable::buildRules()
     */
    public function testReglesIntegrite(): void
    {
        $entity = $this->Hiringreasons->newEntity(['base' => false, 'code' => 'Lorem ipsum do', 'name' => 'Nouveau', 'sort' => 'nouveau']);
        $this->assertFalse($this->Hiringreasons->save($entity));
        $this->assertArrayHasKey('code', $entity->getErrors());
    }
}

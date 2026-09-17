<?php
declare(strict_types=1);

namespace App\Test\TestCase\Model\Table;

use App\Service\TreeIntegrityChecker;
use Cake\ORM\TableRegistry;
use Cake\TestSuite\TestCase;

/** Tests d'intégration du diagnostic des arbres intervallaires. */
class TreeIntegrityCheckerTest extends TestCase
{
    /**
     * @var array<string>
     */
    protected array $fixtures = [
        'app.Departments',
        'app.Menus',
    ];

    public function testLesArbresDesFixturesSontCoherents(): void
    {
        $reports = (new TreeIntegrityChecker($this->getTableLocator()))->check();

        $this->assertCount(2, $reports);
        $this->assertSame('departments', $reports[0]['table']);
        $this->assertTrue($reports[0]['success']);
        $this->assertSame('menus', $reports[1]['table']);
        $this->assertTrue($reports[1]['success']);
    }

    public function testUnCycleParentEstRapporteAvecSonChemin(): void
    {
        $connection = $this->getTableLocator()->get('Menus')->getConnection();
        $connection->execute('UPDATE menus SET parent_id = 1 WHERE id = 1');

        $report = (new TreeIntegrityChecker($this->getTableLocator()))->check('menus')[0];
        $issues = array_column($report['issues'], 'code');

        $this->assertFalse($report['success']);
        $this->assertContains('PARENT_LUI_MEME', $issues);
        $this->assertContains('CYCLE_PARENT', $issues);
    }

    public function testUnParentInexistantEtDesBornesIncoherentesSontRapportes(): void
    {
        $connection = $this->getTableLocator()->get('Menus')->getConnection();
        $connection->execute('UPDATE menus SET parent_id = 999, lft = 3 WHERE id = 1');

        $report = (new TreeIntegrityChecker($this->getTableLocator()))->check('menus')[0];
        $issues = array_column($report['issues'], 'code');

        $this->assertContains('PARENT_INEXISTANT', $issues);
        $this->assertContains('BORNES_NON_CONTIGUES', $issues);
    }

    protected function tearDown(): void
    {
        TableRegistry::getTableLocator()->remove('Departments');
        TableRegistry::getTableLocator()->remove('Menus');
        parent::tearDown();
    }
}

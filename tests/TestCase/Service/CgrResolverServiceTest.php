<?php
declare(strict_types=1);

namespace App\Test\TestCase\Service;

use App\Model\Entity\CgrCode;
use App\Model\Entity\CgrStrategy;
use App\Model\Entity\Department;
use App\Model\Table\CgrCodesTable;
use App\Model\Table\DepartmentsTable;
use App\Service\CgrResolverService;
use Cake\ORM\Query\SelectQuery;
use Cake\ORM\ResultSet;
use Cake\ORM\TableRegistry;
use Cake\TestSuite\TestCase;

class CgrResolverServiceTest extends TestCase
{
    protected function tearDown(): void
    {
        TableRegistry::getTableLocator()->remove('Departments');
        TableRegistry::getTableLocator()->remove('CgrCodes');
        parent::tearDown();
    }

    public function testUnDepartementSansStrategieUtiliseLaConfigurationLibre(): void
    {
        $departments = $this->createMock(DepartmentsTable::class);
        $departments->expects($this->once())
            ->method('get')
            ->willReturn(new Department(['id' => 12]));

        TableRegistry::getTableLocator()->set('Departments', $departments);
        TableRegistry::getTableLocator()->set('CgrCodes', $this->createStub(CgrCodesTable::class));

        $this->assertSame([
            'strategy' => 'FREE',
            'schema' => [],
            'options' => [],
        ], (new CgrResolverService())->getCgrConfigForDepartment(12));
    }

    public function testUneStrategieActiveNormaliseLeSchemaEtGroupeLesCodes(): void
    {
        $strategy = new CgrStrategy([
            'code' => 'MATRIX',
            'definition_json' => json_encode([
                ['type' => ' axe '],
                ['code' => 'niveau'],
                ' secteur ',
                ['type' => '   '],
                [],
            ], JSON_THROW_ON_ERROR),
        ]);
        $departments = $this->createMock(DepartmentsTable::class);
        $departments->expects($this->once())
            ->method('get')
            ->willReturn(new Department(['id' => 12, 'cgr_strategy' => $strategy]));

        $resultSet = new ResultSet([
            new CgrCode(['type' => 'axe', 'code' => 'A1', 'label' => 'Axe un']),
            new CgrCode(['type' => 'NIVEAU', 'code' => 'N2', 'label' => 'Niveau deux']),
        ]);
        $query = $this->createMock(SelectQuery::class);
        $query->expects($this->once())->method('where')->with([
            'CgrCodes.department_id' => 12,
            'CgrCodes.active' => true,
        ])->willReturnSelf();
        $query->expects($this->once())->method('orderBy')->with([
            'CgrCodes.type' => 'ASC',
            'CgrCodes.code' => 'ASC',
        ])->willReturnSelf();
        $query->expects($this->once())->method('all')->willReturn($resultSet);
        $cgrCodes = $this->createMock(CgrCodesTable::class);
        $cgrCodes->expects($this->once())->method('find')->willReturn($query);

        TableRegistry::getTableLocator()->set('Departments', $departments);
        TableRegistry::getTableLocator()->set('CgrCodes', $cgrCodes);

        $this->assertSame([
            'strategy' => 'MATRIX',
            'schema' => ['AXE', 'NIVEAU', 'SECTEUR'],
            'options' => [
                'AXE' => [['code' => 'A1', 'label' => 'A1 - Axe un']],
                'NIVEAU' => [['code' => 'N2', 'label' => 'N2 - Niveau deux']],
            ],
        ], (new CgrResolverService())->getCgrConfigForDepartment(12));
    }
}

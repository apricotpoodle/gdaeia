<?php
declare(strict_types=1);

namespace App\Test\TestCase\Service;

use App\Model\Entity\Department;
use App\Model\Table\CgrCodesTable;
use App\Model\Table\DepartmentsTable;
use App\Service\CgrResolverService;
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
}

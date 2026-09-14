<?php
declare(strict_types=1);

namespace App\Test\TestCase\Model\Table;

use App\Model\Entity\Department;
use App\Model\Table\DepartmentsTable;
use Cake\TestSuite\TestCase;
use ReflectionClass;
use ReflectionMethod;

class DepartmentsTreeSelectTest extends TestCase
{
    public function testTreeSelectFormatKeepsHierarchyAndUsesCodeAsFallbackName(): void
    {
        $child = new Department(['id' => 2, 'code' => 'CHILD']);
        $root = new Department([
            'id' => 1,
            'name' => 'Direction',
            'children' => [$child],
        ]);

        $table = (new ReflectionClass(DepartmentsTable::class))->newInstanceWithoutConstructor();
        $method = new ReflectionMethod(DepartmentsTable::class, 'formatForTreeSelect');

        $result = $method->invoke($table, [$root]);

        $this->assertSame([
            [
                'value' => 1,
                'name' => 'Direction',
                'children' => [['value' => 2, 'name' => 'CHILD']],
            ],
        ], $result);
    }
}

<?php
declare(strict_types=1);

namespace App\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

/**
 * DepartmentsFixture
 */
class DepartmentsFixture extends TestFixture
{
    /**
     * Init method
     *
     * @return void
     */
    public function init(): void
    {
        $this->records = [
            [
                'id' => 1,
                'parent_id' => null,
                'cgr_code_id' => null,
                'lft' => 1,
                'rght' => 4,
                'level' => 1,
                'base' => 1,
                'code' => 'Lorem ipsum dolor sit amet',
                'name' => 'Lorem ipsum dolor sit amet',
                'sort' => 'Lorem ipsum dolor sit amet',
                'department_type_id' => 1,
                'cgr_strategy_id' => null,
                'default_cgr' => 'Lorem ipsum dolor sit amet',
                'current_manager_id' => null,
                'deleted' => '2026-07-03 09:24:22',
                'created' => 1783070662,
                'modified' => 1783070662,
            ],
            [
                'id' => 2,
                'parent_id' => 1,
                'cgr_code_id' => null,
                'lft' => 2,
                'rght' => 3,
                'level' => 2,
                'base' => 1,
                'code' => 'SERVICE-TEST',
                'name' => 'Service de test',
                'sort' => 'Service de test',
                'department_type_id' => 1,
                'cgr_strategy_id' => null,
                'default_cgr' => null,
                'current_manager_id' => null,
                'deleted' => null,
                'created' => 1783070662,
                'modified' => 1783070662,
            ],
        ];
        parent::init();
    }
}

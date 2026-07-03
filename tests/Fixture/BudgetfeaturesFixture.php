<?php
declare(strict_types=1);

namespace App\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

/**
 * BudgetfeaturesFixture
 */
class BudgetfeaturesFixture extends TestFixture
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
                'base' => 1,
                'code' => 'Lorem ipsum do',
                'name' => 'Lorem ipsum dolor sit amet',
                'sort' => 'Lorem ipsum dolor sit amet',
                'deleted' => '2026-07-03 08:16:51',
                'created' => 1783066611,
                'modified' => 1783066611,
            ],
        ];
        parent::init();
    }
}

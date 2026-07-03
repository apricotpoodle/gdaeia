<?php
declare(strict_types=1);

namespace App\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

/**
 * ValidationstatusesFixture
 */
class ValidationstatusesFixture extends TestFixture
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
                'code' => 'Lorem ipsum dolor sit amet',
                'name' => 'Lorem ipsum dolor sit amet',
                'deleted' => '2026-07-03 09:01:35',
                'modified' => 1783069295,
                'created' => 1783069295,
            ],
        ];
        parent::init();
    }
}

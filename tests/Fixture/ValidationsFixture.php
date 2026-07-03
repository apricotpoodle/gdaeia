<?php
declare(strict_types=1);

namespace App\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

/**
 * ValidationsFixture
 */
class ValidationsFixture extends TestFixture
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
                'applicationform_id' => 1,
                'user_id' => 1,
                'role_id' => 1,
                'validated' => '2026-07-03 09:01:34',
                'validationstatus_id' => 1,
                'obs' => 'Lorem ipsum dolor sit amet',
                'deleted' => '2026-07-03 09:01:34',
                'created' => 1783069294,
                'modified' => 1783069294,
            ],
        ];
        parent::init();
    }
}

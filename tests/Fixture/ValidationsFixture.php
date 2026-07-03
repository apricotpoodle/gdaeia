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
                'validated' => '2026-07-03 08:26:51',
                'validationstatus_id' => 1,
                'obs' => 'Lorem ipsum dolor sit amet',
                'deleted' => '2026-07-03 08:26:51',
                'created' => 1783067211,
                'modified' => 1783067211,
            ],
        ];
        parent::init();
    }
}

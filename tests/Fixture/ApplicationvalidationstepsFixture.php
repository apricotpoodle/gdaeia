<?php
declare(strict_types=1);

namespace App\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

/**
 * ApplicationvalidationstepsFixture
 */
class ApplicationvalidationstepsFixture extends TestFixture
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
                'role_id' => 1,
                'validationstatus_id' => 1,
                'comment' => 'Lorem ipsum dolor sit amet',
                'validationsequence_id' => 1,
                'deleted' => '2026-07-03 09:01:25',
                'modified' => 1783069285,
                'created' => 1783069285,
            ],
        ];
        parent::init();
    }
}

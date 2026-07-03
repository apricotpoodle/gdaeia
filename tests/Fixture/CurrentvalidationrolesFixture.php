<?php
declare(strict_types=1);

namespace App\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

/**
 * CurrentvalidationrolesFixture
 */
class CurrentvalidationrolesFixture extends TestFixture
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
                'applicationform_id' => 1,
                'department_id' => 1,
                'validator_role_id' => 1,
                'validation_sequence' => 1,
                'validationstatus_id' => 1,
                'en_cours' => 1,
                'accepted' => 1,
                'rejected' => 1,
            ],
        ];
        parent::init();
    }
}

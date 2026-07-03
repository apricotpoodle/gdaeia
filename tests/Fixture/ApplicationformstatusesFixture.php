<?php
declare(strict_types=1);

namespace App\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

/**
 * ApplicationformstatusesFixture
 */
class ApplicationformstatusesFixture extends TestFixture
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
                'has_validations' => 1,
                'validationstatus_id' => 1,
                'valid_percentage' => 1.5,
                'current_sequence' => 1,
                'en_cours' => 1,
                'accepted' => 1,
                'rejected' => 1,
            ],
        ];
        parent::init();
    }
}

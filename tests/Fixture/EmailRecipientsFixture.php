<?php
declare(strict_types=1);

namespace App\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

/**
 * EmailRecipientsFixture
 */
class EmailRecipientsFixture extends TestFixture
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
                'email_log_id' => 1,
                'recipient_email' => 'Lorem ipsum dolor sit amet',
            ],
        ];
        parent::init();
    }
}

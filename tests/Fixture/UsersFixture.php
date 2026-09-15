<?php
declare(strict_types=1);

namespace App\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

/**
 * UsersFixture
 */
class UsersFixture extends TestFixture
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
                'username' => 'Lorem ipsum dolor sit amet',
                'email' => 'Lorem ipsum dolor sit amet',
                'password' => 'Lorem ipsum dolor sit amet',
                'firstname' => 'Lorem ipsum dolor sit amet',
                'lastname' => 'Lorem ipsum dolor sit amet',
                'token' => 'Lorem ipsum dolor sit amet',
                'issuperuser' => 1,
                'role_id' => 1,
                'token_expires' => 1783069293,
                'deleted' => '2026-07-03 09:01:33',
                'created' => 1783069293,
                'modified' => 1783069293,
            ],
            [
                'id' => 2,
                'username' => 'utilisateur-de-test',
                'email' => 'utilisateur-de-test@example.test',
                'password' => 'Lorem ipsum dolor sit amet',
                'firstname' => 'Utilisateur',
                'lastname' => 'Test',
                'token' => null,
                'issuperuser' => 0,
                'role_id' => 1,
                'token_expires' => null,
                'deleted' => null,
                'created' => 1783069293,
                'modified' => 1783069293,
            ],
        ];
        parent::init();
    }
}

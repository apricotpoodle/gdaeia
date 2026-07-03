<?php
declare(strict_types=1);

namespace App\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

/**
 * FieldAuthorizationsFixture
 */
class FieldAuthorizationsFixture extends TestFixture
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
                'role_id' => 1,
                'resource' => 'Lorem ipsum dolor sit amet',
                'field' => 'Lorem ipsum dolor sit amet',
                'access_level' => 'Lorem ipsum dolor ',
                'created' => 1783069289,
                'modified' => 1783069289,
            ],
        ];
        parent::init();
    }
}

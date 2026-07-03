<?php
declare(strict_types=1);

namespace App\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

/**
 * WorktimesFixture
 */
class WorktimesFixture extends TestFixture
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
                'created' => 1783067213,
                'modified' => 1783067213,
            ],
        ];
        parent::init();
    }
}

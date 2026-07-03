<?php
declare(strict_types=1);

namespace App\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

/**
 * CgrCodesFixture
 */
class CgrCodesFixture extends TestFixture
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
                'department_id' => 1,
                'type' => 'Lorem ipsum dolor sit amet',
                'code' => 'Lorem ipsum do',
                'label' => 'Lorem ipsum dolor sit amet',
                'active' => 1,
                'is_system' => 1,
                'created' => 1783069286,
                'modified' => 1783069286,
            ],
        ];
        parent::init();
    }
}

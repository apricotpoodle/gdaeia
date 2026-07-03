<?php
declare(strict_types=1);

namespace App\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

/**
 * ProfessionalcategoriesFixture
 */
class ProfessionalcategoriesFixture extends TestFixture
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
                'deleted' => '2026-07-03 08:26:49',
                'created' => 1783067209,
                'modified' => 1783067209,
            ],
        ];
        parent::init();
    }
}

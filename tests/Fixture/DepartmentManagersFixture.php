<?php
declare(strict_types=1);

namespace App\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

/** Fixture des associations entre départements et responsables. */
class DepartmentManagersFixture extends TestFixture
{
    /**
     * @var string
     */
    public string $table = 'department_managers';

    /** @return void */
    public function init(): void
    {
        $this->records = [];
        parent::init();
    }
}

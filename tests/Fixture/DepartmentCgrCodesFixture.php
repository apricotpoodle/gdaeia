<?php
declare(strict_types=1);

namespace App\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

/** Fixture des associations entre départements et codes CGR. */
class DepartmentCgrCodesFixture extends TestFixture
{
    /**
     * @var string
     */
    public string $table = 'department_cgr_codes';

    /** @return void */
    public function init(): void
    {
        $this->records = [
            ['department_id' => 1, 'cgr_code_id' => 1],
        ];
        parent::init();
    }
}

<?php
declare(strict_types=1);

namespace App\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

/**
 * ApplicationformsFixture
 */
class ApplicationformsFixture extends TestFixture
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
                'user_id' => 1,
                'cgr' => 'Lorem ipsum dolor sit amet',
                'contracttype_id' => 1,
                'hiringreason_id' => 1,
                'reasonforreplacement' => 'Lorem ipsum dolor sit amet',
                'budgetfeature_id' => 1,
                'jobtitle' => 'Lorem ipsum dolor sit amet',
                'professionalcategory_id' => 1,
                'worktime_id' => 1,
                'workingtimedistribution' => 'Lorem ipsum dolor sit amet',
                'grossremuneration' => 1.5,
                'period_id' => 1,
                'qualification' => 'Lorem ipsum dolor sit amet',
                'begin_at' => '2026-07-03',
                'end_at' => '2026-07-03',
                'applicantname' => 'Lorem ipsum dolor sit amet',
                'yesno_id' => 1,
                'deleted' => '2026-07-03 08:16:37',
                'created' => 1783066597,
                'modified' => 1783066597,
            ],
        ];
        parent::init();
    }
}

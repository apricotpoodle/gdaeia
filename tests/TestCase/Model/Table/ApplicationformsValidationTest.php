<?php
declare(strict_types=1);

namespace App\Test\TestCase\Model\Table;

use App\Model\Table\ApplicationformsTable;
use Cake\TestSuite\TestCase;
use Cake\Validation\Validator;
use ReflectionClass;

class ApplicationformsValidationTest extends TestCase
{
    private ApplicationformsTable $table;

    protected function setUp(): void
    {
        parent::setUp();
        /** @var ApplicationformsTable $table */
        $table = (new ReflectionClass(ApplicationformsTable::class))->newInstanceWithoutConstructor();
        $this->table = $table;
    }

    public function testLesChampsObligatoiresSontRefusesALaCreation(): void
    {
        $errors = $this->validator()->validate(array_fill_keys([
            'department_id', 'user_id', 'contracttype_id', 'hiringreason_id',
            'budgetfeature_id', 'jobtitle', 'professionalcategory_id', 'worktime_id',
            'grossremuneration', 'period_id', 'yesno_id',
        ], null), true);

        foreach (
            [
                'department_id', 'user_id', 'contracttype_id', 'hiringreason_id',
                'budgetfeature_id', 'jobtitle', 'professionalcategory_id', 'worktime_id',
                'grossremuneration', 'period_id', 'yesno_id',
            ] as $field
        ) {
            $this->assertArrayHasKey($field, $errors);
        }
    }

    public function testUneDateDeFinAnterieureALaDateDeDebutEstRefusee(): void
    {
        $errors = $this->validator()->validate([
            'begin_at' => '2026-10-02',
            'end_at' => '2026-10-01',
        ], true);

        $this->assertArrayHasKey('end_at', $errors);
        $this->assertArrayHasKey('greaterThanBegin', $errors['end_at']);
    }

    public function testUneRemunerationInvalideEtUnCollaborateurNegatifSontRefuses(): void
    {
        $errors = $this->validator()->validate([
            'grossremuneration' => 'not-a-decimal',
            'collaborator_id' => -1,
        ], true);

        $this->assertArrayHasKey('grossremuneration', $errors);
        $this->assertArrayHasKey('collaborator_id', $errors);
    }

    private function validator(): Validator
    {
        return $this->table->validationDefault(new Validator());
    }
}

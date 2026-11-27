<?php
declare(strict_types=1);

namespace App\Test\TestCase\Service;

use App\Service\ApplicationformDuplicationSettings;
use Cake\TestSuite\TestCase;

final class ApplicationformDuplicationSettingsTest extends TestCase
{
    public function testLesSixChampsConstituentLaValeurSureParDefaut(): void
    {
        $this->assertSame(
            [
                'hr_description',
                'control_management_validation',
                'final_argument',
                'budgetfeature_id',
                'yesno_id',
                'qualification',
            ],
            ApplicationformDuplicationSettings::resolveResetFields(null),
        );
    }

    public function testUneConfigurationValideEstResolueDansSonOrdre(): void
    {
        $this->assertSame(
            ['final_argument', 'qualification'],
            ApplicationformDuplicationSettings::resolveResetFields('final_argument,qualification'),
        );
    }

    public function testUneConfigurationAbsenteOuInvalideRetombeSurLaValeurSure(): void
    {
        foreach ([null, '', 'final_argument,,qualification', 'unknown', 'user_id'] as $value) {
            $this->assertSame(
                ApplicationformDuplicationSettings::CONFIGURABLE_FIELDS,
                ApplicationformDuplicationSettings::resolveResetFields($value),
                'Configuration testée : ' . ($value ?? 'absente'),
            );
        }
    }

    public function testLesListesSoumisesDoiventEtreNonVidesEtEntierementConfigurables(): void
    {
        foreach ([[], ['unknown'], ['hr_description', 'user_id'], 'hr_description'] as $fields) {
            $errors = ApplicationformDuplicationSettings::validateResetFields($fields);

            $this->assertArrayHasKey('fields', $errors);
            $this->assertNotSame([], $errors['fields']);
        }
    }

    public function testUneListeValideEstAccepteeEtSerialiseeEnCsv(): void
    {
        $fields = ['hr_description', 'qualification'];

        $this->assertSame([], ApplicationformDuplicationSettings::validateResetFields($fields));
        $this->assertSame('hr_description,qualification', ApplicationformDuplicationSettings::serializeResetFields($fields));
    }
}

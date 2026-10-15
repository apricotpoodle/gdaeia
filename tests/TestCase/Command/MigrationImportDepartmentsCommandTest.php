<?php
declare(strict_types=1);

namespace App\Test\TestCase\Command;

use App\Command\MigrationImportDepartmentsCommand;
use Cake\Console\Arguments;
use PHPUnit\Framework\Attributes\CoversClass;

#[CoversClass(MigrationImportDepartmentsCommand::class)]
final class MigrationImportDepartmentsCommandTest extends CommandTestCase
{
    public function testDryRunAfficheLePlanSansAccederALaBase(): void
    {
        $directory = $this->createCsvDirectory([
            '07-reference-companies.csv' => "source_company_id,source_company_code,source_company_name,source_company_order\n5,SEM,Société Éditrice du Monde,1\n",
            '08-reference-services.csv' => "source_service_id,source_service_code,source_service_name,source_service_order\n1,DEV EDIT,Développement Éditorial,DEV EDIT\n2,OP spé,Opérations spéciales,OP spé\n",
            '05-user-service-visibility.csv' => "source_user_id,source_company_id,source_company_code,source_company_name,source_company_service_id\n10,5,SEM,Société Éditrice du Monde,1\n",
            '06-user-cgr-service-scope.csv' => "source_assistant_user_id,source_service_manager_user_id,source_company_id,source_company_code,source_service_id,source_service_code,source_service_name,source_analytic_code_id,source_analytic_code,source_analytic_name\n10,20,5,SEM,1,DEV EDIT,Développement Éditorial,126,AAN02,AAN02 : Développement éditorial\n",
        ]);
        $streams = $this->createIo();

        $result = (new MigrationImportDepartmentsCommand())->execute(
            new Arguments([], ['source-dir' => $directory, 'dry-run' => true], []),
            $streams['io'],
        );

        $this->assertSame(MigrationImportDepartmentsCommand::CODE_SUCCESS, $result);
        $output = $this->streamContents($streams['out']);
        $this->assertStringContainsString('Sociétés : 1', $output);
        $this->assertStringContainsString('Services rapprochés : 1', $output);
        $this->assertStringContainsString('CGR distincts : 1', $output);
        $this->assertStringContainsString('Affectations CGR : 1', $output);
        $this->assertStringContainsString('Services non rapprochés : 1', $output);
    }

    /** @param array<string, string> $files */
    private function createCsvDirectory(array $files): string
    {
        $directory = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'gdaetf2-department-command-' . bin2hex(random_bytes(4));
        mkdir($directory, 0770, true);
        foreach ($files as $name => $contents) {
            file_put_contents($directory . DIRECTORY_SEPARATOR . $name, $contents);
        }

        return $directory;
    }
}

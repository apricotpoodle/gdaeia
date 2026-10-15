<?php
declare(strict_types=1);

namespace App\Test\TestCase\Service\Migration;

use App\Service\Migration\DepartmentImportService;
use Cake\TestSuite\TestCase;
use RuntimeException;

final class DepartmentImportServiceTest extends TestCase
{
    public function testPrepareConstruitLesSocietesServicesEtCgrSansDoublons(): void
    {
        $directory = $this->createCsvDirectory([
            '07-reference-companies.csv' => implode("\n", [
                'source_company_id,source_company_code,source_company_name,source_company_order',
                '5,SEM,Société Éditrice du Monde,1',
                '6,TRA,Télérama,0',
                '',
            ]),
            '08-reference-services.csv' => implode("\n", [
                'source_service_id,source_service_code,source_service_name,source_service_order',
                '1,DEV EDIT,Développement Éditorial,DEV EDIT',
                '2,OP spé,Opérations spéciales,OP spé',
                '3,CORR,CORRECTION,CORR',
                '4,DOC,DOCUMENTATION,DOC',
                '',
            ]),
            '05-user-service-visibility.csv' => implode("\n", [
                'source_user_id,source_company_id,source_company_code,source_company_name,source_company_service_id',
                '10,5,SEM,Société Éditrice du Monde,1',
                '11,6,TRA,Télérama,3',
                '',
            ]),
            '06-user-cgr-service-scope.csv' => implode("\n", [
                'source_assistant_user_id,source_service_manager_user_id,source_company_id,source_company_code,source_service_id,source_service_code,source_service_name,source_analytic_code_id,source_analytic_code,source_analytic_name',
                '10,20,5,SEM,1,DEV EDIT,Développement Éditorial,126,AAN02,AAN02 : Développement éditorial',
                '11,21,5,SEM,1,DEV EDIT,Développement Éditorial,126,AAN02,AAN02 : Développement éditorial',
                '11,21,5,SEM,2,OP spé,Opérations spéciales,127,AAN03,AAN03 : Opérations spéciales',
                '',
            ]),
        ]);

        $plan = (new DepartmentImportService())->prepare($directory);

        $this->assertCount(2, $plan['companies']);
        $this->assertCount(3, $plan['services']);
        $this->assertSame(2, $plan['cgr_count']);
        $this->assertSame(2, $plan['cgr_assignment_count']);
        $this->assertSame(1, $plan['unmapped_service_count']);
        $this->assertSame('SEM-SRV-1', $plan['services'][0]['target_code']);
        $this->assertSame('SERVICE', $plan['services'][0]['cgrs'][0]['type']);
    }

    public function testPrepareRefuseUnExportObligatoireAbsent(): void
    {
        $directory = $this->createCsvDirectory([
            '07-reference-companies.csv' => "source_company_id,source_company_code,source_company_name,source_company_order\n",
        ]);

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Fichier CSV obligatoire absent');

        (new DepartmentImportService())->prepare($directory);
    }

    /** @param array<string, string> $files */
    private function createCsvDirectory(array $files): string
    {
        $directory = sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'gdaetf2-department-import-' . bin2hex(random_bytes(4));
        mkdir($directory, 0770, true);
        foreach ($files as $name => $contents) {
            file_put_contents($directory . DIRECTORY_SEPARATOR . $name, $contents);
        }

        return $directory;
    }
}

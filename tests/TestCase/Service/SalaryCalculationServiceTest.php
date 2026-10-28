<?php
declare(strict_types=1);

namespace App\Test\TestCase\Service;

use App\Service\SalaryCalculationService;
use InvalidArgumentException;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class SalaryCalculationServiceTest extends TestCase
{
    #[DataProvider('unitCalculations')]
    public function testCalculeLeSalaireDeBaseSelonLUnite(
        string $unit,
        string $amount,
        ?int $workingDays,
        string $expected,
    ): void {
        $service = new SalaryCalculationService();

        $this->assertSame($expected, $service->calculateBaseSalary($unit, $amount, $workingDays));
    }

    /** @return iterable<string, array{string, string, int|null, string}> */
    public static function unitCalculations(): iterable
    {
        yield 'euros par jour' => ['EUR_PER_DAY', '125,50', 4, '502.0000'];
        yield 'milliers par mois' => ['K_PER_MONTH', '2,5', null, '43.6500'];
        yield 'euros par mois' => ['EUR_PER_MONTH', '2450,75', null, '2450.7500'];
    }

    public function testRefuseLeCalculHoraireNonDefini(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Le calcul horaire n’est pas configuré.');

        (new SalaryCalculationService())->calculateBaseSalary('EUR_PER_HOUR', '25', 1);
    }

    public function testRefuseUnePigeSansNombreDeJours(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('Le nombre de jours travaillés est obligatoire.');

        (new SalaryCalculationService())->calculateBaseSalary('EUR_PER_DAY', '125', null);
    }

    public function testCalculeDepuisLeCodeDePeriodicite(): void
    {
        $service = new SalaryCalculationService();

        $this->assertSame(
            '502.0000',
            $service->calculateForPeriodCode('EUR_PER_DAY', '125,50', 4),
        );
    }
}

<?php
declare(strict_types=1);

namespace App\Service;

use App\Model\Entity\Period;
use InvalidArgumentException;

/** Calcule le salaire de base à partir de l'unité de rémunération choisie. */
final class SalaryCalculationService
{
    public const DEFAULT_K_COEFFICIENT = 17.46;

    /**
     * Calcule un montant décimal formaté sur quatre décimales.
     *
     * @param string $unit Unité de rémunération normalisée.
     * @param string|float|int $amount Montant saisi, avec point ou virgule.
     * @param int|null $workingDays Nombre de jours travaillés pour une pige.
     * @param float $kCoefficient Coefficient de conversion des milliers mensuels.
     * @return string Montant décimal prêt à être persisté.
     */
    public function calculateBaseSalary(
        string $unit,
        string|float|int $amount,
        ?int $workingDays = null,
        float $kCoefficient = self::DEFAULT_K_COEFFICIENT,
    ): string {
        $normalizedAmount = $this->decimalValue($amount);

        $baseSalary = match ($unit) {
            Period::CODE_EUR_PER_DAY => $this->calculatePerDay($normalizedAmount, $workingDays),
            Period::CODE_K_PER_MONTH => $normalizedAmount * $kCoefficient,
            Period::CODE_EUR_PER_MONTH => $normalizedAmount,
            Period::CODE_EUR_PER_HOUR => throw new InvalidArgumentException(
                'Le calcul horaire n’est pas configuré.',
            ),
            default => throw new InvalidArgumentException('Unité de rémunération inconnue.'),
        };

        return number_format($baseSalary, 4, '.', '');
    }

    /** Calcule le salaire depuis le code stable du référentiel des périodicités. */
    public function calculateForPeriodCode(
        string $periodCode,
        string|float|int $amount,
        ?int $workingDays = null,
        float $kCoefficient = self::DEFAULT_K_COEFFICIENT,
    ): string {
        return $this->calculateBaseSalary($periodCode, $amount, $workingDays, $kCoefficient);
    }

    /** Retourne le montant journalier total après contrôle du nombre de jours. */
    private function calculatePerDay(float $amount, ?int $workingDays): float
    {
        if ($workingDays === null) {
            throw new InvalidArgumentException('Le nombre de jours travaillés est obligatoire.');
        }
        if ($workingDays < 0) {
            throw new InvalidArgumentException('Le nombre de jours travaillés doit être positif.');
        }

        return $amount * $workingDays;
    }

    /** Convertit une saisie décimale française ou internationale en nombre. */
    private function decimalValue(string|float|int $value): float
    {
        if (is_string($value)) {
            $value = str_replace(',', '.', trim($value));
        }
        if ($value === '' || !is_numeric($value)) {
            throw new InvalidArgumentException('Le montant de rémunération doit être décimal.');
        }

        return (float)$value;
    }
}

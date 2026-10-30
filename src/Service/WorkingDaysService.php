<?php
declare(strict_types=1);

namespace App\Service;

use DateTimeImmutable;
use DateTimeInterface;

/** Calcule les durées calendaires et les jours ouvrés d'une période inclusive. */
final class WorkingDaysService
{
    /**
     * Compte les jours du lundi au vendredi entre deux dates incluses.
     *
     * @param \DateTimeInterface $begin Date de début incluse.
     * @param \DateTimeInterface $end Date de fin incluse.
     * @return int Nombre de jours ouvrés.
     */
    public function countWorkingDays(DateTimeInterface $begin, DateTimeInterface $end): int
    {
        $workingDays = 0;
        $beginDate = DateTimeImmutable::createFromInterface($begin)->setTime(0, 0);
        $endDate = DateTimeImmutable::createFromInterface($end)->setTime(0, 0);
        for ($date = $beginDate; $date <= $endDate; $date = $date->modify('+1 day')) {
            if ((int)$date->format('N') <= 5) {
                $workingDays++;
            }
        }

        return $workingDays;
    }

    /**
     * Compte les jours calendaires entre deux dates incluses.
     *
     * @param \DateTimeInterface $begin Date de début incluse.
     * @param \DateTimeInterface $end Date de fin incluse.
     * @return int Nombre de jours calendaires.
     */
    public function countCalendarDays(DateTimeInterface $begin, DateTimeInterface $end): int
    {
        $beginDate = DateTimeImmutable::createFromInterface($begin)->setTime(0, 0);
        $endDate = DateTimeImmutable::createFromInterface($end)->setTime(0, 0);

        return (int)$beginDate->diff($endDate)->days + 1;
    }
}

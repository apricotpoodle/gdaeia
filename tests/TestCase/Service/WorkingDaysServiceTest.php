<?php
declare(strict_types=1);

namespace App\Test\TestCase\Service;

use App\Service\WorkingDaysService;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;

class WorkingDaysServiceTest extends TestCase
{
    public function testCalculeLesJoursOuvresInclusifsSansCompterLeWeekend(): void
    {
        $service = new WorkingDaysService();

        $this->assertSame(
            5,
            $service->countWorkingDays(
                new DateTimeImmutable('2026-10-05'),
                new DateTimeImmutable('2026-10-11'),
            ),
        );
    }

    public function testCalculeLeNombreDeJoursCalendairesInclusifs(): void
    {
        $service = new WorkingDaysService();

        $this->assertSame(
            7,
            $service->countCalendarDays(
                new DateTimeImmutable('2026-10-05'),
                new DateTimeImmutable('2026-10-11'),
            ),
        );
    }
}

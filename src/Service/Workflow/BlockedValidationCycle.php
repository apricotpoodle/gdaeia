<?php
declare(strict_types=1);

namespace App\Service\Workflow;

use App\Model\Entity\Applicationform;
use App\Model\Entity\Department;
use Cake\I18n\Date;
use Cake\I18n\DateTime;
use InvalidArgumentException;

/** Résultat regroupé d'une DAE comportant une ou plusieurs étapes bloquées. */
final readonly class BlockedValidationCycle
{
    public DateTime $activatedAt;
    public DateTime $blockedSince;
    public int $businessDays;

    /** @param list<\App\Service\Workflow\BlockedValidationStep> $blockedSteps */
    public function __construct(
        public Applicationform $applicationform,
        public int $applicationformNumber,
        public ?Date $beginAt,
        public Department $department,
        public array $blockedSteps,
        public string $url,
    ) {
        if ($this->blockedSteps === []) {
            throw new InvalidArgumentException('Un cycle bloqué doit contenir au moins une étape.');
        }
        $first = $this->blockedSteps[0];
        $this->activatedAt = $first->activatedAt;
        $this->blockedSince = $first->blockedSince;
        $this->businessDays = max(array_map(
            static fn(BlockedValidationStep $step): int => $step->businessDays,
            $this->blockedSteps,
        ));
    }
}

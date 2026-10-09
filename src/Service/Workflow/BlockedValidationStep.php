<?php
declare(strict_types=1);

namespace App\Service\Workflow;

use App\Model\Entity\Applicationvalidationstep;
use App\Model\Entity\Role;
use Cake\I18n\DateTime;

/** Données d'une étape identifiée comme bloquée. */
final readonly class BlockedValidationStep
{
    /** @param \App\Model\Entity\Applicationvalidationstep $step Étape bloquée. */
    public function __construct(
        public Applicationvalidationstep $step,
        public Role $role,
        public DateTime $activatedAt,
        public DateTime $blockedSince,
        public int $businessDays,
    ) {
    }
}

<?php
declare(strict_types=1);

namespace App\Model\Entity;

use Cake\ORM\Entity;

/** Exécution immuable du cycle de validation d'une Applicationform. */
final class ValidationWorkflowRun extends Entity
{
    public const STATE_PENDING = 'en_attente';
    public const STATE_ACCEPTED = 'acceptee';
    public const STATE_REJECTED = 'refusee';
    public const STATE_CANCELLED = 'annulee';

    protected array $_accessible = [
        'applicationform_id' => true, 'started_by_user_id' => true, 'state' => true,
        'started_at' => true, 'finished_at' => true, 'created' => true, 'modified' => true,
    ];
}

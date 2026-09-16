<?php
declare(strict_types=1);

namespace App\Service\Workflow;

use RuntimeException;

/** Échec technique lors de l'initialisation transactionnelle d'un cycle de validation. */
final class WorkflowStartFailureException extends RuntimeException
{
}

<?php
declare(strict_types=1);

namespace App\View\Action;

/** Fabrique des commandes d’interface de configuration des séquences. */
final class ValidationsequencesActions
{
    /** Accède au paramétrage global du workflow. */
    public static function workflowSettings(): UiAction
    {
        return new UiAction(
            UiAction::TYPE_LINK,
            __('Paramétrage global'),
            'fa-sliders',
            ['controller' => 'WorkflowSettings', 'action' => 'index'],
            'index',
            'Validationsequences',
            ['class' => 'btn btn-outline-secondary'],
        );
    }
}

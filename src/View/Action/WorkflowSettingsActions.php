<?php
declare(strict_types=1);

namespace App\View\Action;

/** Fabrique des commandes d’interface du paramétrage global du workflow. */
final class WorkflowSettingsActions
{
    /** Retourne vers la configuration des séquences départementales. */
    public static function validationSequences(): UiAction
    {
        return new UiAction(
            UiAction::TYPE_LINK,
            __('Configurer les séquences'),
            'fa-list-ol',
            ['controller' => 'Validationsequences', 'action' => 'index'],
            'index',
            'WorkflowSettings',
            ['class' => 'btn btn-outline-secondary'],
        );
    }

    /** Enregistre le délai global depuis l’écran de paramétrage. */
    public static function saveDefaultDueHours(): UiAction
    {
        return new UiAction(
            UiAction::TYPE_BUTTON,
            __('Enregistrer'),
            'fa-floppy-disk',
            '#',
            'manage',
            'WorkflowSettings',
            ['class' => 'btn btn-primary', 'id' => 'save-validation-default-due-hours'],
        );
    }

    /** Enregistre un commentaire prédéfini depuis l’écran de paramétrage. */
    public static function saveCommentTemplate(): UiAction
    {
        return new UiAction(
            UiAction::TYPE_BUTTON,
            __('Enregistrer le commentaire'),
            'fa-floppy-disk',
            '#',
            'add',
            'ValidationCommentTemplates',
            ['class' => 'btn btn-primary', 'id' => 'save-validation-comment-template'],
        );
    }
}

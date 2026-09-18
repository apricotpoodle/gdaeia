<?php
declare(strict_types=1);

namespace App\View\Action;

use App\Model\Entity\Applicationform;

/** Fabrique des commandes d'interface du domaine des demandes. */
final class ApplicationformsActions
{
    /** @return \App\View\Action\UiAction Commande de création d'une demande. */
    public static function add(): UiAction
    {
        return new UiAction(
            UiAction::TYPE_LINK,
            __('Nouvelle demande'),
            'fa-plus',
            ['action' => 'add'],
            'add',
            'Applicationforms',
            ['class' => 'btn btn-primary'],
        );
    }

    /**
     * @param \App\Model\Entity\Applicationform $applicationform Demande à modifier.
     * @return \App\View\Action\UiAction Commande d'édition contextuelle.
     */
    public static function edit(Applicationform $applicationform, bool $requiresConfirmation = false): UiAction
    {
        return new UiAction(
            UiAction::TYPE_LINK,
            __('Éditer'),
            'fa-pen',
            ['action' => 'edit', $applicationform->id],
            'edit',
            $applicationform,
            [
                'class' => 'btn btn-sm btn-primary',
                'confirm' => $requiresConfirmation
                    ? __('Vous allez modifier une demande en cours de validation. Confirmez-vous cette opération ?')
                    : null,
            ],
        );
    }

    /**
     * @param \App\Model\Entity\Applicationform $applicationform Demande à consulter.
     * @return \App\View\Action\UiAction Commande de consultation contextuelle.
     */
    public static function view(Applicationform $applicationform): UiAction
    {
        return new UiAction(
            UiAction::TYPE_LINK,
            __('Consulter'),
            'fa-eye',
            ['action' => 'view', $applicationform->id],
            'view',
            $applicationform,
            ['class' => 'btn btn-outline-info btn-sm'],
        );
    }

    /**
     * @param \App\Model\Entity\Applicationform $applicationform Demande à supprimer.
     * @return \App\View\Action\UiAction Commande POST de suppression contextuelle.
     */
    public static function delete(Applicationform $applicationform, bool $requiresConfirmation = false): UiAction
    {
        return new UiAction(
            UiAction::TYPE_POST_LINK,
            __('Supprimer'),
            'fa-trash',
            ['action' => 'delete', $applicationform->id],
            'delete',
            $applicationform,
            [
                'confirm' => $requiresConfirmation
                    ? __(
                        '⚠️ La demande n° {0}, ses votes, ses étapes et son cycle seront définitivement supprimés. '
                        . 'Confirmez-vous cette opération ?',
                        $applicationform->id,
                    )
                    : __('⚠️ Supprimer la demande n° {0} ?', $applicationform->id),
                'class' => 'btn btn-sm btn-outline-danger',
            ],
        );
    }

    /** Commande de lancement API du cycle de validation. */
    public static function launchValidation(Applicationform $applicationform): UiAction
    {
        return new UiAction(
            UiAction::TYPE_BUTTON,
            __('Lancer la validation'),
            'fa-rocket',
            '#',
            'launchValidation',
            $applicationform,
            [
                'class' => 'btn btn-sm btn-success',
                'id' => 'launch-validation',
                'data-applicationform-id' => $applicationform->id,
            ],
        );
    }

    /** Commande de remise à zéro administrative du cycle. */
    public static function resetValidation(Applicationform $applicationform): UiAction
    {
        return new UiAction(
            UiAction::TYPE_BUTTON,
            __('Annuler et réinitialiser le cycle'),
            'fa-rotate-left',
            '#',
            'resetValidation',
            $applicationform,
            ['class' => 'btn btn-sm btn-outline-warning', 'id' => 'reset-validation'],
        );
    }

    /**
     * @param string $label Libellé du lien de retour.
     * @param string $class Classes CSS Bootstrap.
     * @return \App\View\Action\UiAction Commande de retour vers la liste des demandes.
     */
    public static function index(string $label = 'Retour', string $class = 'btn btn-sm btn-outline-secondary'): UiAction
    {
        return new UiAction(
            UiAction::TYPE_LINK,
            __($label),
            'fa-arrow-left',
            ['action' => 'index'],
            'index',
            'Applicationforms',
            ['class' => $class],
        );
    }
}

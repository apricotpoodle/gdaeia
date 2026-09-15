<?php
declare(strict_types=1);

namespace App\View\Action;

use App\Model\Entity\User;

/** Fabrique des commandes d'interface du domaine des utilisateurs. */
final class UsersActions
{
    /** @return \App\View\Action\UiAction Commande d'association groupée des départements. */
    public static function bulkDepartments(): UiAction
    {
        return new UiAction(
            UiAction::TYPE_LINK,
            __('Associer des départements'),
            'fa-users-gear',
            ['action' => 'bulkDepartments'],
            'add',
            'Users',
            ['class' => 'btn btn-primary'],
        );
    }

    /**
     * @param \App\Model\Entity\User $user Utilisateur à modifier.
     * @return \App\View\Action\UiAction Commande d'édition contextuelle.
     */
    public static function edit(User $user): UiAction
    {
        return new UiAction(
            UiAction::TYPE_LINK,
            __('Éditer'),
            'fa-pen-to-square',
            ['action' => 'edit', $user->id],
            'edit',
            $user,
            ['class' => 'btn btn-light btn-sm shadow-sm'],
        );
    }

    /**
     * @param \App\Model\Entity\User $user Utilisateur à incarner.
     * @return \App\View\Action\UiAction Commande d'usurpation contextuelle.
     */
    public static function impersonate(User $user): UiAction
    {
        return new UiAction(
            UiAction::TYPE_LINK,
            __('Incarner'),
            'fa-user-secret',
            ['action' => 'impersonate', $user->id],
            'impersonate',
            $user,
            [
                'class' => 'btn btn-warning btn-sm shadow-sm',
                'confirm' => __('Voulez-vous vraiment vous connecter sous l’identité de {0} ?', $user->display_name),
            ],
        );
    }

    /**
     * @param string $label Libellé du lien de retour.
     * @param string $class Classes CSS Bootstrap.
     * @return \App\View\Action\UiAction Commande de retour vers la liste des utilisateurs.
     */
    public static function index(string $label = 'Retour à la liste', string $class = 'btn btn-light btn-sm'): UiAction
    {
        return new UiAction(
            UiAction::TYPE_LINK,
            __($label),
            'fa-arrow-left',
            ['action' => 'index'],
            'index',
            'Users',
            ['class' => $class],
        );
    }
}

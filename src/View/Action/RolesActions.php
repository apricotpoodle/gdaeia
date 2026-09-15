<?php
declare(strict_types=1);

namespace App\View\Action;

use App\Model\Entity\Role;

/** Fabrique des commandes d'interface du domaine des rôles. */
final class RolesActions
{
    /** @return \App\View\Action\UiAction Commande de création d'un rôle. */
    public static function add(): UiAction
    {
        return new UiAction(
            UiAction::TYPE_LINK,
            __('Nouveau rôle'),
            'fa-plus',
            ['action' => 'add'],
            'add',
            'Roles',
            ['class' => 'btn btn-primary'],
        );
    }

    /** @return \App\View\Action\UiAction Commande de retour vers l'index. */
    public static function index(
        string $label = 'Retour à la liste',
        string $class = 'btn btn-outline-secondary',
    ): UiAction {
        return new UiAction(
            UiAction::TYPE_LINK,
            __($label),
            'fa-arrow-left',
            ['action' => 'index'],
            'index',
            'Roles',
            ['class' => $class],
        );
    }

    /** @return \App\View\Action\UiAction Commande d'édition contextuelle. */
    public static function edit(Role $role): UiAction
    {
        return new UiAction(
            UiAction::TYPE_LINK,
            __('Éditer'),
            'fa-pen-to-square',
            ['action' => 'edit', $role->id],
            'edit',
            $role,
            ['class' => 'btn btn-primary'],
        );
    }
}

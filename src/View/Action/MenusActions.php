<?php
declare(strict_types=1);

namespace App\View\Action;

/** Fabrique des commandes d'interface du domaine des options de menu. */
final class MenusActions
{
    /** @return \App\View\Action\UiAction Commande d'accès à la matrice rôles-menus. */
    public static function roleAccess(): UiAction
    {
        return new UiAction(
            UiAction::TYPE_LINK,
            __('Associer aux rôles'),
            'fa-user-tag',
            ['action' => 'roleAccess'],
            'roleAccess',
            'Menus',
            ['class' => 'btn btn-outline-primary'],
        );
    }

    /** @return \App\View\Action\UiAction Commande de création d'une option de menu. */
    public static function add(): UiAction
    {
        return new UiAction(
            UiAction::TYPE_LINK,
            __('Nouveau Menu'),
            'fa-plus',
            ['action' => 'add'],
            'add',
            'Menus',
            ['class' => 'btn btn-primary'],
        );
    }

    /**
     * @param string $label Libellé du lien de retour.
     * @return \App\View\Action\UiAction Commande de retour vers la liste des menus.
     */
    public static function index(string $label = 'Retour aux menus'): UiAction
    {
        return new UiAction(
            UiAction::TYPE_LINK,
            __($label),
            null,
            ['action' => 'index'],
            'index',
            'Menus',
            ['class' => 'btn btn-outline-secondary btn-sm'],
        );
    }
}

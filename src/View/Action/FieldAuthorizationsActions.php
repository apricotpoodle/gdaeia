<?php
declare(strict_types=1);

namespace App\View\Action;

/** Fabrique des commandes d'interface du domaine des autorisations de champ. */
final class FieldAuthorizationsActions
{
    /** @return \App\View\Action\UiAction Commande de création d'une règle. */
    public static function add(): UiAction
    {
        return new UiAction(
            UiAction::TYPE_LINK,
            __('Nouvelle règle'),
            'fa-plus',
            ['action' => 'add'],
            'add',
            'FieldAuthorizations',
            ['class' => 'btn btn-primary'],
        );
    }

    /** @return \App\View\Action\UiAction Commande de retour vers la liste des autorisations. */
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
            'FieldAuthorizations',
            ['class' => $class],
        );
    }
}

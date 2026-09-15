<?php
declare(strict_types=1);

namespace App\View\Action;

/** Fabrique des commandes d'interface du domaine des autorisations de champ. */
final class FieldAuthorizationsActions
{
    /** @return \App\View\Action\UiAction Commande de retour vers la liste des autorisations. */
    public static function index(): UiAction
    {
        return new UiAction(
            UiAction::TYPE_LINK,
            __('Retour à la liste'),
            null,
            ['action' => 'index'],
            'index',
            'FieldAuthorizations',
            ['class' => 'btn btn-outline-secondary'],
        );
    }
}

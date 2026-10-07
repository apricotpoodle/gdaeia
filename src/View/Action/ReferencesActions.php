<?php
declare(strict_types=1);

namespace App\View\Action;

/** Fabrique des commandes communes aux écrans de nomenclatures. */
final class ReferencesActions
{
    /** Construit la commande de création d’une référence. */
    public static function add(string $controller): UiAction
    {
        return new UiAction(
            UiAction::TYPE_LINK,
            __('Nouvelle référence'),
            'fa-plus',
            ['controller' => $controller, 'action' => 'add'],
            'add',
            $controller,
            ['class' => 'btn btn-primary'],
        );
    }

    /** Construit la commande de retour vers l’index. */
    public static function index(string $controller, string $label = 'Retour aux références'): UiAction
    {
        return new UiAction(
            UiAction::TYPE_LINK,
            __($label),
            'fa-arrow-left',
            ['controller' => $controller, 'action' => 'index'],
            'index',
            $controller,
            ['class' => 'btn btn-outline-secondary'],
        );
    }
}

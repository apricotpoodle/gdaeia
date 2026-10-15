<?php
declare(strict_types=1);

namespace App\View\Action;

/** Fabrique des commandes d’interface de l’administration des départements. */
final class DepartmentsActions
{
    /** @return \App\View\Action\UiAction Commande d’ajout. */
    public static function add(): UiAction
    {
        return new UiAction(
            UiAction::TYPE_LINK,
            __('Nouveau département'),
            'fa-plus',
            ['action' => 'add'],
            'add',
            'Departments',
            ['class' => 'btn btn-primary'],
        );
    }

    /** @return \App\View\Action\UiAction Retour à l’index. */
    public static function index(): UiAction
    {
        return new UiAction(
            UiAction::TYPE_LINK,
            __('Retour à l’arbre'),
            'fa-arrow-left',
            ['action' => 'index'],
            'index',
            'Departments',
            ['class' => 'btn btn-outline-secondary'],
        );
    }
}

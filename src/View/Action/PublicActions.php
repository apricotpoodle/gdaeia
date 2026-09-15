<?php
declare(strict_types=1);

namespace App\View\Action;

/** Fabrique des commandes de navigation publique, sans contrôle de Policy. */
final class PublicActions
{
    /** @return \App\View\Action\UiAction Commande publique vers l'accueil applicatif. */
    public static function home(): UiAction
    {
        return new UiAction(
            UiAction::TYPE_LINK,
            'GDAETF2',
            'fa-shield-halved text-danger me-2',
            '/',
            '',
            null,
            ['class' => 'navbar-brand fw-bold mb-0 h1'],
            false,
        );
    }

    /** @return \App\View\Action\UiAction Commande publique de récupération de mot de passe. */
    public static function forgotPassword(): UiAction
    {
        return new UiAction(
            UiAction::TYPE_LINK,
            __('Mot de passe oublié ?'),
            null,
            ['controller' => 'Users', 'action' => 'forgotPassword'],
            '',
            null,
            ['class' => 'small text-primary text-decoration-none fw-semibold'],
            false,
        );
    }

    /** @return \App\View\Action\UiAction Commande publique de retour à la connexion. */
    public static function login(): UiAction
    {
        return new UiAction(
            UiAction::TYPE_LINK,
            __('Retour à la connexion'),
            'fa-arrow-left',
            ['controller' => 'Users', 'action' => 'login'],
            '',
            null,
            ['class' => 'small text-muted text-decoration-none fw-semibold'],
            false,
        );
    }
}

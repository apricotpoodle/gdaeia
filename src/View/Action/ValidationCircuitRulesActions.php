<?php
declare(strict_types=1);

namespace App\View\Action;

/** Commandes autorisées de gestion des circuits courts et de leurs habilitations. */
final class ValidationCircuitRulesActions
{
    /** Accède aux règles locales depuis un écran de configuration. */
    public static function index(): UiAction
    {
        return self::indexLink('btn btn-outline-secondary');
    }

    /** Accède au domaine depuis la navigation indépendante des menus dynamiques. */
    public static function navigation(): UiAction
    {
        return self::indexLink('nav-link');
    }

    /** Enregistre un lot de nouvelles règles ou une modification unitaire. */
    public static function saveRule(): UiAction
    {
        return self::command(__('Enregistrer les règles'), 'fa-floppy-disk', 'save-validation-circuit-rule');
    }

    /** Prévisualise les séquences et les problèmes des départements sélectionnés. */
    public static function preview(): UiAction
    {
        return self::command(__('Prévisualiser les circuits'), 'fa-eye', 'preview-validation-circuit-rule');
    }

    /** Revient au mode de création et vide la sélection explicite. */
    public static function newRule(): UiAction
    {
        return self::command(
            __('Nouvelle sélection'),
            'fa-rotate-left',
            'new-validation-circuit-rule',
            'btn-outline-secondary',
        );
    }

    /** Attribue une habilitation de gestion, distincte du rôle final d’un circuit. */
    public static function assignManagers(): UiAction
    {
        return self::command(
            __('Habiliter le rôle pour la sélection'),
            'fa-user-shield',
            'assign-validation-circuit-managers',
            'btn-primary',
            'manageGrants',
        );
    }

    /**
     * Construit le même point d’entrée avec sa présentation contextuelle.
     *
     * @param string $class Classes CSS du lien.
     * @return \App\View\Action\UiAction
     */
    private static function indexLink(string $class): UiAction
    {
        return new UiAction(
            UiAction::TYPE_LINK,
            __('Circuits courts locaux'),
            'fa-route',
            ['controller' => 'ValidationCircuitRules', 'action' => 'index'],
            'index',
            'ValidationCircuitRules',
            ['class' => $class],
        );
    }

    /**
     * Construit une commande du formulaire avec son autorisation serveur associée.
     *
     * @param string $label Libellé français.
     * @param string $icon Icône Font Awesome.
     * @param string $id Identifiant DOM consommé par le module de page.
     * @param string $variant Variante Bootstrap.
     * @param string $authorizationAction Droit du domaine à évaluer.
     * @return \App\View\Action\UiAction
     */
    private static function command(
        string $label,
        string $icon,
        string $id,
        string $variant = 'btn-primary',
        string $authorizationAction = 'manage',
    ): UiAction {
        return new UiAction(
            UiAction::TYPE_BUTTON,
            $label,
            $icon,
            '#',
            $authorizationAction,
            'ValidationCircuitRules',
            ['class' => 'btn ' . $variant, 'id' => $id],
        );
    }
}

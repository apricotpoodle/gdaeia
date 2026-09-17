<?php
declare(strict_types=1);

namespace App\View\Action;

/**
 * Value object immuable décrivant une action d'interface.
 *
 * La fabrique de domaine choisit la route, la Policy et le rendu attendu ;
 * ActionHelper reste le seul responsable de l'autorisation et du HTML.
 */
final readonly class UiAction
{
    /** Rendu par HtmlHelper::link(). */
    public const TYPE_LINK = 'link';

    /** Rendu par FormHelper::postLink(). */
    public const TYPE_POST_LINK = 'postLink';
    /** Rendu par HtmlHelper::tag('button') pour les commandes API. */
    public const TYPE_BUTTON = 'button';

    /**
     * @param self::TYPE_* $type Type de contrôle à rendre.
     * @param string $label Libellé textuel, toujours échappé par le Helper.
     * @param string|null $icon Classes Font Awesome facultatives.
     * @param array<int|string, mixed>|string $url Destination CakePHP.
     * @param string $authorizationAction Action évaluée par la Policy.
     * @param mixed $resource Entité cible ou nom de table de la Policy.
     * @param array<string, mixed> $options Options HtmlHelper/FormHelper.
     * @param bool $requiresAuthorization Indique si une Policy doit être évaluée.
     */
    public function __construct(
        public string $type,
        public string $label,
        public ?string $icon,
        public array|string $url,
        public string $authorizationAction,
        public mixed $resource,
        public array $options = [],
        public bool $requiresAuthorization = true,
    ) {
    }
}

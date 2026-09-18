<?php
declare(strict_types=1);

namespace App\View\Helper;

use App\View\Action\UiAction;
use Cake\ORM\TableRegistry;
use Cake\View\Helper;
use InvalidArgumentException;
use Throwable;

/**
 * Rendu générique des commandes UI après vérification de leur Policy.
 * Implémente le point de passage unique de présentation défini par les
 * fabriques de commandes de domaine.
 *
 * @extends \Cake\View\Helper<\Cake\View\View>
 * @property \Cake\View\Helper\HtmlHelper $Html
 * @property \Cake\View\Helper\FormHelper $Form
 */
class ActionHelper extends Helper
{
    /**
     * @var array<string>
     */
    protected array $helpers = ['Html', 'Form'];

    /**
     * Retourne le contrôle HTML, ou une chaîne vide si l'action est interdite.
     *
     * @param \App\View\Action\UiAction $action Commande déclarative à afficher.
     * @return string Balisage HTML autorisé, sinon chaîne vide.
     */
    public function render(UiAction $action): string
    {
        if ($action->requiresAuthorization && !$this->isAllowed($action)) {
            return '';
        }

        $options = ['escape' => false] + $action->options;
        if ($action->type === UiAction::TYPE_LINK && isset($options['confirm'])) {
            $confirmation = (string)$options['confirm'];
            unset($options['confirm']);
            $options['onclick'] = 'return confirm(' . json_encode($confirmation) . ');';
        }
        $label = $action->icon === null
            ? h($action->label)
            : sprintf('<i class="fa-solid %s me-1" aria-hidden="true"></i>%s', h($action->icon), h($action->label));

        return match ($action->type) {
            UiAction::TYPE_LINK => $this->Html->link($label, $action->url, $options),
            UiAction::TYPE_POST_LINK => $this->Form->postLink($label, $action->url, $options),
            UiAction::TYPE_BUTTON => $this->Html->tag('button', $label, ['type' => 'button'] + $options),
            default => throw new InvalidArgumentException(sprintf('Type d’action UI inconnu : %s', $action->type)),
        };
    }

    /**
     * Évalue l'autorisation d'une commande UI.
     *
     * @param \App\View\Action\UiAction $action Commande à contrôler.
     * @return bool Vrai lorsque la Policy permet l'action.
     */
    public function isAllowed(UiAction $action): bool
    {
        try {
            $identity = $this->_View->getRequest()->getAttribute('identity');
            $resource = is_string($action->resource)
                ? TableRegistry::getTableLocator()->get($action->resource)->newEmptyEntity()
                : $action->resource;

            return $identity !== null && $identity->can($action->authorizationAction, $resource);
        } catch (Throwable) {
            return false;
        }
    }
}

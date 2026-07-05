<?php

namespace App\View\Helper;

use Cake\View\Helper;
use Cake\ORM\TableRegistry;

/**
 * @class TabulatorHelper
 * @description Générateur de structure DOM pour les grilles Tabulator couplé au plugin Authorization.
 */
class TabulatorHelper extends Helper
{
    /**
     * Génère automatiquement le conteneur HTML d'une grille Tabulator sécurisée.
     *
     * @param string $selectorId Le sélecteur CSS (ex: '#users-table').
     * @param string $controllerName Le nom du contrôleur CakePHP (ex: 'Users').
     * @return string Balisage HTML du conteneur.
     */
    public function renderGrid(string $selectorId, string $controllerName): string
    {
        // 1. Nettoyage du sélecteur pour extraire l'ID HTML brut
        $htmlId = ltrim($selectorId, '#');

        // 2. Instanciation d'une entité vierge pour servir de contexte à la Policy
        $tableInstance = TableRegistry::getTableLocator()->get($controllerName);
        $emptyEntity = $tableInstance->newEmptyEntity();

        // 3. Récupération de l'identité connectée depuis la Request
        $identity = $this->_View->getRequest()->getAttribute('identity');

        // 4. Évaluation dynamique du droit de création (action 'add' de la Policy)
        $canCreate = $identity ? $identity->can('add', $emptyEntity) : false;

        // 5. Rendu du composant HTML porteur des métadonnées structurelles
        return sprintf(
            '<div id="%s" data-controller="%s" data-can-create="%s"></div>',
            h($htmlId),
            h(strtolower($controllerName)),
            $canCreate ? 'true' : 'false'
        );
    }
}

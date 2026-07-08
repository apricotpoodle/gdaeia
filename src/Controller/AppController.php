<?php

declare(strict_types=1);

/**
 * CakePHP(tm) : Rapid Development Framework (https://cakephp.org)
 * Copyright (c) Cake Software Foundation, Inc. (https://cakefoundation.org)
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright Copyright (c) Cake Software Foundation, Inc. (https://cakefoundation.org)
 * @link      https://cakephp.org CakePHP(tm) Project
 * @since     0.2.9
 * @license   https://opensource.org/licenses/mit-license.php MIT License
 */

namespace App\Controller;

use Cake\Controller\Controller;
use Cake\Datasource\EntityInterface;
use Cake\Event\EventInterface;
use Override;

/**
 * Application Controller
 *
 * Add your application-wide methods in the class below, your controllers
 * will inherit them.
 *
 * @link https://book.cakephp.org/5/en/controllers.html#the-app-controller
 */
class AppController extends Controller
{
    /**
     * Initialization hook method.
     *
     * Use this method to add common initialization code like loading components.
     *
     * e.g. `$this->loadComponent('FormProtection');`
     *
     * @return void
     */
    public function initialize(): void
    {
        parent::initialize();

        $this->loadComponent('Flash');
        $this->loadComponent('Authentication.Authentication');
        $this->loadComponent('Authorization.Authorization');

        /*
         * Enable the following component for recommended CakePHP form protection settings.
         * see https://book.cakephp.org/5/en/controllers/components/form-protection.html
         */
        //$this->loadComponent('FormProtection');
    }

    /**
     * Génère un formateur de droits (grid_rights) standardisé pour le TabulatorAdapter.
     * Automatise le CRUD de base et permet l'injection de règles spécifiques.
     *
     * @param array $extraActions Liste d'actions métiers supplémentaires (ex: ['impersonate', 'validate'])
     * @param callable|null $columnsFormatter Hook pour formater la visibilité spécifique des colonnes
     * @return callable
     */
    protected function createGridRightsFormatter(array $extraActions = [], ?callable $columnsFormatter = null): callable
    {
        $authorization = $this->Authorization;

        return function (EntityInterface $entity) use ($authorization, $extraActions, $columnsFormatter) {
            // 1. Le Socle Industriel Commun (CRUD)
            $actions = [
                'view'   => $authorization->can($entity, 'view'),
                'edit'   => $authorization->can($entity, 'edit'),
                'delete' => $authorization->can($entity, 'delete'),
            ];

            // 2. Extension dynamique des actions métiers spécifiques
            foreach ($extraActions as $action) {
                $actions[$action] = $authorization->can($entity, $action);
            }

            // 3. Traitement optionnel de la visibilité des colonnes
            $columns = $columnsFormatter ? $columnsFormatter($entity, $authorization) : [];

            return [
                'actions' => $actions,
                'columns' => $columns,
            ];
        };
    }

    // /**
    //  * Callback beforeFilter - Exécuté avant chaque action de contrôleur.
    //  * * Cette méthode intercepte la requête pour appliquer des règles de gouvernance globale.
    //  * Elle intègre un mécanisme d'isolation pour le plugin de débogage DebugKit afin d'éviter
    //  * des conflits d'autorisation (AuthorizationRequiredException).
    //  *
    //  * @param \Cake\Event\EventInterface $event L'instance de l'événement courant.
    //  * @return void Renvoie une Response pour interrompre le cycle, ou void.
    //  * @throws \RuntimeException Si les composants requis ne sont pas correctement initialisés.
    //  */
    // public function beforeFilter(EventInterface $event)
    // {
    //     parent::beforeFilter($event);

    //     /** * Récupération sémantique et typée de l'objet Request.
    //      * @var \Cake\Http\ServerRequest $request
    //      */
    //     $request = $this->getRequest();

    //     /**
    //      * 🛡️ PASSERELLE DE SÉCURITÉ : Isolation et dérogation pour le plugin DebugKit
    //      * * DebugKit opère via ses propres contrôleurs internes (ex: ToolbarController).
    //      * Sous l'effet du middleware Authorization strict, ces routes déclenchent une exception
    //      * car DebugKit ne possède pas de règles d'autorisation métier (Policies).
    //      */
    //     if ($request->getParam('plugin') === 'DebugKit') {

    //         // On vérifie de manière défensive la présence du composant pour satisfaire PHPStan
    //         if (!isset($this->Authorization)) {
    //             throw new \RuntimeException('Le composant AuthorizationComponent n\'est pas chargé.');
    //         }

    //         /** * Indique impérativement au Middleware de sécurité d'ignorer la vérification
    //          * de conformité des politiques (Policies) pour les requêtes internes de DebugKit.
    //          */
    //         $this->Authorization->skipAuthorization();

    //         /**
    //          * Sécurité additionnelle (Optionnelle) : Si l'infrastructure exige une session
    //          * authentifiée globale, on autorise l'accès anonyme aux ressources de la toolbar.
    //          */
    //         if (isset($this->Authentication)) {
    //             /** @var \Authentication\Controller\Component\AuthenticationComponent $authentication */
    //             $authentication = $this->Authentication;
    //             $authentication->addUnauthenticatedActions(['*']);
    //         }
    //     }
    // }
}

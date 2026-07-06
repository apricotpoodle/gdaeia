<?php

declare(strict_types=1);

namespace App\Controller\Api;

use App\Controller\AppController;
use App\Service\DataGrid\TabulatorAdapter;
use Cake\Event\EventInterface;

/**
 * Class UsersController (API)
 *
 * Contrôleur dédié à l'exposition des données Utilisateurs au format JSON.
 * Gère exclusivement les flux de données (Data Fetching) pour le front-end.
 *
 * @package App\Controller\Api
 * @property \App\Model\Table\UsersTable $Users
 */
class UsersController extends AppController
{
    /**
     * Initialisation du contrôleur.
     * Charge le composant RequestHandler pour activer le rendu JSON natif.
     *
     * @return void
     */
    public function initialize(): void
    {
        parent::initialize();

        // CakePHP 5 : On force explicitement l'utilisation de la vue JSON
        // pour toutes les actions de ce contrôleur API.
        $this->viewBuilder()->setClassName('Json');
    }

    /**
     * Correction de l'interception précoce :
     * On s'assure que l'autorisation est gérée ou contournée proprement avant la sérialisation
     */
    public function beforeFilter(EventInterface $event)
    {
        parent::beforeFilter($event);

        // Option Sécurisée : Si l'utilisateur est authentifié globalement,
        // on l'autorise à consommer l'API index sans re-vérification de Policy ici
        $this->Authorization->skipAuthorization(['index']);
    }

    /**
     * Méthode Index (GET /api/users.json)
     *
     * Récupère la liste paginée des utilisateurs en appliquant les tris et filtres
     * demandés par le composant front-end Tabulator, tout en injectant dynamiquement
     * les droits d'accès visuels (grid_rights) de manière centralisée et DRY.
     *
     * @return void
     */
    public function index(): void
    {
        // Sécurité : On n'accepte que les requêtes en lecture
        $this->request->allowMethod(['get']);

        // =====================================================================
        // 1. VERROU DE SÉCURITÉ : Validation stricte via la UserPolicy::canIndex()
        // =====================================================================
        $this->Authorization->authorize($this->Users->newEmptyEntity(), 'index');

        $adapter = new TabulatorAdapter();
        $queryParams = $this->request->getQueryParams();

        // 2. Préparation de la requête avec la relation vers la table Roles
        $query = $this->Users->find()->contain(['Roles']);

        // 3. Traduction des tris et filtres Tabulator vers la requête SQL
        $query = $adapter->adaptRequest($this->request, $query);

        // 4. Exécution de la requête avec la pagination native de CakePHP
        $paginatedData = $this->paginate($query, [
            'limit' => (int)($queryParams['size'] ?? 20),
            'page'  => (int)($queryParams['page'] ?? 1),
            // SÉCURITÉ : On interdit au Paginator CakePHP de trier via l'URL,
            // car le TabulatorAdapter a déjà appliqué les tris sur l'objet $query.
            'sortableFields' => []
        ]);

        // =====================================================================
        // 5. FABRIQUE DE DROITS DRY (Méthode définie dans AppController)
        // =====================================================================
        $rightsFormatter = $this->createGridRightsFormatter(
            // Actions métiers spécifiques s'ajoutant au CRUD de base (view, edit, delete)
            ['impersonate'],

            // Callback pour piloter la visibilité dynamique des cellules/colonnes
            function ($entity, $authorization) {
                return [
                    'email'       => true,
                    // Seul un profil ayant le droit d'exécuter la suppression (Admin)
                    // verra/pilotera l'interrupteur Super Utilisateur dans sa ligne
                    'issuperuser' => $authorization->can($entity, 'delete'),
                ];
            }
        );

        // 6. Formatage de la structure de réponse par l'adaptateur agnostique
        $output = $adapter->adaptResponse($paginatedData, $rightsFormatter);

        // 7. Rendu final sérialisé en JSON conforme CakePHP 5
        $this->set($output);
        $this->viewBuilder()->setOption('serialize', array_keys($output));
    }
}

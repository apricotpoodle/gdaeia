<?php
declare(strict_types=1);

namespace App\Controller\Api;

use App\Controller\AppController;
use App\Service\DataGrid\TabulatorAdapter;

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
        $this->viewBuilder()->setClassName('Json');    }

    /**
     * Méthode Index (GET /api/users.json)
     * * Récupère la liste paginée des utilisateurs en appliquant les tris
     * demandés par le composant front-end Tabulator.
     *
     * @return void
     */
    public function index(): void
    {
        // Sécurité : On n'accepte que les requêtes en lecture
        $this->request->allowMethod(['get']);
        
        $adapter = new TabulatorAdapter();
        $queryParams = $this->request->getQueryParams();
        
        // 1. Préparation de la requête avec la relation vers la table Roles
        $query = $this->Users->find()->contain(['Roles']);
        
        // 2. Traduction des tris Tabulator vers la requête SQL
        $query = $adapter->adaptRequest($this->request, $query);

        // 3. Exécution de la requête avec la pagination native
        $paginatedData = $this->paginate($query, [
            'limit' => (int)($queryParams['size'] ?? 20),
            'page'  => (int)($queryParams['page'] ?? 1)
        ]);

        // 4. Formatage de la réponse
        $output = $adapter->adaptResponse($paginatedData);

        // 5. Rendu sérialisé en JSON
        $this->set($output);
        $this->viewBuilder()->setOption('serialize', array_keys($output));
    }
}
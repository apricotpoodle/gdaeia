<?php
declare(strict_types=1);

namespace App\Controller\Api;

use App\Controller\AppController;
use App\Service\DataGrid\TabulatorAdapter;
use Cake\Event\EventInterface;

/**
 * Class ApplicationformsController (API)
 *
 * Expose les données des demandes sous format JSON pour le front-end.
 *
 * @property \App\Model\Table\ApplicationformsTable $Applicationforms
 */
class ApplicationformsController extends AppController
{
    /**
     * @return void
     */
    public function initialize(): void
    {
        parent::initialize();
        $this->viewBuilder()->setClassName('Json');
    }

    /**
     * @param \Cake\Event\EventInterface $event
     * @return void
     */
    public function beforeFilter(EventInterface $event)
    {
        parent::beforeFilter($event);
        // Exemption de l'autorisation middleware globale pour vérification granulaire dans la méthode
        $this->Authorization->skipAuthorization(['index']);
    }

    /**
     * Méthode Index (GET /api/applicationforms.json)
     *
     * @return void
     */
    public function index(): void
    {
        $this->request->allowMethod(['get']);

        // Verrou de sécurité
        $this->Authorization->authorize($this->Applicationforms->newEmptyEntity(), 'index');

        $adapter = new TabulatorAdapter();
        $queryParams = $this->request->getQueryParams();

        /** @var \App\Model\Entity\User $currentUser */
        $currentUser = $this->request->getAttribute('identity')->getOriginalData();

        // 1. Préparation de la requête ORM AVEC le filtre de sécurité "visibleTo"
        $query = $this->Applicationforms->find('visibleTo', user: $currentUser)
            // ->contain([
            //     'Departments',
            //     'Users',
            //     'Contracttypes',
            //     'Hiringreasons'
            // ]);
        ->contain([
                // Forcer le LEFT JOIN temporairement pour voir si les données apparaissent
                'Departments' => ['joinType' => 'LEFT'],
                'Users' => ['joinType' => 'LEFT'],
                'Contracttypes' => ['joinType' => 'LEFT'],
                'Hiringreasons' => ['joinType' => 'LEFT']
            ]);

        // 2. Application des tris et filtres Tabulator
        $query = $adapter->adaptRequest($this->request, $query);

        // 3. Pagination native
        $paginatedData = $this->paginate($query, [
            'limit' => (int)($queryParams['size'] ?? 40), // Valeur conseillée pour le scroll progressif (ADR 0039)
            'page'  => (int)($queryParams['page'] ?? 1),
            'sortableFields' => []
        ]);

        // 4. Droits dynamiques de la grille
        $rightsFormatter = $this->createGridRightsFormatter();

        // 5. Rendu structuré pour Tabulator
        $output = $adapter->adaptResponse($paginatedData, $rightsFormatter);

        $this->set($output);
        $this->viewBuilder()->setOption('serialize', array_keys($output));
    }
}

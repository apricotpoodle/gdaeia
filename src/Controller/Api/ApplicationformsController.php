<?php
declare(strict_types=1);

namespace App\Controller\Api;

use App\Controller\AppController;
use App\Service\DataGrid\TabulatorAdapter;
use App\Service\Security\FieldAuthorizationService;
use Cake\Datasource\EntityInterface;
use Cake\Event\EventInterface;
use Cake\Http\Response;
use Cake\ORM\TableRegistry;

/**
 * Class ApplicationformsController (API)
 *
 * Expose les données des demandes sous format JSON pour le front-end.
 *
 * @property \App\Model\Table\ApplicationformsTable $Applicationforms
 */
class ApplicationformsController extends AppController
{
    public function initialize(): void
    {
        parent::initialize();
        $this->viewBuilder()->setClassName('Json');
    }

    public function beforeFilter(EventInterface $event)
    {
        parent::beforeFilter($event);
        $this->Authorization->skipAuthorization(['index', 'getFormSchema']);
    }

    /**
     * Endpoint : GET /api/applicationforms/get-form-schema.json
     * Distribue les permissions sur les champs et les listes de référence filtrées par périmètre (visibleTo).
     */
    public function getFormSchema(): void
    {
        $this->request->allowMethod(['get']);

        $service = new FieldAuthorizationService();
        $identity = $this->request->getAttribute('identity');

        /** @var \App\Model\Entity\User $currentUser */
        $currentUser = $identity->getOriginalData();

        // Récupération du schéma ACL sur la ressource Applicationforms (ADR 0042)
        $schema = $service->getFieldSchema($identity, 'Applicationforms');

        // Instanciation des tables
        $departmentsTable = TableRegistry::getTableLocator()->get('Departments');
        $contracttypesTable = TableRegistry::getTableLocator()->get('Contracttypes');
        $hiringreasonsTable = TableRegistry::getTableLocator()->get('Hiringreasons');
        $profCategoriesTable = TableRegistry::getTableLocator()->get('Professionalcategories');
        $worktimesTable = TableRegistry::getTableLocator()->get('Worktimes');
        $periodsTable = TableRegistry::getTableLocator()->get('Periods');
        $budgetfeaturesTable = TableRegistry::getTableLocator()->get('Budgetfeatures');
        $yesnosTable = TableRegistry::getTableLocator()->get('Yesnos');
        $usersTable = TableRegistry::getTableLocator()->get('Users');

        // Application systématique du finder 'visibleTo' avec l'utilisateur courant (ADR 0041)
        $departments = $departmentsTable
            ->find('visibleTo', user: $currentUser)
            ->find('list', keyField: 'id', valueField: 'name')
            ->toArray();

        $contracttypes = $contracttypesTable
            ->find('visibleTo', user: $currentUser)
            ->find('list', keyField: 'id', valueField: 'name')
            ->toArray();

        $hiringreasons = $hiringreasonsTable
            ->find('visibleTo', user: $currentUser)
            ->find('list', keyField: 'id', valueField: 'name')
            ->toArray();

        $professionalcategories = $profCategoriesTable
            ->find('visibleTo', user: $currentUser)
            ->find('list', keyField: 'id', valueField: 'name')
            ->toArray();

        $worktimes = $worktimesTable
            ->find('visibleTo', user: $currentUser)
            ->find('list', keyField: 'id', valueField: 'name')
            ->toArray();

        $periods = $periodsTable
            ->find('visibleTo', user: $currentUser)
            ->find('list', keyField: 'id', valueField: 'name')
            ->toArray();

        $budgetfeatures = $budgetfeaturesTable
            ->find('visibleTo', user: $currentUser)
            ->find('list', keyField: 'id', valueField: 'name')
            ->toArray();

        $yesnos = $yesnosTable
            ->find('visibleTo', user: $currentUser)
            ->find('list', keyField: 'id', valueField: 'name')
            ->toArray();

        $collaborators = $usersTable
            ->find('visibleTo', user: $currentUser)
            ->find('list', keyField: 'id', valueField: 'email')
            ->toArray();

        $this->set(compact(
            'schema',
            'departments',
            'contracttypes',
            'hiringreasons',
            'professionalcategories',
            'worktimes',
            'periods',
            'budgetfeatures',
            'yesnos',
            'collaborators',
        ));

        $this->viewBuilder()->setOption('serialize', [
            'schema',
            'departments',
            'contracttypes',
            'hiringreasons',
            'professionalcategories',
            'worktimes',
            'periods',
            'budgetfeatures',
            'yesnos',
            'collaborators',
        ]);
    }

    /**
     * Endpoint : POST /api/applicationforms/add.json
     */
    public function add(): ?Response
    {
        $this->request->allowMethod(['post']);
        $this->Authorization->authorize($this->Applicationforms->newEmptyEntity(), 'add');

        $applicationform = $this->Applicationforms->newEmptyEntity();

        $authService = new FieldAuthorizationService();
        $identity = $this->request->getAttribute('identity');

        // Double protection : Filtrage des champs contre la matrice ACL (ADR 0042)
        $schema = $authService->getFieldSchema($identity, 'Applicationforms');
        $filteredData = $authService->filterRequestData($this->request->getData(), $schema);

        // Assignation automatique de l'utilisateur créateur
        /** @var \App\Model\Entity\User $user */
        $user = $identity->getOriginalData();
        $filteredData['user_id'] = $user->id;

        $applicationform = $this->Applicationforms->patchEntity($applicationform, $filteredData);

        if ($this->Applicationforms->save($applicationform)) {
            return $this->response->withType('application/json')
                ->withStringBody(json_encode(['success' => true, 'id' => $applicationform->id]));
        }
        
        /** @var \Cake\Datasource\EntityInterface $applicationform */
        return $this->handleValidationError($applicationform);
    }

    /**
     * Endpoint : PUT/PATCH /api/applicationforms/edit/{id}.json
     */
    public function edit(string $id): ?Response
    {
        $this->request->allowMethod(['post', 'put', 'patch']);

        $applicationform = $this->Applicationforms->get($id);
        $this->Authorization->authorize($applicationform, 'edit');

        $authService = new FieldAuthorizationService();
        $identity = $this->request->getAttribute('identity');

        $schema = $authService->getFieldSchema($identity, 'Applicationforms');
        $filteredData = $authService->filterRequestData($this->request->getData(), $schema);

        $applicationform = $this->Applicationforms->patchEntity($applicationform, $filteredData);

        if ($this->Applicationforms->save($applicationform)) {
            return $this->response->withType('application/json')
                ->withStringBody(json_encode(['success' => true]));
        }

        /** @var \Cake\Datasource\EntityInterface $applicationform */
        return $this->handleValidationError($applicationform);
    }

    /**
     * Gestion centralisée des erreurs de validation
     * 
     * @param \Cake\Datasource\EntityInterface $entity
     * @return \Cake\Http\Response
     */
    private function handleValidationError(EntityInterface $entity): Response
    {
        $errors = $entity->getErrors();
        $message = __('Le formulaire contient des données invalides.');

        if (!empty($errors)) {
            $firstError = current(reset($errors));
            $message = (string)$firstError;
        }

        return $this->response->withType('application/json')
            ->withStatus(400)
            ->withStringBody(json_encode(['success' => false, 'message' => $message]));
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
            ->contain([
                'Departments',
                'Users',
                'Contracttypes',
                'Hiringreasons',
                'Comments',
            ]);

        // 2. Application des tris et filtres Tabulator
        $query = $adapter->adaptRequest($this->request, $query);

        // 3. Pagination native
        $paginatedData = $this->paginate($query, [
            'limit' => (int)($queryParams['size'] ?? 40), // Valeur conseillée pour le scroll progressif (ADR 0039)
            'page'  => (int)($queryParams['page'] ?? 1),
            'sortableFields' => [],
        ]);

        // 4. Droits dynamiques de la grille
        $rightsFormatter = $this->createGridRightsFormatter();

        // 5. Rendu structuré pour Tabulator
        $output = $adapter->adaptResponse($paginatedData, $rightsFormatter);

        $this->set($output);
        $this->viewBuilder()->setOption('serialize', array_keys($output));
    }
}

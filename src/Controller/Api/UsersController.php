<?php
declare(strict_types=1);

namespace App\Controller\Api;

use App\Controller\AppController;
use App\Model\Entity\User;
use App\Service\DataGrid\TabulatorAdapter;
use App\Service\Security\FieldAuthorizationService;
use Cake\Datasource\EntityInterface;
use Cake\Event\EventInterface;
use Cake\Http\Exception\ForbiddenException;
use Cake\Http\Response;
use Cake\Log\Log;
use Cake\ORM\TableRegistry;


/**
 * Class UsersController (API)
 *
 * Contrôleur dédié à l'exposition des données Utilisateurs au format JSON.
 *
 * @package App\Controller\Api
 * @property \App\Model\Table\UsersTable $Users
 */
class UsersController extends AppController
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
    public function beforeFilter(EventInterface $event): void
    {
        parent::beforeFilter($event);
        $this->Authorization->skipAuthorization(['getFormSchema']);
    }

    /**
     * Endpoint : GET /api/users/get-form-schema.json
     * Fournit les permissions sur les champs, les rôles, et l'arborescence des départements autorisés.
     *
     * @return void
     */
    public function getFormSchema(): void
    {
        $this->request->allowMethod(['get']);

        $service = new FieldAuthorizationService();
        $identity = $this->request->getAttribute('identity');

        /** @var \App\Model\Entity\User $currentUser */
        $currentUser = $identity->getOriginalData();

        // Schéma ACL des champs
        $schema = $service->getFieldSchema($identity, 'Users');

        // Liste des rôles
        $rolesTable = TableRegistry::getTableLocator()->get('Roles');
        $roles = $rolesTable->find('list', keyField: 'id', valueField: 'name')
            ->orderBy(['Roles.name' => 'ASC'])
            ->toArray();

        // Arborescence filtrée par périmètre opérateur
        $departmentsTable = TableRegistry::getTableLocator()->get('Departments');
        $departments = $departmentsTable->findTreeSelectFormat($currentUser);

        $this->set(compact('schema', 'roles', 'departments'));
        $this->viewBuilder()->setOption('serialize', ['schema', 'roles', 'departments']);
    }

    /**
     * Endpoint : POST /api/users/add.json
     * Traite l'ajout d'un utilisateur et gère l'association de ses départements.
     *
     * @return \Cake\Http\Response|null
     */
    public function add(): ?Response
    {
        $this->request->allowMethod(['post']);
        $this->Authorization->authorize($this->Users->newEmptyEntity(), 'add');

        $user = $this->Users->newEmptyEntity();
        $authService = new FieldAuthorizationService();
        $identity = $this->request->getAttribute('identity');

        $schema = $authService->getFieldSchema($identity, 'Users');
        $schema['user_departments'] = 'EDIT';

        $rawParams = $this->request->getData();

        // 💡 FIX : Maintien du tableau user_departments s'il est transmis
        if (!isset($rawParams['user_departments']) || $rawParams['user_departments'] === '') {
            $rawParams['user_departments'] = [];
        }

        $filteredData = $authService->filterRequestData($rawParams, $schema);

        $user = $this->Users->patchEntity($user, $filteredData, [
            'associated' => ['UserDepartments'],
        ]);

        if ($this->Users->save($user)) {
            return $this->response->withType('application/json')
                ->withStringBody(json_encode(['success' => true, 'id' => $user->id]));
        }

        return $this->handleValidationError($user);
    }

    /**
     * Endpoint : PUT/PATCH /api/users/edit/{id}.json
     *
     * @param string $id
     * @return \Cake\Http\Response|null
     */
    public function edit(string $id): ?Response
    {
        $this->request->allowMethod(['post', 'put', 'patch']);

        $user = $this->Users->get($id, contain: ['UserDepartments']);
        $this->Authorization->authorize($user, 'edit');

        $authService = new FieldAuthorizationService();
        $identity = $this->request->getAttribute('identity');

        $schema = $authService->getFieldSchema($identity, 'Users');
        $schema['user_departments'] = 'EDIT';

        $rawParams = $this->request->getData();
        // ==============================================================
        // 🛠️ DÉBUT DES LOGS D'ANALYSE
        // ==============================================================
        Log::debug("========== EDITION USER #{$id} ==========");
        Log::debug("1. [HTTP POST] Données brutes reçues pour user_departments : \n" . print_r($rawParams['user_departments'] ?? 'CLÉ ABSENTE', true));

        // 💡 FIX : Forcer la présence du tableau vide si tous les départements ont été décochés
        if (!isset($rawParams['user_departments']) || $rawParams['user_departments'] === '') {
            $rawParams['user_departments'] = [];
        }

        $filteredData = $authService->filterRequestData($rawParams, $schema);
        Log::debug("2. [SECURITY SERVICE] Données après filtrage : \n" . print_r($filteredData['user_departments'] ?? 'PURGÉ PAR LE SERVICE', true));

        $user = $this->Users->patchEntity($user, $filteredData, [
            'associated' => ['UserDepartments'],
        ]);




        // if ($this->Users->save($user)) {
        //     return $this->response->withType('application/json')
        //         ->withStringBody(json_encode(['success' => true]));
        // }

        Log::debug("3. [ORM PATCH] Entité après hydratation (Que contient-elle ?) : \n" . print_r($user->user_departments, true));

        if ($user->hasErrors()) {
            Log::error("🚨 [ORM ERRORS] L'entité User refuse l'enregistrement pour les raisons suivantes : \n" . print_r($user->getErrors(), true));
        }

        if ($this->Users->save($user)) {
            Log::debug("4. [ORM SAVE] Sauvegarde réussie en Base de données !");
            Log::debug("=========================================\n");

            return $this->response->withType('application/json')
                ->withStringBody(json_encode(['success' => true]));
        }

        Log::error("4. [ORM SAVE] Sauvegarde ÉCHOUÉE !");
        Log::debug("=========================================\n");
        // ==============================================================
        // 🛠️ FIN DES LOGS D'ANALYSE
        // ==============================================================
        return $this->handleValidationError($user);
    }

    /**
     * Endpoint : POST /api/users/bulk-departments.json
     *
     * Ajoute un même périmètre explicite de départements à plusieurs utilisateurs.
     * Les identifiants transmis correspondent à l'état effectif de TreeSelectAdapter :
     * lorsqu'un parent est coché, ses descendants font déjà partie de cette liste.
     *
     * @return \Cake\Http\Response
     */
    public function bulkDepartments(): Response
    {
        $this->request->allowMethod(['post']);

        $rawParams = $this->request->getData();
        $userIds = $this->normalizePositiveIntegerList($rawParams['user_ids'] ?? null, 'user_ids');
        $associationMode = $rawParams['association_mode'] ?? 'add';
        if (!is_string($associationMode) || !in_array($associationMode, ['add', 'replace'], true)) {
            throw new \Cake\Http\Exception\BadRequestException(__('Le mode d’association est invalide.'));
        }
        $departmentIds = $this->normalizePositiveIntegerList(
            $rawParams['department_ids'] ?? null,
            'department_ids',
            $associationMode === 'replace',
        );

        $identity = $this->request->getAttribute('identity');
        /** @var \App\Model\Entity\User $currentUser */
        $currentUser = $identity->getOriginalData();

        $authService = new FieldAuthorizationService();
        $fieldSchema = $authService->getFieldSchema($identity, 'Users');
        if (($fieldSchema['user_departments'] ?? 'EDIT') !== 'EDIT') {
            throw new ForbiddenException(__('Vous ne disposez pas du droit de modifier les périmètres organisationnels.'));
        }

        $targetUsers = $this->Users->find('visibleTo', user: $currentUser)
            ->where(['Users.id IN' => $userIds])
            ->all()
            ->toList();

        if (count($targetUsers) !== count($userIds)) {
            throw new ForbiddenException(__('Au moins un utilisateur ciblé est hors de votre périmètre.'));
        }
        foreach ($targetUsers as $targetUser) {
            $this->Authorization->authorize($targetUser, 'edit');
        }

        $authorizedDepartmentIds = $this->flattenTreeSelectValues(
            $this->fetchTable('Departments')->findTreeSelectFormat($currentUser),
        );
        if (array_diff($departmentIds, $authorizedDepartmentIds) !== []) {
            throw new ForbiddenException(__('Au moins un département ciblé est hors de votre périmètre.'));
        }

        /** @var \App\Model\Table\UserDepartmentsTable $userDepartments */
        $userDepartments = $this->fetchTable('UserDepartments');
        $createdCount = $userDepartments->getConnection()->transactional(function () use ($userDepartments, $userIds, $departmentIds, $associationMode): int {
            if ($associationMode === 'replace') {
                return $userDepartments->replaceAssociationsForUsers($userIds, $departmentIds);
            }

            return $userDepartments->addMissingAssociations($userIds, $departmentIds);
        });

        return $this->response->withType('application/json')
            ->withStringBody(json_encode([
                'success' => true,
                'users_count' => count($userIds),
                'departments_count' => count($departmentIds),
                'associations_created' => $createdCount,
                'association_mode' => $associationMode,
            ]));
    }

    /** Retourne l'arbre des départements administrables dans l'écran d'association. */
    public function bulkDepartmentsTree(): void
    {
        $this->request->allowMethod(['get']);
        $this->Authorization->authorize($this->Users->newEmptyEntity(), 'add');

        /** @var \App\Model\Entity\User $currentUser */
        $currentUser = $this->request->getAttribute('identity')->getOriginalData();
        $departments = $this->fetchTable('Departments')->find('treeThreadedVisibleTo', user: $currentUser)
            ->all();

        $this->set('data', $departments);
        $this->viewBuilder()->setOption('serialize', ['data']);
    }

    /** Retourne les utilisateurs administrables dans l'écran d'association. */
    public function bulkDepartmentsUsers(): void
    {
        $this->request->allowMethod(['get']);
        $this->Authorization->authorize($this->Users->newEmptyEntity(), 'add');

        /** @var \App\Model\Entity\User $currentUser */
        $currentUser = $this->request->getAttribute('identity')->getOriginalData();
        $users = $this->Users->find('visibleTo', user: $currentUser)
            ->contain(['Roles'])
            ->orderBy(['Users.lastname' => 'ASC', 'Users.firstname' => 'ASC'])
            ->all();

        $this->set('data', $users);
        $this->viewBuilder()->setOption('serialize', ['data']);
    }

    /** Retourne les utilisateurs associés à tous les départements sélectionnés. */
    public function bulkDepartmentsAssignedUsers(): void
    {
        $this->request->allowMethod(['get']);
        $this->Authorization->authorize($this->Users->newEmptyEntity(), 'add');

        /** @var \App\Model\Entity\User $currentUser */
        $currentUser = $this->request->getAttribute('identity')->getOriginalData();
        $departmentIds = $this->resolveAuthorizedDepartmentIds($this->request->getQuery('department_ids'), $currentUser);
        $userIds = $this->fetchTable('UserDepartments')->find(
            'userIdsAssociatedWithDepartments',
            departmentIds: $departmentIds,
        );
        $users = $this->Users->find('visibleTo', user: $currentUser)
            ->contain(['Roles'])
            ->where(['Users.id IN' => $userIds])
            ->orderBy(['Users.lastname' => 'ASC', 'Users.firstname' => 'ASC'])
            ->all();

        $this->set('data', $users);
        $this->viewBuilder()->setOption('serialize', ['data']);
    }

    /** Associe un utilisateur à tous les départements sélectionnés. */
    public function assignBulkDepartments(): Response
    {
        return $this->updateBulkDepartmentAccess(false);
    }

    /** Retire un utilisateur de tous les départements sélectionnés. */
    public function unassignBulkDepartments(): Response
    {
        return $this->updateBulkDepartmentAccess(true);
    }
    /**
     * Méthode Index (GET /api/users.json)
     *
     * @return void
     */
    public function index(): void
    {
        $this->request->allowMethod(['get']);
        $this->Authorization->authorize($this->Users->newEmptyEntity(), 'index');

        $adapter = new TabulatorAdapter();
        $queryParams = $this->request->getQueryParams();

        /** @var \App\Model\Entity\User $currentUser */
        $currentUser = $this->request->getAttribute('identity')->getOriginalData();

        $query = $this->Users->find('visibleTo', user: $currentUser)
            ->contain(['Roles', 'UserDepartments' => ['Departments']]);

        $query = $adapter->adaptRequest($this->request, $query);

        try {
            $paginatedData = $this->paginate($query, [
                'limit' => (int)($queryParams['size'] ?? 40),
                'page'  => (int)($queryParams['page'] ?? 1),
            ]);
        } catch (\Cake\Http\Exception\NotFoundException $e) {
            $this->request = $this->request->withQueryParams(array_merge($queryParams, ['page' => 1]));
            $paginatedData = $this->paginate($query, [
                'limit' => (int)($queryParams['size'] ?? 40),
                'page'  => 1,
            ]);
        }
        // 2. Détermination dynamique des actions supplémentaires selon le mode d'impersonation
        $extraActions = [];
        if (!$this->Authentication->isImpersonating()) {
            $extraActions[] = 'impersonate';
        }
        $rightsFormatter = $this->createGridRightsFormatter($extraActions);
        $output = $adapter->adaptResponse($paginatedData, $rightsFormatter);

        $this->set($output);
        $this->viewBuilder()->setOption('serialize', array_keys($output));
    }

    /**
     * Gestion des erreurs de validation
     *
     * @param \Cake\Datasource\EntityInterface $entity
     * @return \Cake\Http\Response
     */
    private function handleValidationError(EntityInterface $entity): Response
    {
        $error = $this->findFirstValidationError($entity->getErrors());
        $message = $error === null
            ? __('Impossible d\'enregistrer l\'utilisateur : le serveur n\'a pas retourné de détail de validation.')
            : __('Champ « {0} » : {1}', $this->getUserFieldLabel($error[0]), $error[1]);

        return $this->response->withType('application/json')
            ->withStatus(400)
            ->withStringBody(json_encode(['success' => false, 'message' => $message]));
    }

    /**
     * Valide une liste d'identifiants entiers positifs et élimine ses doublons.
     *
     * @param mixed $values Valeur brute issue du corps JSON.
     * @param string $field Nom du champ à afficher en cas d'erreur.
     * @param bool $allowEmpty Autorise une liste vide, uniquement pour une suppression explicite par remplacement.
     * @return list<int>
     * @throws \Cake\Http\Exception\BadRequestException Si le format est invalide.
     */
    private function normalizePositiveIntegerList(mixed $values, string $field, bool $allowEmpty = false): array
    {
        if (!is_array($values) || (!$allowEmpty && $values === [])) {
            throw new \Cake\Http\Exception\BadRequestException(__('Le champ « {0} » doit être une liste non vide d’identifiants.', $field));
        }

        $normalized = [];
        foreach ($values as $value) {
            if ((!is_int($value) && !(is_string($value) && ctype_digit($value))) || (int)$value < 1) {
                throw new \Cake\Http\Exception\BadRequestException(__('Le champ « {0} » contient un identifiant invalide.', $field));
            }
            $normalized[(int)$value] = (int)$value;
        }

        return array_values($normalized);
    }

    /**
     * Valide le périmètre et applique une mutation atomique d'association.
     *
     * @param bool $remove True pour retirer les associations, false pour les créer.
     * @return \Cake\Http\Response
     */
    private function updateBulkDepartmentAccess(bool $remove): Response
    {
        $this->request->allowMethod(['post']);
        $this->Authorization->authorize($this->Users->newEmptyEntity(), 'add');

        /** @var \App\Model\Entity\User $currentUser */
        $currentUser = $this->request->getAttribute('identity')->getOriginalData();
        $departmentIds = $this->resolveAuthorizedDepartmentIds($this->request->getData('department_ids'), $currentUser);
        $userId = $this->normalizePositiveIntegerList([$this->request->getData('user_id')], 'user_id')[0];
        $targetUser = $this->Users->find('visibleTo', user: $currentUser)->where(['Users.id' => $userId])->first();
        if ($targetUser === null) {
            throw new ForbiddenException(__('L’utilisateur ciblé est hors de votre périmètre.'));
        }
        $this->Authorization->authorize($targetUser, 'edit');

        /** @var \App\Model\Table\UserDepartmentsTable $userDepartments */
        $userDepartments = $this->fetchTable('UserDepartments');
        $count = $userDepartments->getConnection()->transactional(function () use ($userDepartments, $userId, $departmentIds, $remove): int {
            return $remove
                ? $userDepartments->removeAssociationsForUser($userId, $departmentIds)
                : $userDepartments->addMissingAssociations([$userId], $departmentIds);
        });

        return $this->response->withType('application/json')->withStringBody(json_encode([
            'success' => true,
            $remove ? 'associations_deleted' : 'associations_created' => $count,
        ]));
    }

    /**
     * Valide que les départements sélectionnés appartiennent au périmètre de l'opérateur.
     *
     * @param mixed $values Valeur brute issue de la requête.
     * @param \App\Model\Entity\User $currentUser Opérateur connecté.
     * @return list<int>
     */
    private function resolveAuthorizedDepartmentIds(mixed $values, User $currentUser): array
    {
        $departmentIds = $this->normalizePositiveIntegerList($values, 'department_ids');
        $authorizedDepartmentIds = $this->flattenTreeSelectValues(
            $this->fetchTable('Departments')->findTreeSelectFormat($currentUser),
        );
        if (array_diff($departmentIds, $authorizedDepartmentIds) !== []) {
            throw new ForbiddenException(__('Au moins un département ciblé est hors de votre périmètre.'));
        }

        return $departmentIds;
    }

    /**
     * Extrait récursivement les valeurs autorisées d'un arbre TreeselectJS.
     *
     * @param array<int, array<string, mixed>> $nodes Arbre formaté pour TreeselectJS.
     * @return list<int>
     */
    private function flattenTreeSelectValues(array $nodes): array
    {
        $values = [];
        foreach ($nodes as $node) {
            if (isset($node['value'])) {
                $values[] = (int)$node['value'];
            }
            if (isset($node['children']) && is_array($node['children'])) {
                array_push($values, ...$this->flattenTreeSelectValues($node['children']));
            }
        }

        return array_values(array_unique($values));
    }

    /**
     * Extrait la première erreur en préservant le champ métier qui la porte.
     *
     * @param array<string, mixed> $errors Erreurs produites par l'ORM CakePHP.
     * @param string|null $rootField Champ racine pour les associations imbriquées.
     * @return array{0: string, 1: string}|null
     */
    private function findFirstValidationError(array $errors, ?string $rootField = null): ?array
    {
        foreach ($errors as $field => $details) {
            $currentRootField = $rootField ?? (string)$field;
            if (is_string($details)) {
                return [$currentRootField, $details];
            }

            if (is_array($details)) {
                $error = $this->findFirstValidationError($details, $currentRootField);
                if ($error !== null) {
                    return $error;
                }
            }
        }

        return null;
    }

    /**
     * Retourne le libellé fonctionnel d'un champ utilisateur.
     *
     * @param string $field Nom technique du champ.
     * @return string
     */
    private function getUserFieldLabel(string $field): string
    {
        return [
            'email' => __('Adresse courriel'),
            'username' => __('Nom d\'utilisateur'),
            'password' => __('Mot de passe'),
            'role_id' => __('Rôle applicatif'),
            'user_departments' => __('Périmètre organisationnel'),
            'department_id' => __('Département'),
        ][$field] ?? $field;
    }
}

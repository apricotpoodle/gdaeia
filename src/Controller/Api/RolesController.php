<?php
declare(strict_types=1);

namespace App\Controller\Api;

use App\Controller\AppController;
use App\Model\Entity\Role;
use App\Model\Entity\User;
use App\Service\DataGrid\TabulatorAdapter;
use Cake\Datasource\EntityInterface;
use Cake\Http\Response;

/**
 * API JSON du référentiel des rôles.
 *
 * @property \App\Model\Table\RolesTable $Roles
 */
class RolesController extends AppController
{
    /** @return void */
    public function initialize(): void
    {
        parent::initialize();
        $this->viewBuilder()->setClassName('Json');
    }

    /** Liste paginée, triée et filtrée pour Tabulator. */
    public function index(): void
    {
        $this->request->allowMethod(['get']);
        $this->Authorization->authorize($this->Roles->newEmptyEntity(), 'index');

        $adapter = new TabulatorAdapter();
        $query = $this->Roles->find('visibleTo', user: $this->getOperator());
        $query = $adapter->adaptRequest($this->request, $query);
        $paginatedData = $this->paginate($query, [
            'limit' => (int)($this->request->getQuery('size') ?? 20),
            'page' => (int)($this->request->getQuery('page') ?? 1),
            'sortableFields' => [],
        ]);
        $output = $adapter->adaptResponse($paginatedData, $this->createGridRightsFormatter());

        $this->set($output);
        $this->viewBuilder()->setOption('serialize', array_keys($output));
    }

    /** Retourne un rôle actif. */
    public function view(string $id): void
    {
        $this->request->allowMethod(['get']);
        $role = $this->getActiveRole($id);
        $this->Authorization->authorize($role, 'view');
        $this->set('data', $role);
        $this->viewBuilder()->setOption('serialize', ['data']);
    }

    /** Crée un rôle non socle. */
    public function add(): Response
    {
        $this->request->allowMethod(['post']);
        $role = $this->Roles->newEmptyEntity();
        $this->Authorization->authorize($role, 'add');
        $role = $this->Roles->patchEntity($role, $this->request->getData(), $this->patchOptions());

        if ($this->Roles->save($role)) {
            return $this->jsonSuccess(['id' => $role->id], __('Le rôle a été créé avec succès.'));
        }

        return $this->validationError($role);
    }

    /** Modifie un rôle non socle. */
    public function edit(string $id): Response
    {
        $this->request->allowMethod(['post', 'put', 'patch']);
        $role = $this->getActiveRole($id);
        $this->Authorization->authorize($role, 'edit');
        $role = $this->Roles->patchEntity($role, $this->request->getData(), $this->patchOptions());

        if ($this->Roles->save($role)) {
            return $this->jsonSuccess(message: __('Le rôle a été mis à jour avec succès.'));
        }

        return $this->validationError($role);
    }

    /** Désactive logiquement un rôle non socle. */
    public function delete(string $id): Response
    {
        $this->request->allowMethod(['post', 'delete']);
        $role = $this->getActiveRole($id);
        $this->Authorization->authorize($role, 'delete');

        if ($this->Roles->softDelete($role)) {
            return $this->jsonSuccess(message: __('Le rôle a été désactivé avec succès.'));
        }

        return $this->jsonError(__('La désactivation du rôle a échoué.'));
    }

    /** @return array{accessibleFields: array{base: false, deleted: false}} */
    private function patchOptions(): array
    {
        return ['accessibleFields' => ['base' => false, 'deleted' => false]];
    }

    /** @return \App\Model\Entity\Role */
    private function getActiveRole(string $id): Role
    {
        /** @var \App\Model\Entity\Role $role */
        $role = $this->Roles->find('visibleTo', user: $this->getOperator())
            ->where(['Roles.id' => $id])
            ->firstOrFail();

        return $role;
    }

    /** @return \App\Model\Entity\User */
    private function getOperator(): User
    {
        /** @var \App\Model\Entity\User $user */
        $user = $this->request->getAttribute('identity')->getOriginalData();

        return $user;
    }

    /** @param \Cake\Datasource\EntityInterface $entity @return \Cake\Http\Response */
    private function validationError(EntityInterface $entity): Response
    {
        return $this->jsonError(__('Le formulaire contient des données invalides.'), $entity->getErrors());
    }

    /** @param array<string, mixed> $data @return \Cake\Http\Response */
    private function jsonSuccess(array $data = [], ?string $message = null): Response
    {
        return $this->response->withType('application/json')->withStringBody((string)json_encode([
            'success' => true,
            'message' => $message,
        ] + $data));
    }

    /** @param array<string, mixed> $errors @return \Cake\Http\Response */
    private function jsonError(string $message, array $errors = []): Response
    {
        return $this->response->withType('application/json')->withStatus(400)->withStringBody((string)json_encode([
            'success' => false,
            'message' => $message,
            'errors' => $errors,
        ]));
    }
}

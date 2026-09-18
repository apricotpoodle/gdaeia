<?php
declare(strict_types=1);

namespace App\Controller;

use App\Model\Entity\Role;
use App\Model\Entity\User;
use Cake\Http\Response;
use Exception;

/**
 * Contrôleur Web : vues HTML et parcours de formulaires des rôles.
 *
 * @property \App\Model\Table\RolesTable $Roles
 */
class RolesController extends AppController
{
    /** Affiche la coquille HTML de la grille distante. */
    public function index(): void
    {
        $this->Authorization->authorize($this->Roles->newEmptyEntity(), 'index');
    }

    /** Consulte un rôle actif. */
    public function view(string $id): void
    {
        $role = $this->getActiveRole($id);
        $this->Authorization->authorize($role, 'view');
        $this->set(compact('role'));
    }

    /** Crée un rôle à partir du formulaire HTML. */
    public function add(): ?Response
    {
        $role = $this->Roles->newEmptyEntity();
        $this->Authorization->authorize($role, 'add');

        if ($this->request->is('post')) {
            $role = $this->Roles->patchEntity($role, $this->request->getData(), $this->patchOptions());
            if ($this->Roles->save($role)) {
                $this->Flash->success(__('Le rôle a été créé avec succès.'));

                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error(__('Le formulaire contient des données invalides.'));
        }

        $this->set(compact('role'));

        return null;
    }

    /** Modifie un rôle non socle. */
    public function edit(string $id): ?Response
    {
        $role = $this->getActiveRole($id);
        $this->Authorization->authorize($role, 'edit');

        if ($this->request->is(['post', 'put', 'patch'])) {
            $role = $this->Roles->patchEntity($role, $this->request->getData(), $this->patchOptions());
            if ($this->Roles->save($role)) {
                $this->Flash->success(__('Le rôle a été mis à jour avec succès.'));

                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error(__('Le formulaire contient des données invalides.'));
        }

        $this->set(compact('role'));

        return null;
    }

    /** Désactive un rôle et répond en JSON lorsque l'appel provient de Tabulator. */
    public function delete(?string $id = null): Response
    {
        $this->request->allowMethod(['post', 'delete']);
        $role = $this->getActiveRole((string)$id);
        $this->Authorization->authorize($role, 'delete');

        $success = false;
        try {
            $success = $this->Roles->softDelete($role);
            if (!$success) {
                throw new Exception(__("L'ORM a refusé la désactivation du rôle."));
            }
            $message = __('Le rôle {0} a été désactivé avec succès.', $role->name);
        } catch (Exception $exception) {
            $message = $exception->getMessage();
        }

        if ($this->request->is('ajax') || $this->request->accepts('application/json')) {
            return $this->response->withType('application/json')->withStatus($success ? 200 : 400)
                ->withStringBody((string)json_encode(compact('success', 'message')));
        }

        $success ? $this->Flash->success($message) : $this->Flash->error($message);

        return $this->redirect(['action' => 'index']) ?? $this->response;
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

    /**
     * Empêche une requête forgée de créer ou de modifier un rôle socle.
     *
     * @return array{accessibleFields: array{base: false, deleted: false}}
     */
    private function patchOptions(): array
    {
        return ['accessibleFields' => ['base' => false, 'deleted' => false]];
    }

    /** @return \App\Model\Entity\User */
    private function getOperator(): User
    {
        /** @var \App\Model\Entity\User $user */
        $user = $this->request->getAttribute('identity')->getOriginalData();

        return $user;
    }
}

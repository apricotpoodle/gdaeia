<?php

declare(strict_types=1);

namespace App\Controller;

use Cake\Http\Response;
use Exception;

/**
 * Class MenusController
 * Gère l'IHM pour le CRUD et la manipulation de l'arbre des menus.
 */
class MenusController extends AppController
{
    /**
     * @return void
     */
    public function index(): void
    {
        $this->Authorization->authorize($this->Menus->newEmptyEntity(), 'index');
    }

    /**
     * @param string $id
     * @return void
     */
    public function view(string $id): void
    {
        $menu = $this->Menus->get($id, contain: ['ParentMenus', 'ChildMenus']);
        $this->Authorization->authorize($menu, 'view');
        $this->set(compact('menu'));
    }

    /**
     * @return \Cake\Http\Response|null
     */
    public function add(): ?Response
    {
        $menu = $this->Menus->newEmptyEntity();
        $this->Authorization->authorize($menu, 'add');

        if ($this->request->is('post')) {
            $menu = $this->Menus->patchEntity($menu, $this->request->getData());
            if ($this->Menus->save($menu)) {
                $this->Flash->success(__('Menu créé avec succès.'));
                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error(__('Impossible de créer le menu.'));
        }

        // $parentMenus = $this->Menus->ParentMenus->find('treeList', spacer: '— ')->toArray();
        $parentMenus = $this->Menus->find('treeList', spacer: '— ')->toArray();
        $this->set(compact('menu', 'parentMenus'));
        return null;
    }

    /**
     * @param string $id
     * @return \Cake\Http\Response|null
     */
    public function edit(string $id): ?Response
    {
        // $this->request->allowMethod(['get']);
        $menu = $this->Menus->get($id);
        $this->Authorization->authorize($menu, 'edit');

        if ($this->request->is(['post', 'put', 'patch'])) {
            $menu = $this->Menus->patchEntity($menu, $this->request->getData());
            if ($this->Menus->save($menu)) {
                $this->Flash->success(__('Menu mis à jour.'));
                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error(__('Erreur lors de la mise à jour.'));
        }

        // $parentMenus = $this->Menus->ParentMenus->find('treeList', spacer: '— ')->toArray();
        $parentMenus = $this->Menus->find('treeList', spacer: '— ')->toArray();
        $this->set(compact('menu', 'parentMenus'));
        return null;
    }

    /**
     * Déplace un nœud vers le haut (Support AJAX hybride).
     *
     * @param string $id
     * @return \Cake\Http\Response|null
     */
    public function moveUp(string $id): ?Response
    {
        $ms_mvup = 'Le menu a été monté avec succès.';
        $me_mvup = 'Impossible de monter le menu (déjà au niveau le plus haut)';
        $this->request->allowMethod(['post', 'put']);
        $menu = $this->Menus->get($id);
        $this->Authorization->authorize($menu, 'moveUp');

        $success = $this->Menus->moveUp($menu);
        $message = $success ? __($ms_mvup) : __($me_mvup);

        if ($this->request->is('ajax') || $this->request->accepts('application/json')) {
            return $this->response->withType('application/json')
                ->withStringBody(json_encode(['success' => $success, 'message' => $message]));
        }

        $success ? $this->Flash->success($message) : $this->Flash->error($message);

        return $this->redirect($this->referer(['action' => 'index']));
    }

    /**
     * Déplace un nœud vers le bas (Support AJAX hybride).
     *
     * @param string $id
     * @return \Cake\Http\Response|null
     */
    public function moveDown(string $id): ?Response
    {
        $ms_mvdn = 'Le menu a été descendu avec succès.';
        $me_mvdn = 'Impossible de descendre le menu (déjà au niveau le plus bas)';
        $this->request->allowMethod(['post', 'put']);
        $menu = $this->Menus->get($id);
        $this->Authorization->authorize($menu, 'moveDown');

        $success = $this->Menus->moveDown($menu);
        $message = $success ? $ms_mvdn : $me_mvdn;

        if ($this->request->is('ajax') || $this->request->accepts('application/json')) {
            return $this->response->withType('application/json')
                ->withStringBody(json_encode(['success' => $success, 'message' => $message]));
        }

        $success ? $this->Flash->success($message) : $this->Flash->error($message);

        return $this->redirect($this->referer(['action' => 'index']));
    }

    /**
     * Suppression hybride
     */
    public function delete(?string $id = null): ?Response
    {
        $this->request->allowMethod(['post', 'delete']);
        $menu = $this->Menus->get($id);
        $this->Authorization->authorize($menu, 'delete');

        $success = false;
        try {
            if ($this->Menus->delete($menu)) {
                $this->Menus->recover();
                $message = __('Menu supprimé.');
                $success = true;
            } else {
                throw new Exception(__("L'ORM a refusé la suppression."));
            }
        } catch (Exception $e) {
            $message = $e->getMessage();
        }

        if ($this->request->is('ajax') || $this->request->accepts('application/json')) {
            return $this->response->withType('application/json')
                ->withStringBody(json_encode(['success' => $success, 'message' => $message]));
        }

        $success ? $this->Flash->success($message) : $this->Flash->error($message);
        return $this->redirect(['action' => 'index']);
    }
}

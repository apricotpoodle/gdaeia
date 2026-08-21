<?php
declare(strict_types=1);

namespace App\Controller;

use Cake\Http\Response;
use Exception;

/**
 * Class ApplicationformsController (Web)
 *
 * Gère l'affichage des vues HTML et l'action de suppression hybride.
 */
class ApplicationformsController extends AppController
{
    public function beforeFilter(\Cake\Event\EventInterface $event): void
    {
        parent::beforeFilter($event);
    }

    /**
     * Action Index (GET / ou /applicationforms)
     */
    public function index(): void
    {
        $this->Authorization->authorize($this->Applicationforms->newEmptyEntity(), 'index');
    }

    /**
     * Action View (GET /applicationforms/view/{id})
     */
    public function view(string $id): void
    {
        $applicationform = $this->Applicationforms->get($id, contain: [
            'Departments',
            'Users',
            'Contracttypes',
            'Hiringreasons',
            'Budgetfeatures',
            'Professionalcategories',
            'Worktimes',
            'Periods',
            'Yesnos',
            'Comments' => ['Users'],
        ]);
        $this->Authorization->authorize($applicationform, 'view');

        $this->set(compact('applicationform'));
    }

    /**
     * Action Add (GET /applicationforms/add)
     */
    public function add(): void
    {
        $applicationform = $this->Applicationforms->newEmptyEntity();
        $this->Authorization->authorize($applicationform, 'add');

        $this->set(compact('applicationform'));
    }

    /**
     * Action Edit (GET/POST /applicationforms/edit/{id})
     *
     * @param string $id Identifiant de la demande.
     * @return \Cake\Http\Response|null Redirection ou rendu HTML.
     */
    public function edit(string $id): ?Response
    {
        // 1. Chargement de l'entité
        $applicationform = $this->Applicationforms->get($id, contain: ['Comments']);

        // 2. Verrou d'autorisation strict (exécuté avant tout traitement)
        $this->Authorization->authorize($applicationform, 'edit');

        // 3. Traitement de la soumission POST / PUT / PATCH
        if ($this->request->is(['post', 'put', 'patch'])) {
            $applicationform = $this->Applicationforms->patchEntity($applicationform, $this->request->getData());

            if ($this->Applicationforms->save($applicationform)) {
                $this->Flash->success(__('La demande de recrutement #{0} a été mise à jour avec succès.', $applicationform->id));

                return $this->redirect(['action' => 'index']);
            }

            $this->Flash->error(__('Impossible de mettre à jour la demande. Veuillez corriger les erreurs ci-dessous.'));
        }

        // 4. Chargement des listes pour le rendu du formulaire
        /** @var \App\Model\Entity\User $currentUser */
        $currentUser = $this->request->getAttribute('identity')->getOriginalData();

        // Récupération des départements autorisés sous forme d'arbre
        // $departments = $this->Applicationforms->Departments
        //     ->find('treeVisibleTo', user: $currentUser)
        //     ->toArray();
        $contracttypes = $this->Applicationforms->Contracttypes->find('list')->toArray();
        $hiringreasons = $this->Applicationforms->Hiringreasons->find('list')->toArray();
        $professionalcategories = $this->Applicationforms->Professionalcategories->find('list')->toArray();
        $worktimes = $this->Applicationforms->Worktimes->find('list')->toArray();
        $periods = $this->Applicationforms->Periods->find('list')->toArray();
        $budgetfeatures = $this->Applicationforms->Budgetfeatures->find('list')->toArray();
        $yesnos = $this->Applicationforms->Yesnos->find('list')->toArray();
        $collaborators = $this->Applicationforms->Users->find('list', keyField: 'id', valueField: 'email')->toArray();

        $this->set(compact(
            'applicationform',
            // 'departments',
            'contracttypes',
            'hiringreasons',
            'professionalcategories',
            'worktimes',
            'periods',
            'budgetfeatures',
            'yesnos',
            'collaborators'
        ));

        return null;
    }

    /**
     * Action Delete Hybride (POST/DELETE /applicationforms/delete/{id})
     *
     * @param string|null $id Identifiant de la demande.
     * @return \Cake\Http\Response|null Redirection ou payload JSON.
     */
    public function delete(?string $id = null): ?Response
    {
        $this->request->allowMethod(['post', 'delete']);

        $applicationform = $this->Applicationforms->get($id);
        $this->Authorization->authorize($applicationform, 'delete');

        $success = false;
        try {
            if ($this->Applicationforms->delete($applicationform)) {
                $message = __('La demande de recrutement #{0} a été supprimée avec succès.', $id);
                $success = true;
            } else {
                throw new Exception(__("L'ORM a refusé la suppression de l'enregistrement."));
            }
        } catch (Exception $e) {
            $message = $e->getMessage();
        }

        if ($this->request->is('ajax') || $this->request->accepts('application/json')) {
            return $this->response
                ->withType('application/json')
                ->withStatus($success ? 200 : 400)
                ->withStringBody(json_encode([
                    'success' => $success,
                    'message' => $message,
                ]));
        }

        if ($success) {
            $this->Flash->success($message);
        } else {
            $this->Flash->error($message);
        }

        return $this->redirect(['action' => 'index']);
    }
}

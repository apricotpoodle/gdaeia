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
        
        // Optionnel : Si une action particulière ne requiert pas de Policy
        // $this->Authorization->skipAuthorization(['actionSansCheck']);
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
            'Comments' => ['Users'], // Charge le fil de discussion et ses auteurs
        ]);
        $this->Authorization->authorize($applicationform, 'view');

        $this->set(compact('applicationform'));
    }

    /**
     * Action Add (GET /applicationforms/add)
     * Livrera la coquille HTML du formulaire de création.
     */
    public function add(): void
    {
        $applicationform = $this->Applicationforms->newEmptyEntity();
        $this->Authorization->authorize($applicationform, 'add');

        $this->set(compact('applicationform'));
    }

    /**
     * Action Edit (GET /applicationforms/edit/{id})
     * Livrera la coquille HTML du formulaire de modification.
     *
     * @param string $id Identifiant de la demande.
     * @return ?Response Redirection ou rendu HTML.
     */
    public function edit(string $id): ?Response
    {
        $applicationform = $this->Applicationforms->get($id, contain: ['Comments']);
        // 1 chargement de l'entité
        $applicationform = $this->Applicationforms->get($id, contain: ['Comments']);
        // 2. Verou d'autorisation strict (doit eetre executế avant tout traitement )
        $this->Authorization->authorize($applicationform, 'edit');
        // 3. Traitement de la soumission POST / PUT
        if ($this->request->is(['post', 'put', 'patch'])) {
            $applicationform = $this->Applicationforms->patchEntity($applicationform, $this->request->getData());

        // Récupération des listes pour les selects
            if ($this->Applicationforms->save($applicationform)) {
                $this->Flash->success(__('La demande de recrutement #{0} a été mise à jour avec succès.', $applicationform->id));

                return $this->redirect(['action' => 'index']);
            }

            $this->Flash->error(__('Impossible de mettre à jour la demande. Veuillez corriger les erreurs ci-dessous.'));
        }

        // 4. Chargement des listes pour le rendu du formulaire
        $departments = $this->Applicationforms->Departments->find('list')->toArray();
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
            'departments',
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
     * Intercepte XHR pour Tabulator ou effectue une redirection HTML avec Flash.
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

        // Interception XHR/AJAX pour la grille Tabulator
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

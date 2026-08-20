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
        $this->Authorization->authorize($this->Applicationforms->newEmptyEntity(), 'add');
    }

    /**
     * Action Edit (GET /applicationforms/edit/{id})
     * Livrera la coquille HTML du formulaire de modification.
     */
    public function edit(string $id): void
    {
        $applicationform = $this->Applicationforms->get($id);
        $this->Authorization->authorize($applicationform, 'edit');

        $this->set(compact('applicationform'));
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

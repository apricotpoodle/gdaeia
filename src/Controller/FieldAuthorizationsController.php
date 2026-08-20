<?php
declare(strict_types=1);

namespace App\Controller;

use Exception;

class FieldAuthorizationsController extends AppController
{
    /**
     * Méthode Index (GET /field-authorizations)
     * Rend le gabarit HTML contenant le conteneur pour la grille Tabulator.
     */
    public function index(): void
    {
        $this->Authorization->authorize($this->FieldAuthorizations->newEmptyEntity(), 'index');
    }

    /**
     * Action Add (GET /field-authorizations/add)
     * Affiche le formulaire HTML de création. L'insertion réelle est déléguée à l'API.
     */
    public function add(): void
    {
        $this->Authorization->authorize($this->FieldAuthorizations->newEmptyEntity(), 'add');
    }

    /**
     * Action Edit (GET /field-authorizations/edit/{id})
     * Affiche le formulaire HTML d'édition pré-rempli.
     */
    public function edit(string $id): void
    {
        $fieldAuthorization = $this->FieldAuthorizations->get($id);
        $this->Authorization->authorize($fieldAuthorization, 'edit');

        $this->set(compact('fieldAuthorization'));
    }

    /**
     * Action de suppression hybride (POST/DELETE).
     *
     * @param string|null $id Identifiant de l'autorisation.
     * @return \Cake\Http\Response|null Redirection ou payload JSON.
     */
    public function delete(?string $id = null)
    {
        $this->request->allowMethod(['post', 'delete']);

        $fieldAuthorization = $this->FieldAuthorizations->get($id);
        $this->Authorization->authorize($fieldAuthorization, 'delete');

        $success = false;
        try {
            if ($this->FieldAuthorizations->delete($fieldAuthorization)) {
                $message = __("La règle d'autorisation a été supprimée avec succès.");
                $success = true;
            } else {
                throw new Exception(__("L'ORM a refusé la suppression de l'enregistrement."));
            }
        } catch (Exception $e) {
            $message = $e->getMessage();
        }

        // Interception XHR/AJAX pour Tabulator
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

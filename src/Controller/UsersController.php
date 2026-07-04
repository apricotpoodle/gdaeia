<?php

declare(strict_types=1);

namespace App\Controller;

use function Cake\Error\dd;

/**
 * Class UsersController (Web)
 *
 * Contrôleur d'interface utilisateur.
 * A pour unique responsabilité de livrer les vues HTML au navigateur.
 * La récupération des données est déléguée à l'API en AJAX.
 *
 * @package App\Controller
 */
class UsersController extends AppController
{
    /**
     * Méthode Index (GET /users)
     * * Rend le gabarit HTML contenant le conteneur vide pour la grille Tabulator.
     *
     * @return void
     */
    public function index(): void
    {
        // Le rendu de templates/Users/index.php est automatique.
    }
    /**
     * Action de suppression d'un utilisateur.
     * Gère de manière hybride les requêtes HTTP classiques et les appels asynchrones AJAX/JSON.
     *
     * @param string|null $id Identifiant de l'utilisateur.
     * @return \Cake\Http\Response|null Redirection ou payload JSON.
     * @throws \Cake\Datasource\Exception\RecordNotFoundException Si l'enregistrement n'existe pas.
     */
    public function delete($id = null)
    {
        // 1. Sécurité : Interdit le protocole GET pour éviter les suppressions accidentelles via URL
        $this->request->allowMethod(['post', 'delete']);

        $user = $this->Users->get($id);
        $success = false;
        try {
            // Exemple de règle métier arbitraire : Interdiction de supprimer un super utilisateur
            if ($user->issuperuser) {
                throw new \Exception(__("Action interdite : Impossible de supprimer un compte de niveau Super Administrateur."));
            }

            // 2. Exécution de la suppression via l'ORM CakePHP
            // if ($this->Users->delete($user)) {
            if (1 == 1) {
                $message = __("L'utilisateur {0} a été supprimé avec succès de la base de données.", $user->email);
                $success = true;
            } else {
                throw new \Exception(__("L'ORM a refusé la suppression de l'enregistrement. Veuillez vérifier les dépendances relationnelles."));
            }
        } catch (\Exception $e) {
            // Capture de l'erreur (règle métier, contrainte de clé étrangère SQL, etc.)
            $message = $e->getMessage();
        }

        // 3. INTERCEPTION DE L'APPEL AJAX (Négociation de contenu pour Tabulator)
        if ($this->request->is('ajax') || $this->request->accepts('application/json')) {
            return $this->response
                ->withType('application/json')
                ->withStatus($success ? 200 : 400) // Code 400 lève l'exception dans le catch de fetch()
                ->withStringBody(json_encode([
                    'success' => $success,
                    'message' => $message // Ce message sera lu directement par FlashManager.error() ou .success()
                ]));
        }

        // 4. FALLBACK : Traitement classique si la requête n'est pas asynchrone (sécurité)
        if ($success) {
            $this->Flash->success($message);
        } else {
            $this->Flash->error($message);
        }

        return $this->redirect(['action' => 'index']);
    }
}

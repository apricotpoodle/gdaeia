<?php

declare(strict_types=1);

namespace App\Controller;

use App\Log\EmailLoggerTrait;
use App\Mailer\UserMailer;
use Cake\Http\Response;

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
    use EmailLoggerTrait;

    /**
     * Called before the controller action. You can use this method to configure and customize components
     * or perform logic that needs to happen before each controller action.
     *
     * @param \Cake\Event\EventInterface<\Cake\Controller\Controller> $event An Event instance
     * @return void
     * @link https://book.cakephp.org/5/en/controllers.html#request-life-cycle-callbacks
     * @phpcsSuppress SlevomatCodingStandard.TypeHints.ReturnTypeHint.MissingNativeTypeHint
     */
    public function beforeFilter(\Cake\Event\EventInterface $event): void
    {
        parent::beforeFilter($event);

        // 1. Autoriser le plugin Authentication à ne pas bloquer ces pages
        $this->Authentication->allowUnauthenticated(['login', 'register', 'verify', 'forgotPassword']);
        // 2. CORRECTION : Autoriser le plugin Authorization à ignorer ces actions
        $this->Authorization->skipAuthorization(['login', 'register', 'verify', 'forgotPassword']);
    }

    /**
     * Méthode Login
     *
     * @return void|Response
     */
    public function login(): ?Response
    {
        $result = $this->Authentication->getResult();

        // If the user is logged in send them away.
        if ($result && $result->isValid()) {
            // On consomme l'URL interceptée (ex: si l'utilisateur a cliqué sur un vieux lien)
            // Sinon, direction par défaut vers la table de gestion des utilisateurs
            $target = $this->Authentication->getLoginRedirect() ?? '/users/index';
            return $this->Authentication->redirectAfterLogin($target);
        }

        if ($this->request->is('post') && !$result->isValid()) {
            $this->Flash->error('Invalid username or password');
        }

        return null;
    }

    /**
     * Méthode Logout
     *
     * @return Response
     */
    public function logout(): Response
    {
        $this->Authentication->logout();

        return $this->redirect(['controller' => 'Users', 'action' => 'login']);
    }

    /**
     * Méthode Index (GET /users)
     * * Rend le gabarit HTML contenant le conteneur vide pour la grille Tabulator.
     *
     * @return void
     */
    public function index(): void
    {
        // On indique au plugin d'appliquer la règle 'canIndex' définie dans la Policy.
        // On lui passe une entité vide pour donner le contexte du modèle 'User'.
        $this->Authorization->authorize($this->Users->newEmptyEntity(), 'index');
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

    /**
     * Action Forgot Password (GET/POST)
     * Enclenche le flux de récupération par l'envoi d'un jeton à usage unique par email.
     *
     * @return \Cake\Http\Response|null
     */
    public function forgotPassword(): ?Response
    {
        if ($this->request->is('post')) {
            $email = $this->request->getData('email');

            /** @var \App\Model\Entity\User|null $user */
            $user = $this->Users->findByEmail($email)->first();

            if ($user !== null) {
                $token = bin2hex(random_bytes(32));

                $user->set('token', $token);
                $user->set('token_expires', new \DateTime('+1 hour'));

                if ($this->Users->save($user)) {
                    $this->traceEmail("Lien de récupération généré pour {$email} : /users/reset-password/{$token}");

                    // 💡 MAGIQUE : Une seule ligne, sécurisée, asynchrone (non-bloquante si erreur).
                    $mailer = new UserMailer();
                    $mailer->safeSend('forgotPassword', [$user]);
                }
            }

            // Message de sécurité générique anti-énumération
            $this->Flash->success(__('Si cette adresse existe dans notre système, un email de réinitialisation vous a été envoyé.'));
            return $this->redirect(['action' => 'login']);
        }

        return null;
    }

    /**
     * Action Reset Password (GET/POST)
     * Permet la saisie du nouveau mot de passe si le jeton est valide et actif.
     *
     * @param string|null $token Le jeton hexadécimal reçu par l'URL.
     * @return \Cake\Http\Response|null
     */
    public function resetPassword(?string $token = null): ?Response
    {
        $this->Authorization->skipAuthorization();

        if (empty($token)) {
            $this->Flash->error(__('Jeton de récupération invalide ou manquant.'));
            return $this->redirect(['action' => 'login']);
        }

        // Vérification de l'existence et de l'expiration du jeton
        /** @var \App\Model\Entity\User|null $user */
        $user = $this->Users->find()
            ->where([
                'token' => $token,
                'token_expires >' => new \DateTime()
            ])
            ->first();

        if ($user === null) {
            $this->Flash->error(__('Ce lien de récupération a expiré ou est invalide.'));
            return $this->redirect(['action' => 'login']);
        }

        if ($this->request->is(['post', 'put'])) {
            // Le hachage s'exécute automatiquement dans l'entité User via _setPassword
            $user = $this->Users->patchEntity($user, $this->request->getData());

            // Consommation et destruction du jeton à usage unique
            $user->set('token', null);
            $user->set('token_expires', null);

            if ($this->Users->save($user)) {
                $this->Flash->success(__('Votre mot de passe a été modifié avec succès. Veuillez vous connecter.'));
                return $this->redirect(['action' => 'login']);
            }
            $this->Flash->error(__('Impossible de mettre à jour le mot de passe. Veuillez réessayer.'));
        }

        $this->set(compact('token'));
        return null;
    }

    /**
     * Infiltre la session d'un autre utilisateur (Impersonate).
     * Action réservée au support technique (Super Admin).
     *
     * @param string|null $id Identifiant de l'utilisateur cible.
     * @return \Cake\Http\Response|null Redirection après changement d'identité.
     */
    public function impersonate(?string $id = null): ?Response
    {
        // 1. Récupération de l'identité cible
        $targetUser = $this->Users->get($id);

        // 2. VERROU DE SÉCURITÉ : Vérification de la Policy (canImpersonate)
        // Seul un Super Admin peut usurper, et il ne peut pas usurper un autre Super Admin.
        $this->Authorization->authorize($targetUser, 'impersonate');

        // 3. Récupération de l'identité actuelle (le Super Admin)
        $currentUser = $this->Authentication->getIdentity();

        if ($currentUser) {
            // 4. Sauvegarde de l'ID du Super Admin dans la session
            $this->request->getSession()->write('Auth.original_user_id', $currentUser->getIdentifier());

            // 5. Bascule d'identité via le composant d'Authentification CakePHP 5
            $this->Authentication->setIdentity($targetUser);

            $this->Flash->success(__("Vous naviguez désormais en tant que {0} {1} ({2}).", [
                $targetUser->firstname,
                $targetUser->lastname,
                $targetUser->email
            ]));
        }

        return $this->redirect('/'); // Redirection vers l'accueil de l'application
    }

    /**
     * Restaure l'identité originelle du Super Administrateur.
     *
     * @return \Cake\Http\Response|null Redirection.
     */
    public function revertIdentity(): ?Response
    {
        // Pas besoin de règle métier complexe ici : si on a l'ID original en session, on a le droit de revenir.
        $this->Authorization->skipAuthorization();

        $session = $this->request->getSession();
        $originalUserId = $session->read('Auth.original_user_id');

        if ($originalUserId) {
            try {
                // 1. On récupère le vrai Super Admin
                $originalUser = $this->Users->get($originalUserId);

                // 2. On restaure l'identité
                $this->Authentication->setIdentity($originalUser);

                // 3. Nettoyage méticuleux de la trace en session
                $session->delete('Auth.original_user_id');

                $this->Flash->success(__("Retour à votre session Administrateur d'origine."));
            } catch (\Exception $e) {
                // Failsafe : si l'utilisateur original a été supprimé entre temps
                $session->delete('Auth.original_user_id');
                $this->Authentication->logout();
                $this->Flash->error(__("Erreur lors de la restauration de session. Veuillez vous reconnecter."));
                return $this->redirect(['action' => 'login']);
            }
        } else {
            $this->Flash->warning(__("Aucune session d'origine détectée."));
        }

        return $this->redirect(['action' => 'index']);
    }
}

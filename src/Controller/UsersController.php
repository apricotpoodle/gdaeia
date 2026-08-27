<?php
declare(strict_types=1);

namespace App\Controller;

use App\Log\EmailLoggerTrait;
use App\Mailer\UserMailer;
use App\Service\Security\FieldAuthorizationService;
use Cake\Event\EventInterface;
use Cake\Http\Exception\NotFoundException;
use Cake\Http\Response;
use DateTime;
use Exception;

/**
 * Class UsersController (Web)
 *
 * Contrôleur d'interface utilisateur pour la gestion des entités User.
 * A pour responsabilité la livraison des vues HTML et la gestion des formulaires.
 *
 * @package App\Controller
 * @property \App\Model\Table\UsersTable $Users
 */
class UsersController extends AppController
{
    use EmailLoggerTrait;

    /**
     * Callback avant filtrage.
     * Configure l'accès anonyme pour les actions de réinitialisation de mot de passe.
     *
     * @param \Cake\Event\EventInterface $event L'événement courant.
     * @return void
     */
    public function beforeFilter(EventInterface $event): void
    {
        parent::beforeFilter($event);

        $this->Authentication->allowUnauthenticated(['login', 'register', 'verify', 'forgotPassword', 'resetPassword']);
        $this->Authorization->skipAuthorization(['login', 'register', 'verify', 'forgotPassword', 'resetPassword']);
    }

    /**
     * Action Login (GET/POST /users/login)
     *
     * @return \Cake\Http\Response|null Redirection ou rendu du formulaire.
     */
    public function login(): ?Response
    {
        $result = $this->Authentication->getResult();

        if ($result && $result->isValid()) {
            $target = $this->Authentication->getLoginRedirect() ?? '/users/index';

            return $this->Authentication->redirectAfterLogin($target);
        }

        if ($this->request->is('post') && !$result->isValid()) {
            $this->Flash->error(__('Identifiant ou mot de passe invalide.'));
        }

        return null;
    }

    /**
     * Action Logout (GET /users/logout)
     *
     * @return \Cake\Http\Response Redirection vers la page de connexion.
     */
    public function logout(): Response
    {
        $this->Authentication->logout();

        return $this->redirect(['controller' => 'Users', 'action' => 'login']);
    }

    /**
     * Action Index (GET /users)
     * Rend le gabarit HTML contenant la grille Tabulator.
     *
     * @return void
     */
    public function index(): void
    {
        $this->Authorization->authorize($this->Users->newEmptyEntity(), 'index');
    }

    /**
     * Action View (GET /users/view/{id})
     * Affiche le profil détaillé d'un utilisateur et ses départements rattachés.
     *
     * @param string $id Identifiant de l'utilisateur.
     * @return void
     */
    public function view(string $id): void
    {
        $user = $this->Users->get($id, contain: [
            'Roles',
            'UserDepartments' => ['Departments'],
        ]);

        $this->Authorization->authorize($user, 'view');

        $identity = $this->request->getAttribute('identity');
        /** @var \App\Model\Entity\User $currentUser */
        $currentUser = $identity->getOriginalData();

        $authService = new FieldAuthorizationService();
        $fieldSchema = $authService->getFieldSchema($identity, 'Users');

        // Récupération de l'arborescence des départements selon le périmètre de l'opérateur
        $departmentsTree = $this->fetchTable('Departments')->findTreeSelectFormat($currentUser);

        // Extraction des identifiants des départements rattachés sous forme d'IDs numériques
        $selectedDepartmentIds = [];
        if (!empty($user->user_departments)) {
            foreach ($user->user_departments as $userDept) {
                $selectedDepartmentIds[] = (int)$userDept->department_id;
            }
        }

        $this->set(compact('user', 'fieldSchema', 'departmentsTree', 'selectedDepartmentIds'));
    }

    /**
     * Action Add (GET/POST /users/add)
     * Création d'un utilisateur et association avec son périmètre de départements.
     *
     * @return \Cake\Http\Response|null Redirection en cas de succès ou rendu du formulaire.
     */
    public function add(): ?Response
    {
        $user = $this->Users->newEmptyEntity();
        $this->Authorization->authorize($user, 'add');

        if ($this->request->is('post')) {
            $user = $this->Users->patchEntity($user, $this->request->getData(), [
                'associated' => ['UserDepartments'],
            ]);

            if ($this->Users->save($user)) {
                $this->Flash->success(__('L\'utilisateur a été créé avec succès.'));

                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error(__('Impossible de créer l\'utilisateur. Veuillez vérifier les erreurs du formulaire.'));
        }

        $identity = $this->request->getAttribute('identity');
        /** @var \App\Model\Entity\User $currentUser */
        $currentUser = $identity->getOriginalData();

        $authService = new FieldAuthorizationService();
        $fieldSchema = $authService->getFieldSchema($identity, 'Users');

        // Récupération de la liste des rôles applicatifs
        $roles = $this->Users->Roles->find('list', keyField: 'id', valueField: 'name')
            ->orderBy(['Roles.name' => 'ASC'])
            ->toArray();

        // Récupération de l'arborescence des départements autorisés selon le périmètre de l'opérateur
        $departmentsTree = $this->fetchTable('Departments')->findTreeSelectFormat($currentUser);
        $selectedDepartmentIds = [];

        $this->set(compact('user', 'roles', 'fieldSchema', 'departmentsTree', 'selectedDepartmentIds'));

        return null;
    }

    /**
     * Action Edit (GET/POST /users/edit/{id})
     * Modification d'un utilisateur et mise à jour de ses départements rattachés.
     *
     * @param string $id Identifiant de l'utilisateur.
     * @return \Cake\Http\Response|null Redirection en cas de succès ou rendu du formulaire.
     */
    public function edit(string $id): ?Response
    {
        $user = $this->Users->get($id, contain: ['UserDepartments']);
        $this->Authorization->authorize($user, 'edit');

        if ($this->request->is(['post', 'put', 'patch'])) {
            $user = $this->Users->patchEntity($user, $this->request->getData(), [
                'associated' => ['UserDepartments'],
            ]);

            if ($this->Users->save($user)) {
                $this->Flash->success(__('L\'utilisateur #{0} a été mis à jour avec succès.', $user->id));

                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error(__('Impossible de mettre à jour l\'utilisateur. Veuillez corriger les erreurs.'));
        }

        $identity = $this->request->getAttribute('identity');
        /** @var \App\Model\Entity\User $currentUser */
        $currentUser = $identity->getOriginalData();

        $authService = new FieldAuthorizationService();
        $fieldSchema = $authService->getFieldSchema($identity, 'Users');

        $roles = $this->Users->Roles->find('list', keyField: 'id', valueField: 'name')
            ->orderBy(['Roles.name' => 'ASC'])
            ->toArray();

        // Arborescence complète disponible pour l'opérateur
        $departmentsTree = $this->fetchTable('Departments')->findTreeSelectFormat($currentUser);

        // Extraction des identifiants des départements déjà associés au format typé int
        $selectedDepartmentIds = [];
        if (!empty($user->user_departments)) {
            foreach ($user->user_departments as $userDept) {
                $selectedDepartmentIds[] = (int)$userDept->department_id;
            }
        }

        $this->set(compact('user', 'roles', 'fieldSchema', 'departmentsTree', 'selectedDepartmentIds'));

        return null;
    }

    /**
     * Action Delete Hybride (POST/DELETE /users/delete/{id})
     *
     * @param string|null $id Identifiant de l'utilisateur.
     * @return \Cake\Http\Response|null Redirection ou payload JSON.
     */
    public function delete(?string $id = null): ?Response
    {
        $this->request->allowMethod(['post', 'delete']);

        $user = $this->Users->get($id);
        $this->Authorization->authorize($user, 'delete');

        $success = false;
        try {
            if ($user->issuperuser) {
                throw new Exception(__('Action interdite : Impossible de supprimer un compte Super Administrateur.'));
            }

            if ($this->Users->delete($user)) {
                $message = __('L\'utilisateur {0} a été supprimé avec succès.', $user->email);
                $success = true;
            } else {
                throw new Exception(__('L\'ORM a refusé la suppression de l\'enregistrement.'));
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

    /**
     * Action Forgot Password (GET/POST)
     * Envoie un jeton de réinitialisation par courriel.
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
                $user->set('token_expires', new DateTime('+1 hour'));

                if ($this->Users->save($user)) {
                    $this->traceEmail("Lien de récupération généré pour {$email} : /users/reset-password/{$token}");

                    $mailer = new UserMailer();
                    $mailer->safeSend('forgotPassword', [$user]);
                }
            }

            $this->Flash->success(__('Si cette adresse existe dans notre système, un email de réinitialisation vous a été envoyé.'));

            return $this->redirect(['action' => 'login']);
        }

        return null;
    }

    /**
     * Action Reset Password (GET/POST)
     * Saisie du nouveau mot de passe avec jeton à usage unique.
     *
     * @param string|null $token Jeton de sécurité.
     * @return \Cake\Http\Response|null
     */
    public function resetPassword(?string $token = null): ?Response
    {
        $this->Authorization->skipAuthorization();

        if (empty($token)) {
            $this->Flash->error(__('Jeton de récupération invalide ou manquant.'));

            return $this->redirect(['action' => 'login']);
        }

        /** @var \App\Model\Entity\User|null $user */
        $user = $this->Users->find()
            ->where([
                'token' => $token,
                'token_expires >' => new DateTime(),
            ])
            ->first();

        if ($user === null) {
            $this->Flash->error(__('Ce lien de récupération a expiré ou est invalide.'));

            return $this->redirect(['action' => 'login']);
        }

        if ($this->request->is(['post', 'put'])) {
            $user = $this->Users->patchEntity($user, $this->request->getData());
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
     * Usurpation d'identité (Impersonate)
     *
     * @param string|null $id Identifiant de l'utilisateur cible.
     * @return \Cake\Http\Response|null
     */
    public function impersonate(?string $id = null): ?Response
    {
        // 1. Verrou : Interdiction d'usurper une identité si l'on est DÉJÀ en train d'en incarner une
        if ($this->Authentication->isImpersonating()) {
            $this->Flash->error(__('Vous êtes déjà en mode usurpation d\'identité. Veuillez revenir à votre session d\'origine avant de réitérer.'));

            return $this->redirect(['action' => 'index']);
        }
        $targetUser = $this->Users->get($id);
        $this->Authorization->authorize($targetUser, 'impersonate');

        $this->Authentication->impersonate($targetUser);

        return $this->redirect('/');
    }

    /**
     * Annulation de l'usurpation d'identité
     *
     * @return \Cake\Http\Response|null
     */
    public function revertIdentity(): ?Response
    {
        $this->Authorization->skipAuthorization();
        // Pour être sûr que nous toujours en train d'incarner quelqu'un
        if (!$this->Authentication->isImpersonating()) {
            throw new NotFoundException();
        }
        if ($this->Authentication->isImpersonating()) {
            $this->Authentication->stopImpersonating();
        } else {
            $this->Flash->warning(__('Aucune session d\'origine détectée.'));
        }

        return $this->redirect(['action' => 'index']);
    }
}

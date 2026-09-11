<?php
declare(strict_types=1);

namespace App\Controller;

use Cake\Datasource\EntityInterface;
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
            'Departments' => [
                'ParentDepartments', // 👈 Pour le fil d'Ariane
                'Managers',          // 👈 Pour le chef de service
            ],
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

        // 💡 Récupération de l'arborescence complète via le TreeBehavior
        $departmentPath = [];
        if ($applicationform->department_id) {
            $departmentPath = $this->fetchTable('Departments')
                ->find('path', for: $applicationform->department_id)
                ->all()
                ->toArray();
        }

        $this->set(compact('applicationform', 'departmentPath'));
    }

    /**
     * Action Add (GET /applicationforms/add)
     *
     * @return \Cake\Http\Response|null
     */
    public function add(): ?Response
    {
        $applicationform = $this->Applicationforms->newEmptyEntity();
        $this->Authorization->authorize($applicationform, 'add');

        if ($this->request->is('post')) {
            $applicationform = $this->Applicationforms->patchEntity($applicationform, $this->request->getData());

            // Attribution de l'utilisateur créateur
            $identity = $this->request->getAttribute('identity');
            /** @var \App\Model\Entity\User $currentUser */
            $currentUser = $identity->getOriginalData();
            $applicationform->user_id = $currentUser->id;

            if ($this->Applicationforms->save($applicationform)) {
                $this->Flash->success(__('La demande de recrutement a été créée avec succès.'));

                return $this->redirect(['action' => 'index']);
            }
            $this->Flash->error($this->formatApplicationformValidationError($applicationform));
        }

        // Récupération des données de référence et de sécurité (ADR 0042 / 0046)
        $authService = new \App\Service\Security\FieldAuthorizationService();
        $identity = $this->request->getAttribute('identity');
        $fieldSchema = $authService->getFieldSchema($identity, 'Applicationforms');

        /** @var \App\Model\Entity\User $currentUser */
        $currentUser = $identity->getOriginalData();

        // Listes de références filtrées par le périmètre (visibleTo)
        $contracttypes = $this->Applicationforms->Contracttypes->find('visibleTo', user: $currentUser)->find('list')->toArray();
        $hiringreasons = $this->Applicationforms->Hiringreasons->find('visibleTo', user: $currentUser)->find('list')->toArray();
        $professionalcategories = $this->Applicationforms->Professionalcategories->find('visibleTo', user: $currentUser)->find('list')->toArray();
        $worktimes = $this->Applicationforms->Worktimes->find('visibleTo', user: $currentUser)->find('list')->toArray();
        $periods = $this->Applicationforms->Periods->find('visibleTo', user: $currentUser)->find('list')->toArray();
        $budgetfeatures = $this->Applicationforms->Budgetfeatures->find('visibleTo', user: $currentUser)->find('list')->toArray();
        $yesnos = $this->Applicationforms->Yesnos->find('visibleTo', user: $currentUser)->find('list')->toArray();
        $collaborators = $this->Applicationforms->Users->find('visibleTo', user: $currentUser)->find('list', keyField: 'id', valueField: 'display_name')->toArray();

        $this->set(compact(
            'applicationform',
            'fieldSchema',
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

            $this->Flash->error($this->formatApplicationformValidationError($applicationform));
        }

        // 4. Chargement des listes pour le rendu du formulaire
        /** @var \App\Model\Entity\User $currentUser */
        $currentUser = $this->request->getAttribute('identity')->getOriginalData();

        // Récupération des départements autorisés sous forme d'arbre
        // $departments = $this->Applicationforms->Departments
        //     ->find('treeVisibleTo', user: $currentUser)
        //     ->toArray();
        $contracttypes = $this->Applicationforms->Contracttypes->getVisibleList($currentUser);   //   ->find('visibleTo', user: $currentUser)->find('list')->toArray();
        $hiringreasons = $this->Applicationforms->Hiringreasons->getVisibleList($currentUser);
        $professionalcategories = $this->Applicationforms->Professionalcategories->getVisibleList($currentUser);
        $worktimes = $this->Applicationforms->Worktimes->getVisibleList($currentUser);
        $periods = $this->Applicationforms->Periods->getVisibleList($currentUser);
        $budgetfeatures = $this->Applicationforms->Budgetfeatures->getVisibleList($currentUser);
        $yesnos = $this->Applicationforms->Yesnos->getVisibleList($currentUser);

        // 💡 FILTRAGE STRICT DES COLLABORATEURS SELON LE PÉRIMÈTRE UTILISATEUR
        $collaborators = $this->Applicationforms->Users
            ->find('visibleTo', user: $currentUser)
            ->find('list', keyField: 'id', valueField: 'display_name')
            ->toArray();

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

    /**
     * Produit un message de validation immédiatement exploitable dans l'interface Web.
     *
     * @param \Cake\Datasource\EntityInterface $entity Entité dont la sauvegarde a échoué.
     * @return string
     */
    private function formatApplicationformValidationError(EntityInterface $entity): string
    {
        $error = $this->findFirstValidationError($entity->getErrors());
        if ($error === null) {
            return __('Impossible d\'enregistrer la demande : le serveur n\'a pas retourné de détail de validation.');
        }

        [$field, $message] = $error;

        return __('Champ « {0} » : {1}', $this->getApplicationformFieldLabel($field), $message);
    }

    /**
     * Extrait la première erreur en préservant le champ métier qui la porte.
     *
     * @param array<string, mixed> $errors Erreurs produites par l'ORM CakePHP.
     * @param string|null $rootField Champ racine pour les associations imbriquées.
     * @return array{0: string, 1: string}|null
     */
    private function findFirstValidationError(array $errors, ?string $rootField = null): ?array
    {
        foreach ($errors as $field => $details) {
            $currentRootField = $rootField ?? (string)$field;
            if (is_string($details)) {
                return [$currentRootField, $details];
            }

            if (is_array($details)) {
                $error = $this->findFirstValidationError($details, $currentRootField);
                if ($error !== null) {
                    return $error;
                }
            }
        }

        return null;
    }

    /**
     * Retourne le libellé fonctionnel d'un champ de demande de recrutement.
     *
     * @param string $field Nom technique du champ.
     * @return string
     */
    private function getApplicationformFieldLabel(string $field): string
    {
        return [
            'department_id' => __('Département'),
            'user_id' => __('Créateur de la demande'),
            'cgr' => __('Code CGR'),
            'contracttype_id' => __('Type de contrat'),
            'hiringreason_id' => __('Motif de recrutement'),
            'reasonforreplacement' => __('Précision du motif'),
            'budgetfeature_id' => __('Imputation budgétaire'),
            'jobtitle' => __('Intitulé du poste'),
            'professionalcategory_id' => __('Catégorie professionnelle'),
            'worktime_id' => __('Temps de travail'),
            'workingtimedistribution' => __('Répartition du temps de travail'),
            'grossremuneration' => __('Rémunération brute'),
            'period_id' => __('Périodicité'),
            'qualification' => __('Qualification'),
            'begin_at' => __('Date de début'),
            'end_at' => __('Date de fin'),
            'applicantname' => __('Nom du candidat'),
            'yesno_id' => __('Champ Oui/Non'),
        ][$field] ?? $field;
    }
}

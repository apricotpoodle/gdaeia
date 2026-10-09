<?php
declare(strict_types=1);

namespace App\Controller;

use App\Model\Entity\Applicationform;
use App\Model\Entity\User;
use App\Service\Pdf\ApplicationformPdfService;
use App\Service\Security\FieldAuthorizationService;
use App\Service\Workflow\ApplicationformValidationWorkflow;
use Cake\Event\EventInterface;
use Cake\Http\Response;
use Exception;

/**
 * Class ApplicationformsController (Web)
 *
 * Gère l'affichage des vues HTML et l'action de suppression hybride.
 *
 * @property \App\Model\Table\ApplicationformsTable $Applicationforms
 */
class ApplicationformsController extends AppController
{
    /** @inheritDoc */
    public function beforeFilter(EventInterface $event): void
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

    /** Affiche les cycles de validation dont une étape dépasse le délai ouvré. */
    public function blockedValidations(): void
    {
        $subject = $this->Applicationforms->newEmptyEntity();
        $this->Authorization->authorize($subject, 'viewBlockedValidations');
    }

    /**
     * Action View (GET /applicationforms/view/{id})
     */
    public function view(string $id): void
    {
        $applicationform = $this->Applicationforms->get($id, contain: [
            'Departments' => [
                'ParentDepartments', // 👈 Pour le fil d'Ariane
                'Managers', // 👈 Pour le chef de service
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
            'ValidationWorkflowRuns',
        ]);

        $this->Authorization->authorize($applicationform, 'view');

        // 💡 Récupération de l'arborescence complète via le TreeBehavior
        $departmentPath = [];
        if ($applicationform->get(Applicationform::FIELD_DEPARTMENT_ID)) {
            $departmentPath = $this->fetchTable('Departments')
                ->find('path', for: $applicationform->get(Applicationform::FIELD_DEPARTMENT_ID))
                ->all()
                ->toArray();
        }

        $this->set(compact('applicationform', 'departmentPath'));
    }

    /**
     * Produit le PDF sécurisé d'une DAE (GET /applicationforms/viewpdf/{id}).
     *
     * @param string $id Identifiant de la demande.
     * @return \Cake\Http\Response Réponse PDF inline.
     */
    public function viewpdf(string $id): Response
    {
        $applicationform = $this->Applicationforms->get($id, contain: [
            'Departments', 'Contracttypes', 'Hiringreasons', 'Budgetfeatures',
            'Professionalcategories', 'Worktimes', 'Periods',
        ]);
        $this->Authorization->authorize($applicationform, 'viewpdf');
        $identity = $this->request->getAttribute('identity');
        $content = (new ApplicationformPdfService())->generate($applicationform, $identity);

        return $this->response
            ->withType('application/pdf')
            ->withHeader(
                'Content-Disposition',
                sprintf('inline; filename="dae-%s.pdf"', $applicationform->get(Applicationform::FIELD_ID)),
            )
            ->withStringBody($content);
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
            $applicationform->set(Applicationform::FIELD_USER_ID, $currentUser->get(User::FIELD_ID));

            if ($this->Applicationforms->save($applicationform)) {
                $this->Flash->success(__('La demande de recrutement a été créée avec succès.'));

                return $this->redirect(['action' => 'index']);
            }
            $this->flashValidationErrors($applicationform, 'Applicationforms');
        }

        // Récupération des données de référence et de sécurité (ADR 0042 / 0046)
        $authService = new FieldAuthorizationService();
        $identity = $this->request->getAttribute('identity');
        $fieldSchema = $authService->getFieldSchema($identity, 'Applicationforms');

        /** @var \App\Model\Entity\User $currentUser */
        $currentUser = $identity->getOriginalData();

        // Listes de références filtrées par le périmètre (visibleTo)
        $contracttypes = $this->Applicationforms->Contracttypes
            ->find('visibleTo', user: $currentUser)->find('list')->toArray();
        $hiringreasons = $this->Applicationforms->Hiringreasons
            ->find('visibleTo', user: $currentUser)->find('list')->toArray();
        $professionalcategories = $this->Applicationforms->Professionalcategories
            ->find('visibleTo', user: $currentUser)->find('list')->toArray();
        $worktimes = $this->Applicationforms->Worktimes
            ->find('visibleTo', user: $currentUser)->find('list')->toArray();
        $periods = $this->Applicationforms->Periods
            ->find('visibleTo', user: $currentUser)->find('list')->toArray();
        $budgetfeatures = $this->Applicationforms->Budgetfeatures
            ->find('visibleTo', user: $currentUser)->find('list')->toArray();
        $yesnos = $this->Applicationforms->Yesnos
            ->find('visibleTo', user: $currentUser)->find('list')->toArray();
        $collaborators = $this->Applicationforms->Users
            ->find('visibleTo', user: $currentUser)
            ->find('list', keyField: 'id', valueField: 'display_name')->toArray();

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
            'collaborators',
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
                $this->Flash->success(__(
                    'La demande de recrutement #{0} a été mise à jour avec succès.',
                    $applicationform->id,
                ));

                return $this->redirect(['action' => 'index']);
            }

            $this->flashValidationErrors($applicationform, 'Applicationforms');
        }

        // 4. Chargement des listes pour le rendu du formulaire
        /** @var \App\Model\Entity\User $currentUser */
        $currentUser = $this->request->getAttribute('identity')->getOriginalData();

        // Récupération des départements autorisés sous forme d'arbre
        // $departments = $this->Applicationforms->Departments
        //     ->find('treeVisibleTo', user: $currentUser)
        //     ->toArray();
        $contracttypes = $this->Applicationforms->Contracttypes->getVisibleList($currentUser);
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
            'collaborators',
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
            if ((new ApplicationformValidationWorkflow())->deleteApplicationform($applicationform)) {
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
                ->withStringBody((string)json_encode([
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

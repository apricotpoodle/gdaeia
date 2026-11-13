<?php
declare(strict_types=1);

namespace App\Controller;

use App\Model\Entity\Department;
use App\Service\DepartmentManagementService;
use Cake\Http\Response;
use DomainException;

/** Interface Web d’administration de l’arbre des départements. */
final class DepartmentsController extends AppController
{
    /** Affiche l’index arborescent. */
    public function index(): void
    {
        $table = $this->fetchTable('Departments');
        $this->Authorization->authorize($table->newEmptyEntity(), 'index');
    }

    /** Affiche et traite le formulaire d’ajout. */
    public function add(): ?Response
    {
        $table = $this->fetchTable('Departments');
        /** @var \App\Model\Entity\Department $department */
        $department = $table->newEmptyEntity();
        $this->Authorization->authorize($department, 'add');

        if ($this->request->is('post')) {
            try {
                $data = $this->authorizedRequestData('Departments');
                $data += ['base' => false];
                $saved = (new DepartmentManagementService())->save(
                    $department,
                    $data,
                );
                if ($saved->getErrors() === []) {
                    $this->Flash->success(__('Le département a été créé.'));

                    return $this->redirect(['action' => 'index']);
                }
                $this->flashValidationErrors($saved, 'Departments');
                $department = $saved;
            } catch (DomainException $exception) {
                $this->Flash->error($exception->getMessage());
            }
        }

        $this->setFormData($department, 'add');
        $this->viewBuilder()->setTemplate('form');

        return null;
    }

    /** Affiche et traite le formulaire de modification. */
    public function edit(string $id): ?Response
    {
        $table = $this->fetchTable('Departments');
        /** @var \App\Model\Entity\Department $department */
        $department = $table->find()
            ->contain(['Managers', 'CgrCodes'])
            ->where(['id' => $id, 'deleted IS' => null])
            ->firstOrFail();
        $this->Authorization->authorize($department, 'edit');

        if ($this->request->is(['post', 'put', 'patch'])) {
            try {
                $saved = (new DepartmentManagementService())->save(
                    $department,
                    $this->authorizedRequestData('Departments'),
                );
                if ($saved->getErrors() === []) {
                    $this->Flash->success(__('Le département a été mis à jour.'));

                    return $this->redirect(['action' => 'index']);
                }
                $this->flashValidationErrors($saved, 'Departments');
                $department = $saved;
            } catch (DomainException $exception) {
                $this->Flash->error($exception->getMessage());
            }
        }

        $this->setFormData($department, 'edit');
        $this->viewBuilder()->setTemplate('form');

        return null;
    }

    /** @param \App\Model\Entity\Department $department */
    private function setFormData(Department $department, string $mode): void
    {
        /** @var \App\Model\Entity\User $currentUser */
        $currentUser = $this->request->getAttribute('identity')->getOriginalData();
        $parentsQuery = $this->fetchTable('Departments')->find('visibleTo', user: $currentUser)
            ->where(['Departments.deleted IS' => null]);
        if (!$department->isNew()) {
            $parentsQuery->where([
                'Departments.id !=' => $department->get('id'),
                'OR' => [
                    'Departments.lft <' => $department->get('lft'),
                    'Departments.rght >' => $department->get('rght'),
                ],
            ]);
        }

        $this->set([
            'department' => $department,
            'mode' => $mode,
            'fieldSchema' => $this->fieldAuthorizationSchema('Departments'),
            'strategies' => $this->fetchTable('CgrStrategies')->find('list')->toArray(),
            'managers' => $this->fetchTable('Users')->find('list', keyField: 'id', valueField: 'username')
                ->where(['deleted IS' => null])
                ->toArray(),
            'cgrCodes' => $this->fetchTable('CgrCodes')->find('list', keyField: 'id', valueField: 'label')
                ->where(['active' => true])
                ->orderBy(['type' => 'ASC', 'code' => 'ASC'])
                ->toArray(),
            'parents' => $parentsQuery->orderBy(['Departments.lft' => 'ASC'])
                ->select(['id', 'name'])
                ->all()
                ->combine('id', 'name')
                ->toArray(),
        ]);
    }
}

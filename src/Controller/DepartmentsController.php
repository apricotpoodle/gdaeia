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
                (new DepartmentManagementService())->save($department, $this->request->getData());
                $this->Flash->success(__('Le département a été créé.'));

                return $this->redirect(['action' => 'index']);
            } catch (DomainException $exception) {
                $this->Flash->error($exception->getMessage());
            }
        }

        $this->setFormData($department, 'add');

        return null;
    }

    /** Affiche et traite le formulaire de modification. */
    public function edit(string $id): ?Response
    {
        $table = $this->fetchTable('Departments');
        /** @var \App\Model\Entity\Department $department */
        $department = $table->find()->where(['id' => $id, 'deleted IS' => null])->firstOrFail();
        $this->Authorization->authorize($department, 'edit');

        if ($this->request->is(['post', 'put', 'patch'])) {
            try {
                (new DepartmentManagementService())->save($department, $this->request->getData());
                $this->Flash->success(__('Le département a été mis à jour.'));

                return $this->redirect(['action' => 'index']);
            } catch (DomainException $exception) {
                $this->Flash->error($exception->getMessage());
            }
        }

        $this->setFormData($department, 'edit');

        return null;
    }

    /** @param \App\Model\Entity\Department $department */
    private function setFormData(Department $department, string $mode): void
    {
        $this->set([
            'department' => $department,
            'mode' => $mode,
            'strategies' => $this->fetchTable('CgrStrategies')->find('list')->toArray(),
            'managers' => $this->fetchTable('Users')->find('list', keyField: 'id', valueField: 'username')
                ->where(['deleted IS' => null])
                ->toArray(),
            'parents' => $this->fetchTable('Departments')->find('list', keyField: 'id', valueField: 'name')
                ->where(['deleted IS' => null])
                ->orderBy(['lft' => 'ASC'])
                ->toArray(),
        ]);
    }
}

<?php
declare(strict_types=1);

namespace App\Controller;

use Cake\Datasource\EntityInterface;
use Cake\Http\Response;
use Cake\I18n\DateTime;
use Cake\ORM\Table;
use Exception;

/** Contrôleur Web commun aux sept nomenclatures de demandes. */
abstract class ReferenceController extends AppController
{
    /** Nom ORM de la nomenclature concrète. */
    abstract protected function referenceAlias(): string;

    /** Libellé métier affiché par l’interface. */
    abstract protected function referenceLabel(): string;

    /** Affiche la grille Tabulator de la nomenclature. */
    public function index(): void
    {
        $table = $this->referenceTable();
        $this->Authorization->authorize($table->newEmptyEntity(), 'index');
        $this->viewBuilder()->setTemplatePath('References')->setTemplate('index');
        $this->set($this->referenceViewData());
    }

    /** Affiche et traite le formulaire de création. */
    public function add(): ?Response
    {
        $table = $this->referenceTable();
        $entity = $table->newEmptyEntity();
        $this->Authorization->authorize($entity, 'add');

        if ($this->request->is('post')) {
            $entity = $table->patchEntity($entity, $this->request->getData(), $this->patchOptions());
            $entity->set('base', false);
            if ($table->save($entity)) {
                $this->Flash->success(__('La référence a été créée avec succès.'));

                return $this->redirect(['action' => 'index']);
            }
            $this->flashValidationErrors($entity, $this->referenceAlias());
        }

        $this->viewBuilder()->setTemplatePath('References')->setTemplate('form');
        $this->set($this->referenceViewData() + ['reference' => $entity, 'mode' => 'add']);

        return null;
    }

    /** Affiche et traite le formulaire de modification. */
    public function edit(string $id): ?Response
    {
        $table = $this->referenceTable();
        $entity = $this->getActiveReference($id);
        $this->Authorization->authorize($entity, 'edit');

        if ($this->request->is(['post', 'put', 'patch'])) {
            $entity = $table->patchEntity($entity, $this->request->getData(), $this->patchOptions());
            if ($table->save($entity)) {
                $this->Flash->success(__('La référence a été mise à jour.'));

                return $this->redirect(['action' => 'index']);
            }
            $this->flashValidationErrors($entity, $this->referenceAlias());
        }

        $this->viewBuilder()->setTemplatePath('References')->setTemplate('form');
        $this->set($this->referenceViewData() + ['reference' => $entity, 'mode' => 'edit']);

        return null;
    }

    /** Supprime logiquement ou physiquement une ligne selon le schéma. */
    public function delete(?string $id = null): Response
    {
        $table = $this->referenceTable();
        $entity = $this->getActiveReference((string)$id);
        $this->Authorization->authorize($entity, 'delete');
        $success = false;

        try {
            if ($table->getSchema()->hasColumn('deleted')) {
                $entity->set('deleted', DateTime::now());
                $success = (bool)$table->save($entity);
            } else {
                $success = (bool)$table->delete($entity);
            }
            $message = $success
                ? __('La référence a été supprimée.')
                : __('Impossible de supprimer la référence.');
        } catch (Exception $exception) {
            $message = $exception->getMessage();
        }

        if ($this->request->is('ajax') || $this->request->accepts('application/json')) {
            return $this->response->withType('application/json')->withStatus($success ? 200 : 400)
                ->withStringBody((string)json_encode(
                    ['success' => $success, 'message' => $message],
                    JSON_UNESCAPED_UNICODE,
                ));
        }

        $success ? $this->Flash->success($message) : $this->Flash->error($message);

        return $this->redirect(['action' => 'index']) ?? $this->response;
    }

    /** @return array{referenceLabel: string, referenceAlias: string} */
    protected function referenceViewData(): array
    {
        return ['referenceLabel' => $this->referenceLabel(), 'referenceAlias' => $this->referenceAlias()];
    }

    /** @return array{accessibleFields: array{base: false, deleted: false}} */
    protected function patchOptions(): array
    {
        return ['accessibleFields' => ['base' => false, 'deleted' => false]];
    }

    /** Retourne la table de la nomenclature courante. */
    protected function referenceTable(): Table
    {
        return $this->fetchTable($this->referenceAlias());
    }

    /** Retourne une référence active dans le périmètre de l’opérateur. */
    protected function getActiveReference(string $id): EntityInterface
    {
        $identity = $this->request->getAttribute('identity');
        /** @var \App\Model\Entity\User $user */
        $user = $identity->getOriginalData();

        return $this->referenceTable()->find('visibleTo', user: $user)
            ->where([$this->referenceAlias() . '.id' => $id])
            ->firstOrFail();
    }
}

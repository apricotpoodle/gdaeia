<?php
declare(strict_types=1);

namespace App\Controller\Api;

use App\Controller\AppController;
use App\Model\Entity\User;
use App\Service\DataGrid\TabulatorAdapter;
use Cake\Datasource\EntityInterface;
use Cake\Http\Response;
use Cake\I18n\DateTime;
use Cake\ORM\Table;

/** API commune aux grilles distantes des nomenclatures. */
abstract class ReferenceController extends AppController
{
    /** Nom ORM de la nomenclature courante. */
    abstract protected function referenceAlias(): string;

    /** Configure la réponse JSON du contrôleur. */
    public function initialize(): void
    {
        parent::initialize();
        $this->viewBuilder()->setClassName('Json');
    }

    /** Retourne la page distante Tabulator. */
    public function index(): void
    {
        $this->request->allowMethod(['get']);
        $table = $this->referenceTable();
        $this->Authorization->authorize($table->newEmptyEntity(), 'index');
        $query = (new TabulatorAdapter())->adaptRequest(
            $this->request,
            $table->find('visibleTo', user: $this->operator()),
        );
        $data = $this->paginate($query, [
            'limit' => (int)($this->request->getQuery('size') ?? 20),
            'page' => (int)($this->request->getQuery('page') ?? 1),
            'sortableFields' => [],
        ]);
        $output = (new TabulatorAdapter())->adaptResponse($data, $this->createGridRightsFormatter());
        $this->set($output);
        $this->viewBuilder()->setOption('serialize', array_keys($output));
    }

    /** Retourne une ligne active. */
    public function view(string $id): void
    {
        $this->request->allowMethod(['get']);
        $entity = $this->getActiveReference($id);
        $this->Authorization->authorize($entity, 'view');
        $this->set('data', $entity);
        $this->viewBuilder()->setOption('serialize', ['data']);
    }

    /** Crée une ligne de nomenclature. */
    public function add(): Response
    {
        $this->request->allowMethod(['post']);
        $table = $this->referenceTable();
        $entity = $table->newEmptyEntity();
        $this->Authorization->authorize($entity, 'add');
        $entity = $table->patchEntity($entity, $this->request->getData(), $this->patchOptions());
        $entity->set('base', false);
        if ($table->save($entity)) {
            return $this->jsonSuccess(['id' => $entity->get('id')], __('La référence a été créée avec succès.'));
        }

        return $this->validationErrorResponse($entity, $this->referenceAlias());
    }

    /** Modifie une ligne non socle. */
    public function edit(string $id): Response
    {
        $this->request->allowMethod(['post', 'put', 'patch']);
        $table = $this->referenceTable();
        $entity = $this->getActiveReference($id);
        $this->Authorization->authorize($entity, 'edit');
        $entity = $table->patchEntity($entity, $this->request->getData(), $this->patchOptions());
        if ($table->save($entity)) {
            return $this->jsonSuccess(message: __('La référence a été mise à jour.'));
        }

        return $this->validationErrorResponse($entity, $this->referenceAlias());
    }

    /** Supprime une ligne selon la stratégie du schéma. */
    public function delete(string $id): Response
    {
        $table = $this->referenceTable();
        $entity = $this->getActiveReference($id);
        $this->Authorization->authorize($entity, 'delete');
        $success = $table->getSchema()->hasColumn('deleted')
            ? $this->softDelete($table, $entity)
            : (bool)$table->delete($entity);

        return $success
            ? $this->jsonSuccess(message: __('La référence a été supprimée.'))
            : $this->jsonError(__('Impossible de supprimer la référence.'));
    }

    /** Retourne la table de la nomenclature courante. */
    protected function referenceTable(): Table
    {
        return $this->fetchTable($this->referenceAlias());
    }

    /** Retourne une ligne active dans le périmètre de l’opérateur. */
    protected function getActiveReference(string $id): EntityInterface
    {
        return $this->referenceTable()->find('visibleTo', user: $this->operator())
            ->where([$this->referenceAlias() . '.id' => $id])
            ->firstOrFail();
    }

    /** @return array{accessibleFields: array{base: false, deleted: false}} */
    protected function patchOptions(): array
    {
        return ['accessibleFields' => ['base' => false, 'deleted' => false]];
    }

    /** Désactive logiquement une ligne possédant une colonne deleted. */
    protected function softDelete(Table $table, EntityInterface $entity): bool
    {
        $entity->set('deleted', DateTime::now());

        return (bool)$table->save($entity);
    }

    /**
     * Retourne l’opérateur authentifié.
     *
     * @return \App\Model\Entity\User Opérateur courant.
     */
    protected function operator(): User
    {
        /** @var \App\Model\Entity\User $user */
        $user = $this->request->getAttribute('identity')->getOriginalData();

        return $user;
    }

    /** @param array<string, mixed> $data */
    protected function jsonSuccess(array $data = [], ?string $message = null): Response
    {
        return $this->response->withType('application/json')->withStringBody((string)json_encode([
            'success' => true,
            'message' => $message,
        ] + $data, JSON_UNESCAPED_UNICODE));
    }

    /** Retourne une erreur JSON standardisée. */
    protected function jsonError(string $message): Response
    {
        return $this->response->withType('application/json')->withStatus(400)
            ->withStringBody((string)json_encode(
                ['success' => false, 'message' => $message, 'errors' => null],
                JSON_UNESCAPED_UNICODE,
            ));
    }
}

<?php
declare(strict_types=1);

namespace App\Controller\Api;

use App\Controller\AppController;
use App\Model\Entity\ValidationCommentTemplate;
use App\Model\Table\ValidationCommentTemplatesTable;
use App\Service\DataGrid\TabulatorAdapter;
use Cake\Datasource\EntityInterface;
use Cake\Http\Exception\BadRequestException;
use Cake\Http\Response;

/** API du paramétrage global et des commentaires prédéfinis du workflow. */
class WorkflowSettingsController extends AppController
{
    /** @return void */
    public function initialize(): void
    {
        parent::initialize();
        $this->viewBuilder()->setClassName('Json');
    }

    /** Lit ou met à jour le délai global de validation, en heures. */
    public function defaultDueHours(): ?Response
    {
        $this->request->allowMethod(['get', 'post']);
        /** @var \App\Model\Table\WorkflowSettingsTable $settings */
        $settings = $this->fetchTable('WorkflowSettings');
        $this->Authorization->authorize($settings->newEmptyEntity(), 'manage');
        /** @var \App\Model\Entity\WorkflowSetting|null $setting */
        $setting = $settings->find()->where(['name' => 'validation.default_due_hours'])->first();
        if ($this->request->is('get')) {
            $this->set('data', ['default_due_hours' => (int)($setting?->get('value') ?? 72)]);
            $this->viewBuilder()->setOption('serialize', ['data']);

            return null;
        }

        $hours = $this->positiveInteger($this->request->getData('default_due_hours'), 'default_due_hours');
        $setting ??= $settings->newEntity(['name' => 'validation.default_due_hours']);
        $setting->set('value', (string)$hours);
        if (!$settings->save($setting)) {
            return $this->jsonError(__('Impossible d’enregistrer le délai global.'));
        }

        return $this->jsonSuccess(['default_due_hours' => $hours]);
    }

    /** Retourne le catalogue de commentaires sous le contrat distant Tabulator. */
    public function commentTemplates(): void
    {
        $this->request->allowMethod(['get']);
        /** @var \App\Model\Table\ValidationCommentTemplatesTable $table */
        $table = $this->fetchTable('ValidationCommentTemplates');
        $this->Authorization->authorize($table->newEmptyEntity(), 'index');
        $adapter = new TabulatorAdapter();
        $query = $adapter->adaptRequest($this->request, $table->find());
        $data = $this->paginate($query, [
            'limit' => (int)($this->request->getQuery('size') ?? 20),
            'page' => (int)($this->request->getQuery('page') ?? 1),
            'sortableFields' => ['decision', 'label', 'content', 'position', 'active'],
        ]);
        $output = $adapter->adaptResponse($data, $this->createGridRightsFormatter());
        $this->set($output);
        $this->viewBuilder()->setOption('serialize', array_keys($output));
    }

    /** Crée un commentaire prédéfini. */
    public function createCommentTemplate(): Response
    {
        $this->request->allowMethod(['post']);
        /** @var \App\Model\Table\ValidationCommentTemplatesTable $table */
        $table = $this->fetchTable('ValidationCommentTemplates');
        $template = $table->newEmptyEntity();
        $this->Authorization->authorize($template, 'add');

        return $this->saveCommentTemplate($table->patchEntity($template, $this->request->getData(), [
            'fields' => ['decision', 'label', 'content', 'position', 'active'],
        ]), $table);
    }

    /** Modifie un commentaire prédéfini. */
    public function updateCommentTemplate(string $id): Response
    {
        $this->request->allowMethod(['post']);
        /** @var \App\Model\Table\ValidationCommentTemplatesTable $table */
        $table = $this->fetchTable('ValidationCommentTemplates');
        $template = $table->get($this->positiveInteger($id, 'id'));
        $this->Authorization->authorize($template, 'edit');

        return $this->saveCommentTemplate($table->patchEntity($template, $this->request->getData(), [
            'fields' => ['decision', 'label', 'content', 'position', 'active'],
        ]), $table);
    }

    /** Supprime un commentaire prédéfini. */
    public function deleteCommentTemplate(string $id): Response
    {
        $this->request->allowMethod(['post']);
        /** @var \App\Model\Table\ValidationCommentTemplatesTable $table */
        $table = $this->fetchTable('ValidationCommentTemplates');
        $template = $table->get($this->positiveInteger($id, 'id'));
        $this->Authorization->authorize($template, 'delete');
        if (!$table->delete($template)) {
            return $this->jsonError(__('Impossible de supprimer le commentaire prédéfini.'));
        }

        return $this->jsonSuccess(['id' => (int)$id]);
    }

    /**
     * @param \App\Model\Entity\ValidationCommentTemplate $template Commentaire à enregistrer.
     * @param \App\Model\Table\ValidationCommentTemplatesTable $table Table de persistance.
     * @return \Cake\Http\Response Réponse JSON normalisée.
     */
    private function saveCommentTemplate(
        ValidationCommentTemplate $template,
        ValidationCommentTemplatesTable $table,
    ): Response {
        if (!$table->save($template)) {
            return $this->validationError($template);
        }

        return $this->jsonSuccess(['template' => $template]);
    }

    /** @param mixed $value Valeur à contrôler. @param string $field Nom du champ. @return int Entier positif. */
    private function positiveInteger(mixed $value, string $field): int
    {
        if (filter_var($value, FILTER_VALIDATE_INT) === false || (int)$value < 1) {
            throw new BadRequestException(__('Le champ « {0} » doit être un entier positif.', $field));
        }

        return (int)$value;
    }

    /** @param array<string, mixed> $data Données de succès. @return \Cake\Http\Response Réponse JSON. */
    private function jsonSuccess(array $data): Response
    {
        return $this->response->withType('application/json')
            ->withStringBody((string)json_encode(['success' => true] + $data));
    }

    /** @param array<string, mixed> $errors Erreurs détaillées. @param int $status Code HTTP. @return \Cake\Http\Response Réponse JSON. */
    private function jsonError(string $message, array $errors = [], int $status = 400): Response
    {
        return $this->response->withType('application/json')->withStatus($status)
            ->withStringBody((string)json_encode([
                'success' => false,
                'message' => $message,
                'errors' => $errors,
            ], JSON_UNESCAPED_UNICODE));
    }

    /** Retourne les erreurs ORM conformément au contrat des erreurs de validation API. */
    private function validationError(EntityInterface $entity): Response
    {
        return $this->jsonError(__('Le formulaire contient des données invalides.'), $entity->getErrors(), 422);
    }
}

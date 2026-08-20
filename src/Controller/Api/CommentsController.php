<?php
declare(strict_types=1);

namespace App\Controller\Api;

use App\Controller\AppController;
use Cake\Http\Response;
use App\Model\Table\CommentsTable;

/**
 * Class CommentsController (API)
 *
 * Expose le CRUD des commentaires polymorphiques pour le front-end.
 */
class CommentsController extends AppController
{
    public function initialize(): void
    {
        parent::initialize();
        $this->viewBuilder()->setClassName('Json');
    }

/**
     * Endpoint : GET /api/comments.json?model=Applicationforms&foreign_key=12
     * Récupère le fil de discussion sécurisé et arborescent.
     */
    public function index(): void
    {
        $this->request->allowMethod(['get']);
        $this->Authorization->skipAuthorization();

        $model = $this->request->getQuery('model');
        $foreignKey = $this->request->getQuery('foreign_key');

        /** @var \App\Model\Entity\User $currentUser */
        $currentUser = $this->request->getAttribute('identity')->getOriginalData();
        
        /** @var CommentsTable $commentsTable */
        $commentsTable = $this->fetchTable('Comments');

        // 🚀 CHAÎNAGE DES FINDERS : visibleTo() + threaded()
        $query = $commentsTable
            ->find('visibleTo', user: $currentUser)
            ->find('threaded')
            ->contain(['Users'])
            ->orderBy(['Comments.created' => 'ASC']);

        if ($model && $foreignKey) {
            $query->where([
                'Comments.model' => $model,
                'Comments.foreign_key' => (int)$foreignKey,
            ]);
        } else {
            $query->where(['1 = 0']); // Failsafe si paramètres manquants
        }

        $comments = $query->all();

        $this->set(compact('comments'));
        $this->viewBuilder()->setOption('serialize', ['comments']);
    }

    /**
     * Endpoint : POST /api/comments/add.json
     */
    public function add(): ?Response
    {
        $this->request->allowMethod(['post']);
        $this->Authorization->skipAuthorization();

        $commentsTable = $this->fetchTable('Comments');
        $comment = $commentsTable->newEmptyEntity();

        $data = $this->request->getData();
        
        /** @var \App\Model\Entity\User $user */
        $user = $this->request->getAttribute('identity')->getOriginalData();
        $data['user_id'] = $user->id;

        $comment = $commentsTable->patchEntity($comment, $data);

        if ($commentsTable->save($comment)) {
            $comment = $commentsTable->get($comment->id, contain: ['Users']);

            return $this->response->withType('application/json')
                ->withStringBody(json_encode([
                    'success' => true,
                    'comment' => $comment,
                ]));
        }

        return $this->handleValidationError($comment);
    }

    /**
     * Endpoint : PUT/PATCH /api/comments/edit/{id}.json
     */
    public function edit(string $id): ?Response
    {
        $this->request->allowMethod(['post', 'put', 'patch']);
        $commentsTable = $this->fetchTable('Comments');
        
        $comment = $commentsTable->get($id);
        
        /** @var \App\Model\Entity\User $currentUser */
        $currentUser = $this->request->getAttribute('identity')->getOriginalData();

        // Seul l'auteur ou un Super Admin peut éditer son commentaire
        if (!$currentUser->issuperuser && $comment->user_id !== $currentUser->id) {
            return $this->response->withType('application/json')
                ->withStatus(403)
                ->withStringBody(json_encode([
                    'success' => false,
                    'message' => __('Vous n\'êtes pas autorisé à modifier ce commentaire.'),
                ]));
        }

        $comment = $commentsTable->patchEntity($comment, $this->request->getData(), [
            'accessibleFields' => ['content' => true, 'type' => true],
        ]);

        if ($commentsTable->save($comment)) {
            $comment = $commentsTable->get($comment->id, contain: ['Users']);

            return $this->response->withType('application/json')
                ->withStringBody(json_encode([
                    'success' => true,
                    'comment' => $comment,
                ]));
        }

        return $this->handleValidationError($comment);
    }

    /**
     * Endpoint : DELETE /api/comments/delete/{id}.json
     */
    public function delete(string $id): ?Response
    {
        $this->request->allowMethod(['post', 'delete']);
        $commentsTable = $this->fetchTable('Comments');

        $comment = $commentsTable->get($id);

        /** @var \App\Model\Entity\User $currentUser */
        $currentUser = $this->request->getAttribute('identity')->getOriginalData();

        if (!$currentUser->issuperuser && $comment->user_id !== $currentUser->id) {
            return $this->response->withType('application/json')
                ->withStatus(403)
                ->withStringBody(json_encode([
                    'success' => false,
                    'message' => __('Vous n\'êtes pas autorisé à supprimer ce commentaire.'),
                ]));
        }

        if ($commentsTable->delete($comment)) {
            return $this->response->withType('application/json')
                ->withStringBody(json_encode(['success' => true]));
        }

        return $this->response->withType('application/json')
            ->withStatus(400)
            ->withStringBody(json_encode([
                'success' => false,
                'message' => __('Impossible de supprimer ce commentaire.'),
            ]));
    }

    /**
     * Gestion centralisée des erreurs de validation
     */
    private function handleValidationError(\Cake\Datasource\EntityInterface $entity): Response
    {
        $errors = $entity->getErrors();
        $message = __("Données invalides.");

        if (!empty($errors)) {
            $firstError = current(reset($errors));
            $message = (string)$firstError;
        }

        return $this->response->withType('application/json')
            ->withStatus(400)
            ->withStringBody(json_encode(['success' => false, 'message' => $message]));
    }
}
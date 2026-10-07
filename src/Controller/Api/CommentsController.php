<?php
declare(strict_types=1);

namespace App\Controller\Api;

use App\Controller\AppController;
use App\Model\Entity\Comment;
use App\Model\Entity\User;
use Cake\Http\Response;

/**
 * Class CommentsController (API)
 *
 * Expose le CRUD des commentaires polymorphiques pour le front-end.
 *
 * @property \App\Model\Table\CommentsTable $Comments
 */
class CommentsController extends AppController
{
    /** @inheritDoc */
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

        /** @var \App\Model\Table\CommentsTable $commentsTable */
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
        $data[Comment::FIELD_USER_ID] = $user->get(User::FIELD_ID);

        $comment = $commentsTable->patchEntity($comment, $data);
        /** @var \App\Model\Entity\Comment $comment */

        if ($commentsTable->save($comment)) {
            $comment = $commentsTable->get($comment->get(Comment::FIELD_ID), contain: ['Users']);

            return $this->response->withType('application/json')
                ->withStringBody((string)json_encode([
                    'success' => true,
                    'comment' => $comment,
                ]));
        }

        return $this->validationErrorResponse($comment, 'Comments');
    }

    /**
     * Endpoint : PUT/PATCH /api/comments/edit/{id}.json
     */
    public function edit(string $id): ?Response
    {
        $this->request->allowMethod(['post', 'put', 'patch']);
        // Le contrôle d'auteur ou de super-administrateur est effectué ci-dessous.
        $this->Authorization->skipAuthorization();
        $commentsTable = $this->fetchTable('Comments');

        $comment = $commentsTable->get($id);
        /** @var \App\Model\Entity\Comment $comment */

        /** @var \App\Model\Entity\User $currentUser */
        $currentUser = $this->request->getAttribute('identity')->getOriginalData();

        // Seul l'auteur ou un Super Admin peut éditer son commentaire
        if (
            !$currentUser->get(User::FIELD_ISSUPERUSER)
            && $comment->get(Comment::FIELD_USER_ID) !== $currentUser->get(User::FIELD_ID)
        ) {
            return $this->response->withType('application/json')
                ->withStatus(403)
                ->withStringBody((string)json_encode([
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
                ->withStringBody((string)json_encode([
                    'success' => true,
                    'comment' => $comment,
                ]));
        }

        return $this->validationErrorResponse($comment, 'Comments');
    }

    /**
     * Endpoint : DELETE /api/comments/delete/{id}.json
     */
    public function delete(string $id): ?Response
    {
        $this->request->allowMethod(['post', 'delete']);
        $this->Authorization->skipAuthorization();
        $commentsTable = $this->fetchTable('Comments');

        $comment = $commentsTable->get($id);
        /** @var \App\Model\Entity\Comment $comment */

        /** @var \App\Model\Entity\User $currentUser */
        $currentUser = $this->request->getAttribute('identity')->getOriginalData();

        if (!$currentUser->issuperuser && $comment->user_id !== $currentUser->id) {
            return $this->response->withType('application/json')
                ->withStatus(403)
                ->withStringBody((string)json_encode([
                    'success' => false,
                    'message' => __('Vous n\'êtes pas autorisé à supprimer ce commentaire.'),
                ]));
        }

        if ($commentsTable->delete($comment)) {
            return $this->response->withType('application/json')
                ->withStringBody((string)json_encode(['success' => true]));
        }

        return $this->response->withType('application/json')
            ->withStatus(400)
            ->withStringBody((string)json_encode([
                'success' => false,
                'message' => __('Impossible de supprimer ce commentaire.'),
            ]));
    }
}

<?php
declare(strict_types=1);

namespace App\Controller\Api;

use App\Controller\AppController;
use App\Model\Entity\RolePermission;
use App\Service\Security\PermissionResolver;
use Cake\Http\Response;

/** API d’audit et d’attribution du catalogue des permissions. */
final class PermissionsController extends AppController
{
    /** Initialise le rendu JSON. */
    public function initialize(): void
    {
        parent::initialize();
        $this->viewBuilder()->setClassName('Json');
    }

    /** Retourne le catalogue des permissions et ses périmètres. */
    public function index(): void
    {
        $this->request->allowMethod(['get']);
        $permission = $this->fetchTable('Permissions')->newEmptyEntity();
        $this->Authorization->authorize($permission, 'index');
        $data = $this->fetchTable('Permissions')->find()->contain(['PermissionScopes'])->all()->toList();
        $this->set(['success' => true, 'data' => $data]);
        $this->viewBuilder()->setOption('serialize', ['success', 'data']);
    }

    /** Attribue un périmètre admissible à un rôle. */
    public function assign(): Response
    {
        $this->request->allowMethod(['post']);
        $permission = $this->fetchTable('Permissions')->newEmptyEntity();
        $this->Authorization->authorize($permission, 'manage');
        $data = $this->request->getData();
        $roleId = (int)($data['role_id'] ?? 0);
        $permissionId = (int)($data['permission_id'] ?? 0);
        $scopeType = (string)($data['scope_type'] ?? '');
        $scopes = $this->fetchTable('PermissionScopes')->find()->where([
            'permission_id' => $permissionId,
            'scope_type' => $scopeType,
        ]);
        if (
            $roleId < 1
            || $permissionId < 1
            || !in_array($scopeType, PermissionResolver::SCOPE_TYPES, true)
        ) {
            return $this->jsonError(__('La permission, le rôle ou le périmètre est invalide.'));
        }
        if ($scopes->count() === 0) {
            return $this->jsonError(__('Ce périmètre n’est pas admissible pour cette permission.'));
        }
        $table = $this->fetchTable('RolePermissions');
        $existing = $table->find()->where([
            'role_id' => $roleId,
            'permission_id' => $permissionId,
            'scope_type' => $scopeType,
        ])->first();
        if ($existing instanceof RolePermission) {
            return $this->jsonSuccess(message: __('La permission a été attribuée.'));
        }
        $saved = $table->save($table->newEntity([
            'role_id' => $roleId,
            'permission_id' => $permissionId,
            'scope_type' => $scopeType,
        ]));
        if ($saved) {
            return $this->jsonSuccess(message: __('La permission a été attribuée.'));
        }

        return $this->jsonError(__('L’attribution de la permission a échoué.'));
    }

    /** Retire une attribution de permission. */
    public function revoke(string $id): Response
    {
        $this->request->allowMethod(['post', 'delete']);
        $permission = $this->fetchTable('Permissions')->newEmptyEntity();
        $this->Authorization->authorize($permission, 'manage');
        $table = $this->fetchTable('RolePermissions');
        $assignment = $table->get($id);
        if ($table->delete($assignment)) {
            return $this->jsonSuccess(message: __('La permission a été retirée.'));
        }

        return $this->jsonError(__('Le retrait de la permission a échoué.'));
    }

    /** Construit une réponse JSON positive. */
    private function jsonSuccess(?string $message = null): Response
    {
        return $this->response->withType('application/json')->withStringBody((string)json_encode([
            'success' => true,
            'message' => $message,
        ]));
    }

    /** Construit une réponse JSON d’erreur de validation. */
    private function jsonError(string $message): Response
    {
        return $this->response->withType('application/json')->withStatus(422)->withStringBody((string)json_encode([
            'success' => false,
            'message' => $message,
            'errors' => [],
        ]));
    }
}

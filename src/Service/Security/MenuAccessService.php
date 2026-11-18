<?php
declare(strict_types=1);

namespace App\Service\Security;

use App\Model\Entity\User;
use Cake\ORM\TableRegistry;

/** Filtre la navigation pendant la coexistence de role_menus et des permissions. */
final class MenuAccessService
{
    /** Construit le service avec le résolveur commun. */
    public function __construct(private readonly PermissionResolver $permissions = new PermissionResolver())
    {
    }

    /**
     * Conserve l’ordre et les nœuds produits par le finder threaded historique.
     * department_id de role_menus reste volontairement ignoré comme auparavant.
     *
     * @param iterable<\App\Model\Entity\Menu> $menus Arbre historiquement éligible.
     * @return list<\App\Model\Entity\Menu>
     */
    public function filter(iterable $menus, ?User $user): array
    {
        if ($user === null || (!$user->isSuperUser() && $user->get('role_id') === null)) {
            return [];
        }

        $allowedIds = [];
        $codes = [];
        if (!$user->isSuperUser()) {
            $tables = TableRegistry::getTableLocator();
            $allowedIds = $tables->get('RoleMenus')->find()
                ->select(['menu_id'])->where(['role_id' => $user->get('role_id')])
                ->all()->extract('menu_id')->toList();
            foreach ($tables->get('Permissions')->find()->select(['id', 'code'])->all() as $permission) {
                $codes[(int)$permission->get('id')] = (string)$permission->get('code');
            }
        }

        return $this->filterTree($menus, $user, array_map('intval', $allowedIds), $codes);
    }

    /**
     * @param iterable<\App\Model\Entity\Menu> $menus Arbre à filtrer.
     * @param \App\Model\Entity\User $user Identité effective.
     * @param list<int> $allowedIds Associations historiques du rôle.
     * @param array<int, string> $codes Catalogue des permissions.
     * @return list<\App\Model\Entity\Menu>
     */
    private function filterTree(iterable $menus, User $user, array $allowedIds, array $codes): array
    {
        $visible = [];
        foreach ($menus as $menu) {
            if (!$user->isSuperUser() && !in_array((int)$menu->get('id'), $allowedIds, true)) {
                continue;
            }
            $children = $this->filterTree($menu->get('children') ?? [], $user, $allowedIds, $codes);
            $url = trim((string)$menu->get('url'));
            if (($url === '' || $url === '#') && $children === []) {
                continue;
            }
            if (!$user->isSuperUser() && $menu->get('required_permission_id') !== null) {
                $code = $codes[(int)$menu->get('required_permission_id')] ?? null;
                if ($code === null || !$this->allowsDestination($user, $code)) {
                    continue;
                }
            }
            $menu->set('children', $children);
            $visible[] = $menu;
        }

        return $visible;
    }

    /** Applique le périmètre d’entrée correspondant à la permission du menu. */
    private function allowsDestination(User $user, string $code): bool
    {
        return match ($code) {
            'applicationforms.create' => $this->permissions->allows($user, $code, PermissionResolver::SCOPE_SELF),
            'menus.manage', 'applicationforms.vote' =>
                $this->permissions->allows($user, $code, PermissionResolver::SCOPE_UNSCOPED),
            'departments.read', 'departments.manage', 'validationsequences.read' =>
                $this->permissions->allowsDepartment($user, $code),
            'applicationforms.read', 'applicationforms.update', 'applicationforms.delete',
            'users.read', 'users.update' => $this->permissions->allowsEntry($user, $code),
            default => $this->permissions->allows($user, $code),
        };
    }
}

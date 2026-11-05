<?php
declare(strict_types=1);

use App\Model\Entity\Permission;

/** @var list<\App\Model\Entity\Permission> $permissions */
$this->assign('title', __('Catalogue des permissions'));
?>
<div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="h3 mb-0"><?= __('Catalogue des permissions') ?></h1>
</div>
<table class="table table-striped" data-testid="permissions-catalog">
    <thead><tr><th><?= __('Code') ?></th><th><?= __('Libellé') ?></th><th><?= __('Périmètres admissibles') ?></th></tr></thead>
    <tbody>
    <?php foreach ($permissions as $permission): ?>
        <tr>
            <td><?= h($permission->get(Permission::FIELD_CODE)) ?></td>
            <td><?= h($permission->get(Permission::FIELD_LABEL)) ?></td>
            <td><?= h(implode(', ', array_map(static fn($scope): string => (string)$scope->get('scope_type'), $permission->get('permission_scopes') ?? []))) ?></td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>

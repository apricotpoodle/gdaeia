<?php
/**
 * @file templates/element/Users/department_select.php
 * @description Élément agnostique de sélection d'arborescence pour les départements utilisateurs.
 *
 * @var \App\View\AppView $this
 * @var bool $isReadOnly
 * @var array $departmentsTree
 * @var array<int> $selectedDepartmentIds
 */

$isReadOnly = $isReadOnly ?? false;
$selectedDepartmentIds = $selectedDepartmentIds ?? [];
$departmentsTree = $departmentsTree ?? [];
?>

<!-- Conteneur Agnostique TreeselectJS -->
<div id="user-departments-tree"
     class="treeselect-target"
     data-field-name="user_departments"
     data-foreign-key="department_id"
     data-hidden-container="user-departments-hidden-inputs"
     data-data-script="user-departments-data"
     data-api-url="/api/users/get-form-schema.json"
     data-placeholder="<?= __('Sélectionner les départements...') ?>"
     data-readonly="<?= $isReadOnly ? 'true' : 'false' ?>">
</div>

<!-- Injection des données initiales sous forme de JSON local -->
<script id="user-departments-data" type="application/json">
    <?= json_encode([
        'options' => $departmentsTree,
        'value' => $selectedDepartmentIds
    ], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>
</script>

<!-- Conteneur dynamique d'inputs cachés alimenté par TreeSelectAdapter.js -->
<div id="user-departments-hidden-inputs">
    <?php if (!empty($selectedDepartmentIds)): ?>
        <?php foreach ($selectedDepartmentIds as $index => $deptId): ?>
            <input type="hidden" name="user_departments[<?= $index ?>][department_id]" value="<?= h($deptId) ?>">
        <?php endforeach; ?>
    <?php else: ?>
        <input type="hidden" name="user_departments" value="">
    <?php endif; ?>
</div>

<small class="form-text text-muted">
    <?= __('Sélectionnez les départements ou sous-arborescences que cet utilisateur a le droit de visualiser ou d\'administrer.') ?>
</small>

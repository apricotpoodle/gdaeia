<?php
/**
 * @file templates/element/Users/department_select.php
 * @description Élément agnostique de sélection d'arborescence pour les départements utilisateurs.
 *
 * @var \App\View\AppView $this
 * @var bool $isReadOnly
 * @var array $departmentsTree
 * @var array<int> $selectedDepartmentIds
 * @var string $fieldName
 * @var string $foreignKey
 * @var string $hiddenContainerId
 * @var string|null $dataScriptId
 * @var string $apiUrl
 * @var string $placeholder
 * @var bool $alwaysOpen
 * @var bool $staticList
 * @var bool $expandSelected
 */

$isReadOnly = $isReadOnly ?? false;
$selectedDepartmentIds = $selectedDepartmentIds ?? [];
$departmentsTree = $departmentsTree ?? [];
$fieldName = $fieldName ?? 'user_departments';
$foreignKey = $foreignKey ?? 'department_id';
$hiddenContainerId = $hiddenContainerId ?? 'user-departments-hidden-inputs';
$dataScriptId = $dataScriptId ?? 'user-departments-data';
$apiUrl = $apiUrl ?? '/api/users/get-form-schema.json';
$placeholder = $placeholder ?? __('Sélectionner les départements...');
$alwaysOpen = $alwaysOpen ?? false;
$staticList = $staticList ?? false;
$expandSelected = $expandSelected ?? false;
?>

<!-- Conteneur Agnostique TreeselectJS -->
<div id="user-departments-tree"
     class="treeselect-target"
     data-field-name="<?= h($fieldName) ?>"
     data-foreign-key="<?= h($foreignKey) ?>"
     data-hidden-container="<?= h($hiddenContainerId) ?>"
     <?= $dataScriptId !== null ? 'data-data-script="' . h($dataScriptId) . '"' : '' ?>
     data-api-url="<?= h($apiUrl) ?>"
     data-placeholder="<?= h($placeholder) ?>"
     data-readonly="<?= $isReadOnly ? 'true' : 'false' ?>"
     data-always-open="<?= $alwaysOpen ? 'true' : 'false' ?>"
     data-static-list="<?= $staticList ? 'true' : 'false' ?>"
     data-expand-selected="<?= $expandSelected ? 'true' : 'false' ?>">
</div>

<!-- Injection des données initiales sous forme de JSON local -->
<?php if ($dataScriptId !== null): ?>
    <script id="<?= h($dataScriptId) ?>" type="application/json">
        <?= json_encode([
            'options' => $departmentsTree,
            'value' => $selectedDepartmentIds
        ], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>
    </script>
<?php endif; ?>

<!-- Conteneur dynamique d'inputs cachés alimenté par TreeSelectAdapter.js -->
<div id="<?= h($hiddenContainerId) ?>">
    <?php if (!empty($selectedDepartmentIds)): ?>
        <?php foreach ($selectedDepartmentIds as $index => $deptId): ?>
            <input type="hidden" name="<?= h($fieldName) ?>[<?= $index ?>][<?= h($foreignKey) ?>]" value="<?= h($deptId) ?>">
        <?php endforeach; ?>
    <?php else: ?>
        <input type="hidden" name="<?= h($fieldName) ?>" value="">
    <?php endif; ?>
</div>

<small class="form-text text-muted">
    <?= $isReadOnly
        ? __('Consultez, dépliez ou recherchez les départements du périmètre de cet utilisateur.')
        : __('Sélectionnez les départements ou sous-arborescences que cet utilisateur a le droit de visualiser ou d\'administrer.') ?>
</small>

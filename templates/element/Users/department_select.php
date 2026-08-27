<?php
declare(strict_types=1);

/**
 * @file templates/element/Users/department_select.php
 * @var \App\View\AppView $this
 * @var array<string, string> $fieldSchema
 * @var array $departmentsTree
 * @var array<int> $selectedDepartmentIds
 */

$accessLevel = $fieldSchema['user_departments'] ?? 'EDIT';

if ($accessLevel === 'HIDE') {
    return;
}

$isReadOnly = ($accessLevel === 'READ');
?>

<div class="mb-3 user-departments-container" id="user-departments-wrapper">
    <label class="form-label font-weight-bold" for="user-departments-tree">
        <i class="fa-solid fa-sitemap me-1"></i> <?= __('Départements & Arborescences Autorisés') ?>
    </label>

    <div id="user-departments-tree"
         class="treeselect-target"
         data-readonly="<?= $isReadOnly ? 'true' : 'false' ?>">
    </div>

    <!-- Inputs cachés formattés pour patchEntity avec HasMany (user_departments.INDEX.department_id) -->
    <div id="user-departments-hidden-inputs">
        <?php if (!empty($selectedDepartmentIds)): ?>
            <?php foreach ($selectedDepartmentIds as $index => $deptId): ?>
                <input type="hidden" name="user_departments[<?= $index ?>][department_id]" value="<?= h($deptId) ?>">
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <small class="form-text text-muted">
        <?= __('Sélectionnez les départements ou sous-arborescences que cet utilisateur a le droit de visualiser ou d\'administrer.') ?>
    </small>
</div>

<script id="user-departments-data" type="application/json">
<?= json_encode([
    'options' => $departmentsTree ?? [],
    'value' => $selectedDepartmentIds ?? []
], JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT) ?>
</script>

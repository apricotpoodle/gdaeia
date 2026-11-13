<?php
declare(strict_types=1);

/** @var \App\View\AppView $this */
/** @var \App\Model\Entity\Department $department */
/** @var \Authorization\IdentityInterface|null $identity */
/** @var array<string, string> $fieldSchema */
/** @var array<int, string> $parents */
/** @var array<int, string> $strategies */

use App\View\Action\DepartmentsActions;

$isEdit = $mode === 'edit';
$isBaseRecord = (bool)$department->get('base') || (bool)$department->getOriginal('base');
$currentUser = $identity?->getOriginalData();
$isSuperUser = $currentUser instanceof \App\Model\Entity\User
    && (bool)$currentUser->get(\App\Model\Entity\User::FIELD_ISSUPERUSER);
$canEditBase = $isSuperUser || (($fieldSchema['base'] ?? 'NONE') === 'EDIT');
$managerNames = array_values(array_filter(array_map(
    static function ($manager): string {
        $displayName = (string)$manager->get('display_name');
        if ($displayName !== '') {
            return $displayName;
        }

        return trim((string)$manager->get('firstname') . ' ' . (string)$manager->get('lastname'));
    },
    $department->get('managers') ?: [],
)));
$cgrCodes = array_values(array_filter(array_map(
    static fn($code): string => (string)$code->get('code'),
    $department->get('cgr_codes') ?: [],
)));
$parentValue = $department->get('parent_id');

$this->assign('title', $isEdit ? __('Modifier un département') : __('Nouveau département'));
$this->Html->css('/assets/treeselectjs/treeselectjs.css', ['block' => true]);
$this->Html->script('/assets/treeselectjs/treeselectjs.umd.js', ['block' => 'scriptBottom']);
$this->Html->script('views/Departments/form.js', ['type' => 'module', 'block' => 'scriptBottom']);
?>
<div class="content">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h3 class="mb-0"><?= h($isEdit ? __('Modifier le département') : __('Nouveau département')) ?></h3>
        <?= $this->Action->render(DepartmentsActions::index()) ?>
    </div>

    <?= $this->Form->create($department, ['id' => 'department-form']) ?>
    <?php if ($isEdit): ?>
        <ul class="nav nav-tabs mb-3" id="department-tabs" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active" id="department-tab" data-bs-toggle="tab" data-bs-target="#department-panel" type="button" role="tab" aria-controls="department-panel" aria-selected="true">
                    <?= __('Département') ?>
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="cgrs-tab" data-bs-toggle="tab" data-bs-target="#cgrs-panel" type="button" role="tab" aria-controls="cgrs-panel" aria-selected="false">
                    <?= __('Cgrs') ?>
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="managers-tab" data-bs-toggle="tab" data-bs-target="#managers-panel" type="button" role="tab" aria-controls="managers-panel" aria-selected="false">
                    <?= __('Responsables') ?>
                </button>
            </li>
        </ul>
    <?php endif; ?>

    <div class="tab-content" id="department-tab-content">
        <div class="tab-pane fade show active" id="department-panel" role="tabpanel" aria-labelledby="department-tab">
            <div class="card mb-4">
                <div class="card-body row g-3">
                    <div class="col-md-4">
                        <?= $this->Form->control('code', ['label' => __('Code'), 'required' => true, 'readonly' => $isBaseRecord]) ?>
                    </div>
                    <div class="col-md-4">
                        <?= $this->Form->control('name', ['label' => __('Nom'), 'required' => true, 'readonly' => $isBaseRecord]) ?>
                    </div>
                    <div class="col-md-4">
                        <?= $this->Form->control('sort', ['label' => __('Tri'), 'readonly' => $isBaseRecord]) ?>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label" for="department-parent-tree"><?= __('Département parent') ?></label>
                        <div id="department-parent-tree" class="treeselect-target"></div>
                        <?= $this->Form->hidden('parent_id', ['id' => 'department-parent-id', 'value' => $parentValue]) ?>
                        <script id="department-parent-options" type="application/json">
                            <?= json_encode(
                                [
                                    'options' => array_map(
                                        static fn(int|string $id, string $name): array => ['value' => (int)$id, 'name' => $name],
                                        array_keys($parents),
                                        array_values($parents),
                                    ),
                                    'value' => $parentValue === null ? [] : [(int)$parentValue],
                                    'readOnly' => $isBaseRecord,
                                ],
                                JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP,
                            ) ?>
                        </script>
                    </div>
                    <div class="col-md-6">
                        <?= $this->Form->control('cgr_strategy_id', [
                            'label' => __('Stratégie CGR'),
                            'empty' => __('Aucune'),
                            'options' => $strategies,
                            'disabled' => $isBaseRecord,
                        ]) ?>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label"><?= __('Responsables') ?></label>
                        <div id="department-managers-summary" class="form-control bg-light" aria-readonly="true">
                            <?= h($managerNames === [] ? __('Aucun responsable') : implode(', ', $managerNames)) ?>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label"><?= __('CGR associés') ?></label>
                        <div id="department-cgrs-summary" class="form-control bg-light" aria-readonly="true">
                            <?= h($cgrCodes === [] ? __('Aucun CGR') : implode(', ', $cgrCodes)) ?>
                        </div>
                    </div>
                    <?= $this->Form->hidden('department_type_id', ['value' => $department->get('department_type_id') ?: 1]) ?>
                    <?php if ($canEditBase): ?>
                        <div class="col-md-4">
                            <?= $this->Form->control('base', ['label' => __('Département socle'), 'type' => 'checkbox']) ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
            <div class="d-flex gap-2 mb-4">
                <?= $this->Form->button(__('Enregistrer'), ['class' => 'btn btn-primary']) ?>
                <?= $this->Action->render(DepartmentsActions::index()) ?>
            </div>
        </div>

        <?php if ($isEdit): ?>
            <div class="tab-pane fade" id="cgrs-panel" role="tabpanel" aria-labelledby="cgrs-tab">
                <div class="row g-4">
                    <div class="col-lg-6">
                        <h4 class="h6"><?= __('CGR disponibles') ?></h4>
                        <?= $this->Tabulator->renderGrid('#department-cgrs-available-table', 'CgrCodes') ?>
                    </div>
                    <div class="col-lg-6">
                        <h4 class="h6"><?= __('CGR associés au département') ?></h4>
                        <?= $this->Tabulator->renderGrid('#department-cgrs-associated-table', 'CgrCodes') ?>
                    </div>
                </div>
            </div>
            <div class="tab-pane fade" id="managers-panel" role="tabpanel" aria-labelledby="managers-tab">
                <div class="row g-4">
                    <div class="col-lg-6">
                        <h4 class="h6"><?= __('Responsables disponibles') ?></h4>
                        <?= $this->Tabulator->renderGrid('#department-managers-available-table', 'Users') ?>
                    </div>
                    <div class="col-lg-6">
                        <h4 class="h6"><?= __('Responsables associés au département') ?></h4>
                        <?= $this->Tabulator->renderGrid('#department-managers-associated-table', 'Users') ?>
                    </div>
                </div>
            </div>
            <input type="hidden" id="department-id" value="<?= (int)$department->get('id') ?>">
        <?php endif; ?>
    </div>
    <?= $this->Form->end() ?>
</div>

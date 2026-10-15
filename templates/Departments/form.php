<?php
declare(strict_types=1);

/** @var \App\View\AppView $this */
/** @var \App\Model\Entity\Department $department */
use App\View\Action\DepartmentsActions;

$isEdit = $mode === 'edit';
$this->assign('title', $isEdit ? __('Modifier un département') : __('Nouveau département'));
$this->Html->script('views/Departments/form.js', ['type' => 'module', 'block' => 'scriptBottom']);
?>
<div class="content">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h3 class="mb-0"><?= h($isEdit ? __('Modifier le département') : __('Nouveau département')) ?></h3>
        <?= $this->Action->render(DepartmentsActions::index()) ?>
    </div>

    <?= $this->Form->create($department) ?>
    <div class="card mb-4">
        <div class="card-body row g-3">
            <div class="col-md-4">
                <?= $this->Form->control('code', ['label' => __('Code'), 'required' => true]) ?>
            </div>
            <div class="col-md-4">
                <?= $this->Form->control('name', ['label' => __('Nom'), 'required' => true]) ?>
            </div>
            <div class="col-md-4">
                <?= $this->Form->control('sort', ['label' => __('Clé de tri')]) ?>
            </div>
            <div class="col-md-4">
                <?= $this->Form->control('parent_id', ['label' => __('Département parent'), 'empty' => __('Racine'), 'options' => $parents]) ?>
            </div>
            <div class="col-md-4">
                <?= $this->Form->control('department_type_id', ['label' => __('Type de département'), 'type' => 'number', 'min' => 1, 'required' => true]) ?>
            </div>
            <div class="col-md-4">
                <?= $this->Form->control('current_manager_id', ['label' => __('Responsable'), 'empty' => __('Aucun'), 'options' => $managers]) ?>
            </div>
            <div class="col-md-6">
                <?= $this->Form->control('cgr_strategy_id', ['label' => __('Stratégie CGR'), 'empty' => __('Aucune'), 'options' => $strategies]) ?>
            </div>
            <div class="col-md-6">
                <?= $this->Form->control('cgr_code_id', ['label' => __('CGR par défaut'), 'type' => 'number', 'min' => 1, 'empty' => __('Aucun')]) ?>
            </div>
        </div>
    </div>
    <div class="d-flex gap-2 mb-4">
        <?= $this->Form->button(__('Enregistrer'), ['class' => 'btn btn-primary']) ?>
        <?= $this->Action->render(DepartmentsActions::index()) ?>
    </div>
    <?= $this->Form->end() ?>

    <?php if ($isEdit): ?>
        <section class="card">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h4 class="h6 mb-0"><?= __('Codes CGR associés') ?></h4>
                    <button id="add-cgr-code" class="btn btn-sm btn-primary" type="button"><?= __('Ajouter un code') ?></button>
                </div>
                <?= $this->Tabulator->renderGrid('department-cgr-codes-table', 'CgrCodes') ?>
                <input type="hidden" id="department-id" value="<?= (int)$department->get('id') ?>">
                <form id="cgr-code-form" class="row g-2 mt-3 d-none">
                    <input type="hidden" name="id">
                    <div class="col-md-2"><input class="form-control" name="type" maxlength="32" required placeholder="<?= __('Type') ?>" aria-label="<?= __('Type') ?>"></div>
                    <div class="col-md-3"><input class="form-control" name="code" maxlength="16" required placeholder="<?= __('Code') ?>" aria-label="<?= __('Code') ?>"></div>
                    <div class="col-md-5"><input class="form-control" name="label" maxlength="255" required placeholder="<?= __('Libellé') ?>" aria-label="<?= __('Libellé') ?>"></div>
                    <div class="col-md-1 form-check d-flex align-items-center"><input class="form-check-input" name="active" type="checkbox" checked><label class="form-check-label ms-1"><?= __('Actif') ?></label></div>
                    <div class="col-md-1 d-flex gap-1"><button class="btn btn-primary" type="submit" aria-label="<?= __('Enregistrer') ?>">✓</button><button id="cancel-cgr-code" class="btn btn-link" type="button" aria-label="<?= __('Annuler') ?>">×</button></div>
                </form>
            </div>
        </section>
    <?php endif; ?>
</div>

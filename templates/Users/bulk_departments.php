<?php
declare(strict_types=1);

/**
 * @var \App\View\AppView $this
 * @var bool $canEditDepartments
 */

$this->assign('title', __('Associer des départements à plusieurs utilisateurs'));
$this->Html->css('vendor/treeselect/treeselectjs.css', ['block' => true]);
$this->Html->script('views/Users/bulk-departments.js', ['type' => 'module', 'block' => 'scriptBottom']);
?>

<div class="row justify-content-center bulk-departments-screen">
    <div class="col-xl-10 h-100">
        <div class="card shadow-sm h-100">
            <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                <h1 class="h4 mb-0"><i class="fa-solid fa-users-gear me-2"></i><?= h($this->fetch('title')) ?></h1>
                <?= $this->Action->render(\App\View\Action\UsersActions::index()) ?>
            </div>
            <div class="card-body bulk-departments-card-body">

                <?php if (!($canEditDepartments ?? false)): ?>
                    <div class="alert alert-warning mb-0" role="alert">
                        <?= __('Votre profil ne permet pas de modifier les périmètres organisationnels.') ?>
                    </div>
                <?php else: ?>
                <form id="bulk-departments-form" class="bulk-departments-form" novalidate>
                    <div class="bulk-departments-tree">
                        <label class="form-label fw-semibold mb-1"><?= __('Périmètre Departments') ?></label>
                        <?= $this->element('Users/department_select', [
                            'fieldName' => 'department_ids',
                            'foreignKey' => 'id',
                            'hiddenContainerId' => 'bulk-department-ids',
                            'dataScriptId' => null,
                            'departmentsTree' => [],
                            'selectedDepartmentIds' => [],
                        ]) ?>
                    </div>

                    <div class="bulk-departments-actions">
                        <?= $this->Action->render(\App\View\Action\UsersActions::index(__('Annuler'), 'btn btn-secondary btn-sm')) ?>
                        <button id="bulk-departments-add" type="submit" class="btn btn-success btn-sm" data-association-mode="add" disabled>
                            <i class="fa-solid fa-plus me-1"></i><?= __('Ajouter les associations') ?>
                        </button>
                        <button id="bulk-departments-replace" type="submit" class="btn btn-danger btn-sm" data-association-mode="replace" disabled>
                            <i class="fa-solid fa-arrows-rotate me-1"></i><?= __('Remplacer les associations') ?>
                        </button>
                    </div>

                    <div class="bulk-departments-users">
                        <fieldset>
                            <legend class="form-label fw-semibold mb-2"><?= __('Utilisateurs concernés') ?></legend>
                            <div class="row g-3 align-items-stretch bulk-user-tables">
                                <div class="col-md-5 bulk-user-table">
                                    <label for="bulk-available-users-table" class="form-label small text-muted"><?= __('Utilisateurs à sélectionner') ?></label>
                                    <div id="bulk-available-users-table" aria-describedby="bulk-users-help"></div>
                                </div>
                                <div class="col-md-2 bulk-transfer-column">
                                    <span id="bulk-add-users-button"></span>
                                    <span id="bulk-remove-users-button"></span>
                                </div>
                                <div class="col-md-5 bulk-user-table">
                                    <label for="bulk-selected-users-table" class="form-label small text-muted"><?= __('Utilisateurs sélectionnés') ?></label>
                                    <div id="bulk-selected-users-table"></div>
                                </div>
                            </div>
                            <div id="bulk-users-help" class="form-text mt-2"><?= __('Filtrez chaque colonne dans les en-têtes. Sélectionnez une ou plusieurs lignes, puis utilisez les flèches ou un double-clic.') ?></div>
                        </fieldset>
                    </div>
                </form>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

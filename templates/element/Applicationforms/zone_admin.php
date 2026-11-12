<?php

/**
 * @file zone_admin.php
 * @description Élément représentant la Zone 1 (Administration) du formulaire.
 *
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Applicationform $applicationform
 * @var array<int, string> $collaborators
 * @var array<int, string> $assistants
 * @var int $assistantDefaultId
 * @var list<\App\Model\Entity\User> $departmentManagers
 * @var bool $canEditAdmin
 * @var bool|null $circuitLocked
 */

?>

<div class="card shadow-sm border-0 mb-3">
    <div class="card-header bg-light py-2">
        <h6 class="card-title mb-0 text-primary fw-bold">
            <i class="fa-solid fa-user-gear me-2"></i><?= __('Zone 1 : Administration & Candidat') ?>
        </h6>
    </div>
    <div class="card-body">
        <div class="row g-3">

            <!-- Sélection du Département -->
            <div class="col-md-12">
                <label class="form-label fs-7 fw-medium"><?= $this->FieldMetadata->label('Applicationforms', 'department_id') ?></label>
                <?php if ($circuitLocked ?? false): ?>
                    <div class="form-control form-control-sm bg-light"><?= h($applicationform->department->name ?? '') ?></div>
                <?php else: ?>
                    <div id="department-tree-select"></div>
                <?php endif; ?>

                <?= $this->Form->hidden('department_id', ['id' => 'department-id']) ?>
            </div>

            <div class="col-md-6">
                <?= $this->Form->control('assistant_id', [
                    'label' => __('Assistante'),
                    'options' => $assistants ?? [],
                    'empty' => __('-- Sélectionner --'),
                    'class' => 'form-select form-select-sm',
                    'disabled' => !$canEditAdmin || (int)$applicationform->get('department_id') < 1,
                    'data-assistants-url' => '/api/applicationforms/assistants-for-department.json',
                    'data-default-assistant-id' => (string)($assistantDefaultId ?? ''),
                    'data-can-edit' => $canEditAdmin ? 'true' : 'false',
                ]) ?>
                <div id="assistant-options-empty" class="form-text text-warning" hidden>
                    <?= __('Aucune assistante éligible pour ce département.') ?>
                </div>
                <div id="assistant-options-error" class="form-text text-danger" hidden>
                    <?= __('La liste des assistantes n’a pas pu être chargée.') ?>
                </div>
            </div>

            <div class="col-md-6">
                <label class="form-label"><?= __('Responsables du département') ?></label>
                <?php
                $canLoadDepartmentManagers = $canEditAdmin
                    && !($circuitLocked ?? false)
                    && ($fieldSchema['department_id'] ?? 'EDIT') === 'EDIT';
                $managerNames = array_map(
                    static fn($manager): string => (string)$manager->get('display_name'),
                    $departmentManagers,
                );
                ?>
                <div
                    id="department-managers"
                    class="form-control form-control-sm bg-light"
                    data-managers-url="/api/applicationforms/managers-for-department.json"
                    data-can-load="<?= $canLoadDepartmentManagers ? 'true' : 'false' ?>"
                    aria-live="polite"
                ><?= h($managerNames === [] ? __('Aucun responsable associé') : implode(', ', $managerNames)) ?></div>
                <div id="department-managers-error" class="form-text text-danger" hidden>
                    <?= __('La liste des responsables n’a pas pu être chargée.') ?>
                </div>
            </div>

            <!-- Intitulé du poste -->
            <div class="col-md-12">
                <?= $this->Form->control('jobtitle', [
                    'id' => 'jobtitle',
                    'label' => ['text' => $this->FieldMetadata->label('Applicationforms', 'jobtitle'), 'class' => 'form-label fs-7 fw-medium'],
                    'class' => 'form-control form-control-sm',
                    'placeholder' => __('Saisir l\'intitulé du poste...'),
                    'required' => true,
                    'disabled' => !$canEditAdmin,
                ]) ?>
            </div>

            <!-- Sélecteur Collaborateur Interne -->
            <div class="col-md-6 collaborator-select-wrapper">
                <?= $this->Form->control('collaborator_id', [
                    'id' => 'collaborator-id',
                    'label' => ['text' => $this->FieldMetadata->label('Applicationforms', 'collaborator_id'), 'class' => 'form-label fs-7 fw-medium'],
                    'options' => $collaborators ?? [],
                    'empty' => __('--- Candidat externe / Saisie libre ---'),
                    'class' => 'form-select form-select-sm',
                    'disabled' => !$canEditAdmin
                ]) ?>
            </div>

            <!-- Champ Texte Candidat Externe / Nom du poste -->
            <div class="col-md-6">
                <?= $this->Form->control('applicant_first_name', [
                    'label' => __('Prénom du candidat'),
                    'class' => 'form-control form-control-sm',
                    'disabled' => !$canEditAdmin,
                ]) ?>
            </div>

            <div class="col-md-6">
                <?= $this->Form->control('applicant_last_name', [
                    'label' => __('Nom du candidat'),
                    'class' => 'form-control form-control-sm',
                    'disabled' => !$canEditAdmin,
                ]) ?>
            </div>

            <div class="col-md-6">
                <?= $this->Form->control('applicantname', [
                    'id' => 'applicantname',
                    'label' => ['text' => $this->FieldMetadata->label('Applicationforms', 'applicantname'), 'class' => 'form-label fs-7 fw-medium'],
                    'class' => 'form-control form-control-sm',
                    'placeholder' => __('Saisir le nom du candidat...'),
                    'disabled' => !$canEditAdmin
                ]) ?>
            </div>

            <!-- Saisie dynamique du Code CGR -->
            <div class="col-md-12 mt-2">
                <label class="form-label fs-7 fw-medium text-primary">
                    <i class="fa-solid fa-sitemap me-1"></i> <?= $this->FieldMetadata->label('Applicationforms', 'cgr') ?>
                </label>

                <!-- 1. Conteneur vide où le JavaScript injectera les select (Secteur, Axe, etc.) -->
                <div id="cgr-components-container" class="d-flex flex-wrap gap-2 mb-2"></div>

                <!-- 2. Champ cible lu par CakePHP lors de la sauvegarde -->
                <?= $this->Form->control('cgr', [
                    'id' => 'cgr-final-input',
                    'type' => 'text',
                    'label' => false,
                    'class' => 'form-control form-control-sm bg-light',
                    'readonly' => true,
                    'disabled' => !$canEditAdmin,
                    'placeholder' => __('Sélectionnez d\'abord un département...')
                ]) ?>
            </div>

        </div>
    </div>
</div>

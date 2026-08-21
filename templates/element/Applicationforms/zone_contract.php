<?php
/**
 * Element : Zone Caractéristiques du contrat
 *
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Applicationform $applicationform
 * @var array<int, string> $contracttypes
 * @var array<int, string> $hiringreasons
 * @var array<string, string> $fieldSchema
 */

// Chargement du JS externe dédié dans le bloc 'script' du layout
$this->Html->script('views/Applicationforms/zone-contract', ['block' => true]);

// Traitement des autorisations au niveau des champs (Field-Level ACL)
$isEditable = function (string $field) use ($fieldSchema): bool {
    return ($fieldSchema[$field] ?? 'EDIT') === 'EDIT';
};
?>

<div class="card h-100 shadow-sm border-0">
    <div class="card-header bg-light fw-bold text-uppercase fs-7 text-secondary">
        <i class="fa-solid fa-file-contract me-1"></i> <?= __('2. Caractéristiques du Contrat') ?>
    </div>
    <div class="card-body">
        <div class="row g-3">

            <!-- Type de contrat -->
            <div class="col-md-6">
                <label for="contracttype-id" class="form-label fw-bold">
                    <i class="fa-solid fa-file-signature text-secondary me-1"></i>
                    <?= __('Type de contrat') ?> <span class="text-danger">*</span>
                </label>
                <?= $this->Form->control('contracttype_id', [
                    'type' => 'select',
                    'options' => $contracttypes,
                    'empty' => __('-- Sélectionner --'),
                    'label' => false,
                    'class' => 'form-select',
                    'id' => 'contracttype-id',
                    'disabled' => !$isEditable('contracttype_id'),
                    'required' => true,
                ]) ?>
            </div>

            <!-- Motif de recrutement -->
            <div class="col-md-6">
                <label for="hiringreason-id" class="form-label fw-bold">
                    <i class="fa-solid fa-clipboard-question text-secondary me-1"></i>
                    <?= __('Motif de recrutement') ?> <span class="text-danger">*</span>
                </label>
                <?= $this->Form->control('hiringreason_id', [
                    'type' => 'select',
                    'options' => $hiringreasons,
                    'empty' => __('-- Sélectionner --'),
                    'label' => false,
                    'class' => 'form-select',
                    'id' => 'hiringreason-id',
                    'disabled' => !$isEditable('hiringreason_id'),
                    'required' => true,
                ]) ?>
            </div>

            <!-- Libellé libre expliquant le motif -->
            <div class="col-md-12">
                <label for="reasonforreplacement" class="form-label fw-bold">
                    <i class="fa-solid fa-pen-fancy text-secondary me-1"></i>
                    <?= __('Précisions motif / Remplacement') ?>
                </label>
                <?= $this->Form->control('reasonforreplacement', [
                    'type' => 'text',
                    'label' => false,
                    'class' => 'form-control',
                    'id' => 'reasonforreplacement',
                    'placeholder' => __('Expliquer le motif ou préciser la personne remplacée...'),
                    'disabled' => !$isEditable('reasonforreplacement'),
                ]) ?>
            </div>

            <!-- Date de début -->
            <div class="col-md-6">
                <label for="begin-at" class="form-label fw-bold">
                    <i class="fa-regular fa-calendar-check text-secondary me-1"></i>
                    <?= __('Date de début') ?>
                </label>
                <?= $this->Form->control('begin_at', [
                    'type' => 'date',
                    'label' => false,
                    'class' => 'form-control',
                    'id' => 'begin-at',
                    'disabled' => !$isEditable('begin_at'),
                ]) ?>
            </div>

            <!-- Date de fin -->
            <div class="col-md-6" id="container-end-at">
                <label for="end-at" class="form-label fw-bold">
                    <i class="fa-regular fa-calendar-xmark text-secondary me-1"></i>
                    <?= __('Date de fin') ?>
                    <span id="end-at-required-asterisk" class="text-danger d-none">*</span>
                </label>
                <?= $this->Form->control('end_at', [
                    'type' => 'date',
                    'label' => false,
                    'class' => 'form-control',
                    'id' => 'end-at',
                    'disabled' => !$isEditable('end_at'),
                ]) ?>
                <div id="cdi-warning-text" class="form-text text-muted d-none">
                    <?= __('Un contrat CDI ne peut pas comporter de date de fin.') ?>
                </div>
            </div>

        </div>
    </div>
</div>

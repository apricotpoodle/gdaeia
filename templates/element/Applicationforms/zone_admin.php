<?php
/**
 * Element : Zone Informations Administration
 *
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Applicationform $applicationform
 * @var array<int, string> $collaborators
 * @var array<string, string> $fieldSchema
 * @var bool $canEditAdmin
 */

// Traitement des autorisations au niveau des champs (Field-Level ACL)
$isEditable = function (string $field) use ($fieldSchema, $canEditAdmin): bool {
    if (!$canEditAdmin) {
        return false;
    }

    return ($fieldSchema[$field] ?? 'EDIT') === 'EDIT';
};
?>

<div class="card h-100 shadow-sm border-0">
    <div class="card-header bg-light fw-bold text-uppercase fs-7 text-secondary">
        <i class="fa-solid fa-shield-halved me-1"></i> <?= __('1. Informations Admin') ?>
    </div>
    <div class="card-body">
        <div class="row g-3">

            <!-- Sélecteur de Département via TreeselectJS -->
            <div class="col-md-6">
                <label for="department-id" class="form-label fw-bold">
                    <i class="fa-solid fa-sitemap text-secondary me-1"></i>
                    <?= __('Département') ?> <span class="text-danger">*</span>
                </label>

                <!-- Champ caché liant la sélection à l'ORM CakePHP -->
                <?= $this->Form->hidden('department_id', [
                    'id' => 'department-id',
                    'disabled' => !$isEditable('department_id'),
                ]) ?>

                <!-- Conteneur IHM du Treeselect -->
                <div id="department-tree-select"></div>
            </div>

            <!-- Zone d'injection dynamique des composants CGR -->
            <div class="col-md-6">
                <label for="cgr-final-input" class="form-label fw-bold">
                    <i class="fa-solid fa-layer-group text-secondary me-1"></i>
                    <?= __('Code CGR') ?>
                </label>

                <!-- Conteneur généré en JS (plusieurs selects selon la stratégie) -->
                <div id="cgr-components-container" class="d-flex gap-2 mb-2"></div>

                <!-- Champ réel persistant en base -->
                <?= $this->Form->control('cgr', [
                    'type' => 'text',
                    'id' => 'cgr-final-input',
                    'class' => 'form-control bg-light',
                    'readonly' => true,
                    'disabled' => !$isEditable('cgr'),
                    'label' => false,
                ]) ?>
            </div>

            <!-- Intitulé du poste -->
            <div class="col-md-12">
                <label for="jobtitle" class="form-label fw-bold">
                    <i class="fa-solid fa-briefcase text-secondary me-1"></i>
                    <?= __('Intitulé du poste') ?> <span class="text-danger">*</span>
                </label>
                <?= $this->Form->control('jobtitle', [
                    'type' => 'text',
                    'label' => false,
                    'class' => 'form-control',
                    'id' => 'jobtitle',
                    'disabled' => !$isEditable('jobtitle'),
                    'required' => true,
                ]) ?>
            </div>

            <!-- Nom du candidat pressenti -->
            <div class="col-md-6">
                <label for="applicantname" class="form-label fw-bold">
                    <i class="fa-solid fa-user-tag text-secondary me-1"></i>
                    <?= __('Nom du candidat pressenti') ?>
                </label>
                <?= $this->Form->control('applicantname', [
                    'type' => 'text',
                    'label' => false,
                    'class' => 'form-control',
                    'id' => 'applicantname',
                    'disabled' => !$isEditable('applicantname'),
                ]) ?>
            </div>

            <!-- Collaborateur concerné (Candidat interne) -->
            <div class="col-md-6">
                <label for="collaborator-id" class="form-label fw-bold">
                    <i class="fa-solid fa-user-gear text-secondary me-1"></i>
                    <?= __('Collaborateur concerné (Candidat interne)') ?>
                </label>
                <?= $this->Form->control('collaborator_id', [
                    'type' => 'select',
                    'options' => $collaborators,
                    'empty' => __('-- Sélectionner --'),
                    'label' => false,
                    'class' => 'form-select',
                    'id' => 'collaborator-id',
                    'disabled' => !$isEditable('collaborator_id'),
                ]) ?>
            </div>

        </div>
    </div>
</div>

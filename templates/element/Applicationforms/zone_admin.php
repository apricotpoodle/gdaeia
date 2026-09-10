<?php

/**
 * @file zone_admin.php
 * @description Élément représentant la Zone 1 (Administration) du formulaire.
 *
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Applicationform $applicationform
 * @var array<int, string> $collaborators
 * @var array<string, string> $fieldSchema
 * @var bool $canEditAdmin
 */

// Chargement du script de gestion dynamique du candidat
$this->Html->script('views/Applicationforms/applicationform-candidate', ['block' => true]);
// Chargement du script de gestion dynamique du CGR
$this->Html->script('views/Applicationforms/applicationform-cgr', ['block' => true]);
?>

<div class="card shadow-sm border-0 mb-3">
    <div class="card-header bg-light py-2">
        <h6 class="card-title mb-0 text-primary fw-bold">
            <i class="fa-solid fa-user-gear me-2"></i><?= __('Zone 1 : Administration & Candidat') ?>
        </h6>
    </div>
    <div class="card-body">
        <div class="row g-3">

            <!-- Sélecteur Collaborateur Interne -->
            <div class="col-md-6 collaborator-select-wrapper">
                <?= $this->Form->control('collaborator_id', [
                    'id' => 'collaborator-id',
                    'label' => ['text' => __('Collaborateur interne pressenti'), 'class' => 'form-label fs-7 fw-medium'],
                    'options' => $collaborators ?? [],
                    'empty' => __('--- Candidat externe / Saisie libre ---'),
                    'class' => 'form-select form-select-sm',
                    'disabled' => !$canEditAdmin
                ]) ?>
            </div>

            <!-- Champ Texte Candidat Externe / Nom du poste -->
            <div class="col-md-6">
                <?= $this->Form->control('applicantname', [
                    'id' => 'applicantname',
                    'label' => ['text' => __('Intitulé / Nom du candidat'), 'class' => 'form-label fs-7 fw-medium'],
                    'class' => 'form-control form-control-sm',
                    'placeholder' => __('Saisir le nom du candidat...'),
                    'disabled' => !$canEditAdmin
                ]) ?>
            </div>

            <!-- Sélection du Département -->
            <div class="col-md-12">
                <label class="form-label fs-7 fw-medium"><?= __('Département') ?></label>
                <!-- Le composant visuel Treeselect s'accroche ici : -->
                <div id="department-tree-select"></div>

                <!-- L'input CakePHP doit impérativement être masqué (hidden) : -->
                <?= $this->Form->hidden('department_id', ['id' => 'department-id']) ?>
            </div>

            <!-- Saisie dynamique du Code CGR -->
            <div class="col-md-12 mt-2">
                <label class="form-label fs-7 fw-medium text-primary">
                    <i class="fa-solid fa-sitemap me-1"></i> <?= __('Code CGR') ?>
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

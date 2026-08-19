<?php
/**
 * Vue de création d'une Demande de Recrutement
 * @var \App\View\AppView $this
 */

$this->Html->script('views/Applicationforms/create.js', ['type' => 'module', 'block' => true]);
?>

<div class="applicationforms add content">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3><?= __('Nouvelle Demande de Recrutement') ?></h3>
        <a href="<?= $this->Url->build(['action' => 'index']) ?>" class="btn btn-outline-secondary btn-sm">
            <i class="fas fa-arrow-left me-1"></i> <?= __('Retour à la liste') ?>
        </a>
    </div>

    <form id="applicationform-create-form" class="card shadow-sm p-4">
        <div class="row g-3">
            <div class="col-md-6 form-group-wrapper">
                <label for="jobtitle" class="form-label fw-bold"><?= __('Intitulé du poste') ?></label>
                <input type="text" name="jobtitle" id="jobtitle" class="form-control" required />
            </div>

            <div class="col-md-6 form-group-wrapper">
                <label for="department-id" class="form-label fw-bold"><?= __('Département') ?></label>
                <select name="department_id" id="department-id" class="form-select" required>
                    <option value=""><?= __('-- Sélectionner --') ?></option>
                </select>
            </div>

            <div class="col-md-6 form-group-wrapper">
                <label for="contracttype-id" class="form-label fw-bold"><?= __('Type de contrat') ?></label>
                <select name="contracttype_id" id="contracttype-id" class="form-select" required>
                    <option value=""><?= __('-- Sélectionner --') ?></option>
                </select>
            </div>

            <div class="col-md-6 form-group-wrapper">
                <label for="hiringreason-id" class="form-label fw-bold"><?= __('Motif de recrutement') ?></label>
                <select name="hiringreason_id" id="hiringreason-id" class="form-select" required>
                    <option value=""><?= __('-- Sélectionner --') ?></option>
                </select>
            </div>

            <div class="col-md-6 form-group-wrapper">
                <label for="professionalcategory-id" class="form-label fw-bold"><?= __('Catégorie professionnelle') ?></label>
                <select name="professionalcategory_id" id="professionalcategory-id" class="form-select" required>
                    <option value=""><?= __('-- Sélectionner --') ?></option>
                </select>
            </div>

            <div class="col-md-6 form-group-wrapper">
                <label for="worktime-id" class="form-label fw-bold"><?= __('Temps de travail') ?></label>
                <select name="worktime_id" id="worktime-id" class="form-select" required>
                    <option value=""><?= __('-- Sélectionner --') ?></option>
                </select>
            </div>

            <div class="col-md-4 form-group-wrapper">
                <label for="period-id" class="form-label fw-bold"><?= __('Période') ?></label>
                <select name="period_id" id="period-id" class="form-select" required>
                    <option value=""><?= __('-- Sélectionner --') ?></option>
                </select>
            </div>

            <div class="col-md-4 form-group-wrapper">
                <label for="budgetfeature-id" class="form-label fw-bold"><?= __('Élément budgétaire') ?></label>
                <select name="budgetfeature_id" id="budgetfeature-id" class="form-select" required>
                    <option value=""><?= __('-- Sélectionner --') ?></option>
                </select>
            </div>

            <div class="col-md-4 form-group-wrapper">
                <label for="yesno-id" class="form-label fw-bold"><?= __('Prévu au budget') ?></label>
                <select name="yesno_id" id="yesno-id" class="form-select" required>
                    <option value=""><?= __('-- Sélectionner --') ?></option>
                </select>
            </div>

            <div class="col-12 form-group-wrapper">
                <label for="reasonforreplacement" class="form-label"><?= __('Précisions / Remplacement') ?></label>
                <textarea name="reasonforreplacement" id="reasonforreplacement" class="form-control" rows="3"></textarea>
            </div>
        </div>

        <div class="d-flex justify-content-end gap-2 mt-4">
            <a href="<?= $this->Url->build(['action' => 'index']) ?>" class="btn btn-light"><?= __('Annuler') ?></a>
            <button type="submit" class="btn btn-success">
                <i class="fas fa-save me-1"></i> <?= __('Enregistrer la demande') ?>
            </button>
        </div>
    </form>
</div>

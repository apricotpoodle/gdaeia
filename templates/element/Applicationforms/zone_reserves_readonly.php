<?php

/**
 * Zone 4 de consultation : champs réservés administration et RH.
 *
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Applicationform $applicationform
 */
?>

<div class="card h-100 shadow-sm border-0 mb-3 border-start border-4 border-warning">
    <div class="card-header bg-light fw-bold text-uppercase fs-7 text-warning-emphasis">
        <i class="fa-solid fa-lock me-1" aria-hidden="true"></i><?= __('4. Champs Réservés Administration/RH') ?>
    </div>
    <div class="card-body">
        <div class="row g-3">
            <?= $this->element('Applicationforms/display_field', [
                'label' => __('Descriptif / motif RH'),
                'value' => $applicationform->hr_description,
                'valueClass' => 'text-break',
            ]) ?>
            <?= $this->element('Applicationforms/display_field', [
                'label' => __('Validation Contrôle de gestion'),
                'value' => $applicationform->control_management_validation,
                'valueClass' => 'text-break',
            ]) ?>
            <?= $this->element('Applicationforms/display_field', [
                'label' => __('Argumentaire final'),
                'value' => $applicationform->final_argument,
                'valueClass' => 'text-break',
            ]) ?>
            <?= $this->element('Applicationforms/display_field', [
                'label' => __('Caractéristique budgétaire'),
                'value' => $applicationform->budgetfeature->name ?? $applicationform->budgetfeature->label ?? null,
            ]) ?>
            <?= $this->element('Applicationforms/display_field', [
                'label' => __('Inscrit au budget'),
                'value' => $applicationform->yesno->name ?? $applicationform->yesno->label ?? null,
            ]) ?>
            <?= $this->element('Applicationforms/display_field', [
                'label' => __('Qualification retenue'),
                'value' => $applicationform->qualification,
                'valueClass' => 'text-break',
            ]) ?>
        </div>
    </div>
</div>

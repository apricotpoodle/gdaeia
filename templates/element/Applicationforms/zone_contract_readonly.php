<?php

/**
 * Zone 2 de consultation : caractéristiques du contrat.
 *
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Applicationform $applicationform
 */

?>

<div class="card h-100 shadow-sm border-0 mb-3">
    <div class="card-header bg-light fw-bold text-uppercase fs-7 text-secondary">
        <i class="fa-solid fa-file-contract me-1" aria-hidden="true"></i><?= __('2. Caractéristiques du Contrat') ?>
    </div>
    <div class="card-body">
        <div class="row g-3">
            <?= $this->element('Applicationforms/display_field', [
                'label' => __('Type de contrat'),
                'value' => $applicationform->contracttype->name ?? $applicationform->contracttype->code ?? null,
            ]) ?>
            <?= $this->element('Applicationforms/display_field', [
                'label' => __('Motif de recrutement'),
                'value' => $applicationform->hiringreason->name ?? $applicationform->hiringreason->label ?? null,
            ]) ?>
            <?= $this->element('Applicationforms/display_field', [
                'label' => __('Motif détaillé'),
                'value' => $applicationform->reasonforreplacement,
                'valueClass' => 'text-break',
            ]) ?>
            <?= $this->element('Applicationforms/display_field', [
                'label' => __('Date de début'),
                'value' => $applicationform->begin_at?->format('d/m/Y'),
            ]) ?>
            <?= $this->element('Applicationforms/display_field', [
                'label' => __('Date de fin'),
                'value' => $applicationform->end_at?->format('d/m/Y'),
            ]) ?>
            <?= $this->element('Applicationforms/display_field', [
                'label' => __('Nombre de jours ouvrés'),
                'value' => $applicationform->working_days === null ? null : (string)$applicationform->working_days,
            ]) ?>
        </div>
    </div>
</div>

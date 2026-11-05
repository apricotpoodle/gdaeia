<?php

/**
 * Zone 1 de consultation : administration et candidat.
 *
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Applicationform $applicationform
 */

$departmentManagers = $applicationform->department->managers ?? [];
?>

<div class="card shadow-sm border-0 mb-3">
    <div class="card-header bg-light py-2">
        <h6 class="card-title mb-0 text-primary fw-bold">
            <i class="fa-solid fa-user-gear me-2" aria-hidden="true"></i><?= __('Zone 1 : Administration & Candidat') ?>
        </h6>
    </div>
    <div class="card-body">
        <div class="row g-3">
            <?= $this->element('Applicationforms/display_field', [
                'label' => __('Assistante'),
                'value' => $applicationform->assistant?->get('display_name'),
            ]) ?>
            <?= $this->element('Applicationforms/display_field', [
                'label' => __('Responsables du département'),
                'value' => $departmentManagers !== []
                    ? implode(', ', array_map(static fn($manager): string => (string)$manager->get('display_name'), $departmentManagers))
                    : __('Aucun responsable associé'),
            ]) ?>
            <?= $this->element('Applicationforms/display_field', [
                'label' => __('Intitulé du poste'),
                'value' => $applicationform->jobtitle,
            ]) ?>
            <?= $this->element('Applicationforms/display_field', [
                'label' => __('Candidat'),
                'value' => $applicationform->candidate_name,
            ]) ?>
            <?= $this->element('Applicationforms/display_field', [
                'label' => __('Prénom du candidat'),
                'value' => $applicationform->applicant_first_name,
            ]) ?>
            <?= $this->element('Applicationforms/display_field', [
                'label' => __('Nom du candidat'),
                'value' => $applicationform->applicant_last_name,
            ]) ?>
            <?= $this->element('Applicationforms/display_field', [
                'label' => __('Département'),
                'value' => $applicationform->department->name ?? null,
            ]) ?>
            <?= $this->element('Applicationforms/display_field', [
                'label' => __('Code CGR'),
                'value' => $applicationform->cgr,
                'valueClass' => 'font-monospace',
            ]) ?>
        </div>
    </div>
</div>

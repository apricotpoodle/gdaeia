<?php

/**
 * Zone 3 de consultation : temps de travail et rémunération.
 *
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Applicationform $applicationform
 */

use Cake\I18n\Number;
?>

<div class="card h-100 shadow-sm border-0 mb-3">
    <div class="card-header bg-light fw-bold text-uppercase fs-7 text-secondary">
        <i class="fa-solid fa-euro-sign me-1" aria-hidden="true"></i><?= __('3. Temps de travail & Rémunération') ?>
    </div>
    <div class="card-body">
        <div class="row g-3">
            <?= $this->element('Applicationforms/display_field', [
                'label' => __('Catégorie professionnelle'),
                'value' => $applicationform->professionalcategory->name ?? $applicationform->professionalcategory->label ?? null,
            ]) ?>
            <?= $this->element('Applicationforms/display_field', [
                'label' => __('Temps de travail'),
                'value' => $applicationform->worktime->name ?? $applicationform->worktime->label ?? null,
            ]) ?>
            <?= $this->element('Applicationforms/display_field', [
                'label' => __('Rémunération brute'),
                'value' => $applicationform->grossremuneration === null
                    ? null
                    : Number::currency($applicationform->grossremuneration, 'EUR', ['locale' => 'fr_FR']),
            ]) ?>
            <?= $this->element('Applicationforms/display_field', [
                'label' => __('Périodicité'),
                'value' => $applicationform->period?->get('name'),
            ]) ?>
            <?= $this->element('Applicationforms/display_field', [
                'label' => __('Salaire de base calculé'),
                'value' => $applicationform->salary_base === null
                    ? null
                    : Number::currency($applicationform->salary_base, 'EUR', ['locale' => 'fr_FR']),
            ]) ?>
        </div>
    </div>
</div>

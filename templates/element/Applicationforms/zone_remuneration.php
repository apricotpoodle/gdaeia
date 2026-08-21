<?php
/**
 * Element : Zone Temps de travail & Rémunération
 *
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Applicationform $applicationform
 * @var array<int, string> $professionalcategories
 * @var array<int, string> $worktimes
 * @var array<int, string> $periods
 * @var array<string, string> $fieldSchema
 * @var bool $canEditRemuneration
 */

// Traitement des autorisations au niveau des champs (Field-Level ACL)
$isEditable = function (string $field) use ($fieldSchema, $canEditRemuneration): bool {
    if (!$canEditRemuneration) {
        return false;
    }

    return ($fieldSchema[$field] ?? 'EDIT') === 'EDIT';
};
?>

<div class="card h-100 shadow-sm border-0">
    <div class="card-header bg-light fw-bold text-uppercase fs-7 text-secondary">
        <i class="fa-solid fa-euro-sign me-1"></i> <?= __('3. Temps de travail & Rémunération') ?>
    </div>
    <div class="card-body">
        <div class="row g-3">

            <!-- Catégorie professionnelle -->
            <div class="col-md-6">
                <label for="professionalcategory-id" class="form-label fw-bold">
                    <i class="fa-solid fa-layer-group text-secondary me-1"></i>
                    <?= __('Catégorie professionnelle') ?> <span class="text-danger">*</span>
                </label>
                <?= $this->Form->control('professionalcategory_id', [
                    'type' => 'select',
                    'options' => $professionalcategories,
                    'empty' => __('-- Sélectionner --'),
                    'label' => false,
                    'class' => 'form-select',
                    'id' => 'professionalcategory-id',
                    'disabled' => !$isEditable('professionalcategory_id'),
                    'required' => true,
                ]) ?>
            </div>

            <!-- Temps de travail -->
            <div class="col-md-6">
                <label for="worktime-id" class="form-label fw-bold">
                    <i class="fa-solid fa-business-time text-secondary me-1"></i>
                    <?= __('Temps de travail') ?> <span class="text-danger">*</span>
                </label>
                <?= $this->Form->control('worktime_id', [
                    'type' => 'select',
                    'options' => $worktimes,
                    'empty' => __('-- Sélectionner --'),
                    'label' => false,
                    'class' => 'form-select',
                    'id' => 'worktime-id',
                    'disabled' => !$isEditable('worktime_id'),
                    'required' => true,
                ]) ?>
            </div>

            <!-- Rémunération Brute -->
            <div class="col-md-6">
                <label for="grossremuneration" class="form-label fw-bold">
                    <i class="fa-solid fa-money-bill-wave text-secondary me-1"></i>
                    <?= __('Rémunération Brute') ?> <span class="text-danger">*</span>
                </label>
                <?= $this->Form->control('grossremuneration', [
                    'type' => 'number',
                    'step' => '0.01',
                    'label' => false,
                    'class' => 'form-control',
                    'id' => 'grossremuneration',
                    'disabled' => !$isEditable('grossremuneration'),
                    'required' => true,
                ]) ?>
            </div>

            <!-- Périodicité -->
            <div class="col-md-6">
                <label for="period-id" class="form-label fw-bold">
                    <i class="fa-solid fa-rotate text-secondary me-1"></i>
                    <?= __('Périodicité') ?> <span class="text-danger">*</span>
                </label>
                <?= $this->Form->control('period_id', [
                    'type' => 'select',
                    'options' => $periods,
                    'empty' => __('-- Sélectionner --'),
                    'label' => false,
                    'class' => 'form-select',
                    'id' => 'period-id',
                    'disabled' => !$isEditable('period_id'),
                    'required' => true,
                ]) ?>
            </div>

        </div>
    </div>
</div>

<?php
/**
 * Element : Zone Champs Réservés Administration/RH
 *
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Applicationform $applicationform
 * @var array<int, string> $budgetfeatures
 * @var array<int, string> $yesnos
 * @var array<string, string> $fieldSchema
 * @var bool $canEditReserves
 */

// Traitement des autorisations au niveau des champs (Field-Level ACL)
$isEditable = function (string $field) use ($fieldSchema, $canEditReserves): bool {
    if (!$canEditReserves) {
        return false;
    }

    return ($fieldSchema[$field] ?? 'EDIT') === 'EDIT';
};
?>

<div class="card h-100 shadow-sm border-0 border-start border-4 border-warning">
    <div class="card-header bg-light fw-bold text-uppercase fs-7 text-warning-emphasis">
        <i class="fa-solid fa-lock me-1"></i> <?= __('4. Champs Réservés Administration/RH') ?>
    </div>
    <div class="card-body">
        <div class="row g-3">

            <!-- Caractéristique budgétaire -->
            <div class="col-md-6">
                <label for="budgetfeature-id" class="form-label fw-bold">
                    <i class="fa-solid fa-sack-dollar text-secondary me-1"></i>
                    <?= __('Caractéristique budgétaire') ?>
                </label>
                <?= $this->Form->control('budgetfeature_id', [
                    'type' => 'select',
                    'options' => $budgetfeatures,
                    'empty' => __('-- Sélectionner --'),
                    'label' => false,
                    'class' => 'form-select',
                    'id' => 'budgetfeature-id',
                    'disabled' => !$isEditable('budgetfeature_id'),
                ]) ?>
            </div>

            <!-- Inscrit au budget -->
            <div class="col-md-6">
                <label for="yesno-id" class="form-label fw-bold">
                    <i class="fa-solid fa-coins text-secondary me-1"></i>
                    <?= __('Inscrit au budget') ?>
                </label>
                <?= $this->Form->control('yesno_id', [
                    'type' => 'select',
                    'options' => $yesnos,
                    'empty' => __('-- Sélectionner --'),
                    'label' => false,
                    'class' => 'form-select',
                    'id' => 'yesno-id',
                    'disabled' => !$isEditable('yesno_id'),
                ]) ?>
            </div>

            <!-- Qualification retenue -->
            <div class="col-md-12">
                <label for="qualification" class="form-label fw-bold">
                    <i class="fa-solid fa-user-check text-secondary me-1"></i>
                    <?= __('Qualification retenue') ?>
                </label>
                <?= $this->Form->control('qualification', [
                    'type' => 'text',
                    'label' => false,
                    'class' => 'form-control',
                    'id' => 'qualification',
                    'placeholder' => __('Préciser la qualification retenue par les RH...'),
                    'disabled' => !$isEditable('qualification'),
                ]) ?>
            </div>

        </div>
    </div>
</div>

<?php
/**
 * Vue : Saisie / Création d'une demande de recrutement (Applicationforms)
 *
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Applicationform $applicationform
 * @var \Authorization\IdentityInterface $identity
 * @var array<int, string> $departments
 * @var array<int, string> $contracttypes
 * @var array<int, string> $hiringreasons
 * @var array<int, string> $professionalcategories
 * @var array<int, string> $worktimes
 * @var array<int, string> $periods
 * @var array<int, string> $budgetfeatures
 * @var array<int, string> $yesnos
 * @var array<int, string> $collaborators
 * @var array<string, string> $fieldSchema
 */

$this->assign('title', __('Nouvelle demande de recrutement'));

// Configuration du fil d'Ariane via BreadcrumbsHelper (DRY)
$this->Breadcrumbs->add(__('Demandes de recrutement'), ['action' => 'index']);
$this->Breadcrumbs->add(__('Nouvelle demande'));

// Inclusion des assets CSS et JS requis
$this->Html->css('vendor/treeselect/treeselectjs', ['block' => true]);
$this->Html->script('vendor/treeselect/treeselectjs.umd', ['block' => true]);
$this->Html->script('views/Applicationforms/applicationform-cgr', ['block' => true]);
$this->Html->script('views/Applicationforms/applicationform-treeselect', ['block' => true]);
?>

<div class="container-fluid mt-3">
    <!-- Barre d'entête & Actions -->
    <div class="d-flex justify-content-between align-items-center mb-3 pb-2 border-bottom">
        <h2>
            <i class="fa-solid fa-file-circle-plus text-primary me-2"></i>
            <?= __('Créer une nouvelle demande de recrutement') ?>
        </h2>

        <div class="btn-toolbar gap-2">
            <?= $this->Html->link(
                '<i class="fa-solid fa-arrow-left me-1"></i> ' . __('Retour à la liste'),
                ['action' => 'index'],
                ['class' => 'btn btn-outline-secondary', 'escape' => false]
            ) ?>
        </div>
    </div>

    <!-- Formulaire Principal -->
    <?= $this->Form->create($applicationform, ['id' => 'applicationform-main-form', 'class' => 'needs-validation']) ?>

    <div class="row g-4">

        <!-- ZONE 1 : ADMINISTRATION -->
        <?php if ($identity->can('viewZoneAdmin', $applicationform)): ?>
            <div class="col-12 col-lg-6">
                <?= $this->element('Applicationforms/zone_admin', [
                    'applicationform' => $applicationform,
                    'collaborators' => $collaborators ?? [],
                    'fieldSchema' => $fieldSchema ?? [],
                    'canEditAdmin' => $identity->can('editZoneAdmin', $applicationform),
                ]) ?>
            </div>
        <?php endif; ?>

        <!-- ZONE 2 : CARACTÉRISTIQUES DU CONTRAT -->
        <?php if ($identity->can('viewZoneContrat', $applicationform)): ?>
            <div class="col-12 col-lg-6">
                <?= $this->element('Applicationforms/zone_contract', [
                    'applicationform' => $applicationform,
                    'contracttypes' => $contracttypes ?? [],
                    'hiringreasons' => $hiringreasons ?? [],
                    'fieldSchema' => $fieldSchema ?? [],
                ]) ?>
            </div>
        <?php endif; ?>

        <!-- ZONE 3 : RÉMUNÉRATION & TEMPS DE TRAVAIL -->
        <?php if ($identity->can('viewZoneRemuneration', $applicationform)): ?>
            <div class="col-12 col-lg-6">
                <?= $this->element('Applicationforms/zone_remuneration', [
                    'applicationform' => $applicationform,
                    'professionalcategories' => $professionalcategories ?? [],
                    'worktimes' => $worktimes ?? [],
                    'periods' => $periods ?? [],
                    'fieldSchema' => $fieldSchema ?? [],
                    'canEditRemuneration' => $identity->can('editZoneRemuneration', $applicationform),
                ]) ?>
            </div>
        <?php endif; ?>

        <!-- ZONE 4 : RÉSERVÉS RH / ADMIN -->
        <?php if ($identity->can('viewZoneReserves', $applicationform)): ?>
            <div class="col-12 col-lg-6">
                <?= $this->element('Applicationforms/zone_reserves', [
                    'applicationform' => $applicationform,
                    'budgetfeatures' => $budgetfeatures ?? [],
                    'yesnos' => $yesnos ?? [],
                    'fieldSchema' => $fieldSchema ?? [],
                    'canEditReserves' => $identity->can('editZoneReserves', $applicationform),
                ]) ?>
            </div>
        <?php endif; ?>

    </div>

    <!-- Actions bas de page -->
    <div class="mt-4 mb-5 text-end">
        <?= $this->Html->link(
            '<i class="fa-solid fa-xmark me-1"></i> ' . __('Annuler'),
            ['action' => 'index'],
            ['class' => 'btn btn-secondary me-2', 'escape' => false]
        ) ?>
        <?= $this->Form->button(
            '<i class="fa-solid fa-paper-plane me-1"></i> ' . __('Créer la demande'),
            [
                'type' => 'submit',
                'class' => 'btn btn-primary px-4',
                'escapeTitle' => false,
            ]
        ) ?>
    </div>
    <?= $this->Form->end() ?>
</div>

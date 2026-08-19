<?php
/**
 * Vue de consultation d'une Demande de Recrutement
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Applicationform $applicationform
 */
?>

<div class="applicationforms view content">
    <!-- En-tête de la page avec boutons d'action -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="mb-1"><?= h($applicationform->jobtitle) ?></h3>
            <span class="badge bg-secondary">Demande #<?= h($applicationform->id) ?></span>
        </div>
        <div class="d-flex gap-2">
            <a href="<?= $this->Url->build(['action' => 'index']) ?>" class="btn btn-outline-secondary btn-sm">
                <i class="fas fa-arrow-left me-1"></i> <?= __('Retour à la liste') ?>
            </a>
            <a href="<?= $this->Url->build(['action' => 'edit', $applicationform->id]) ?>" class="btn btn-primary btn-sm">
                <i class="fas fa-edit me-1"></i> <?= __('Modifier') ?>
            </a>
        </div>
    </div>

    <div class="row g-4">
        <!-- Informations Générales -->
        <div class="col-md-6">
            <div class="card shadow-sm h-100">
                <div class="card-header bg-light fw-bold">
                    <i class="fas fa-info-circle me-2 text-primary"></i><?= __('Informations Générales') ?>
                </div>
                <div class="card-body">
                    <table class="table table-borderless mb-0">
                        <tr>
                            <th class="ps-0 text-muted" style="width: 40%;"><?= __('Département') ?></th>
                            <td class="fw-bold"><?= $applicationform->hasValue('department') ? h($applicationform->department->name) : '-' ?></td>
                        </tr>
                        <tr>
                            <th class="ps-0 text-muted"><?= __('Demandeur') ?></th>
                            <td><?= $applicationform->hasValue('user') ? h($applicationform->user->firstname . ' ' . $applicationform->user->lastname) : '-' ?></td>
                        </tr>
                        <tr>
                            <th class="ps-0 text-muted"><?= __('Intitulé du poste') ?></th>
                            <td class="fw-bold"><?= h($applicationform->jobtitle) ?></td>
                        </tr>
                        <tr>
                            <th class="ps-0 text-muted"><?= __('CGR') ?></th>
                            <td><?= h($applicationform->cgr ?: '-') ?></td>
                        </tr>
                        <tr>
                            <th class="ps-0 text-muted"><?= __('Créée le') ?></th>
                            <td><?= h($applicationform->created->format('d/m/Y H:i')) ?></td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>

        <!-- Caractéristiques du Poste & Contrat -->
        <div class="col-md-6">
            <div class="card shadow-sm h-100">
                <div class="card-header bg-light fw-bold">
                    <i class="fas fa-briefcase me-2 text-primary"></i><?= __('Caractéristiques du Poste') ?>
                </div>
                <div class="card-body">
                    <table class="table table-borderless mb-0">
                        <tr>
                            <th class="ps-0 text-muted" style="width: 40%;"><?= __('Type de contrat') ?></th>
                            <td><span class="badge bg-info text-dark"><?= $applicationform->hasValue('contracttype') ? h($applicationform->contracttype->name) : '-' ?></span></td>
                        </tr>
                        <tr>
                            <th class="ps-0 text-muted"><?= __('Motif du recrutement') ?></th>
                            <td><?= $applicationform->hasValue('hiringreason') ? h($applicationform->hiringreason->name) : '-' ?></td>
                        </tr>
                        <tr>
                            <th class="ps-0 text-muted"><?= __('Catégorie Prof.') ?></th>
                            <td><?= $applicationform->hasValue('professionalcategory') ? h($applicationform->professionalcategory->name) : '-' ?></td>
                        </tr>
                        <tr>
                            <th class="ps-0 text-muted"><?= __('Temps de travail') ?></th>
                            <td><?= $applicationform->hasValue('worktime') ? h($applicationform->worktime->name) : '-' ?></td>
                        </tr>
                        <tr>
                            <th class="ps-0 text-muted"><?= __('Période') ?></th>
                            <td><?= $applicationform->hasValue('period') ? h($applicationform->period->name) : '-' ?></td>
                        </tr>
                    </table>
                </div>
            </div>
        </div>

        <!-- Volet Budgétaire & Précisions -->
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-header bg-light fw-bold">
                    <i class="fas fa-coins me-2 text-primary"></i><?= __('Volet Budgétaire & Précisions') ?>
                </div>
                <div class="card-body">
                    <div class="row g-3 mb-3">
                        <div class="col-md-4">
                            <span class="text-muted d-block small"><?= __('Prévu au budget') ?></span>
                            <span class="fw-bold"><?= $applicationform->hasValue('yesno') ? h($applicationform->yesno->name) : '-' ?></span>
                        </div>
                        <div class="col-md-4">
                            <span class="text-muted d-block small"><?= __('Caractéristique budgétaire') ?></span>
                            <span class="fw-bold"><?= $applicationform->hasValue('budgetfeature') ? h($applicationform->budgetfeature->name) : '-' ?></span>
                        </div>
                        <div class="col-md-4">
                            <span class="text-muted d-block small"><?= __('Rémunération brute') ?></span>
                            <span class="fw-bold"><?= number_format((float)$applicationform->grossremuneration, 2, ',', ' ') ?> €</span>
                        </div>
                    </div>
                    <?php if (!empty($applicationform->reasonforreplacement)): ?>
                        <hr />
                        <div>
                            <span class="text-muted d-block small mb-1"><?= __('Précisions / Motif du remplacement') ?></span>
                            <p class="mb-0 text-dark bg-light p-3 rounded border"><?= nl2br(h($applicationform->reasonforreplacement)) ?></p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

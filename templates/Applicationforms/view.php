<?php

/**
 * @file view.php
 * @description Vue de consultation optimisée pour une demande de recrutement (Applicationform).
 * Intègre un bandeau de traçabilité supérieur avec fil d'Ariane du département et chef de service.
 *
 * @var \App\View\AppView $this Instance de la vue CakePHP.
 * @var \App\Model\Entity\Applicationform $applicationform Entité de la demande.
 * @var \Authorization\IdentityInterface|null $identity Identité de l'utilisateur connecté.
 */

use Cake\I18n\Number;

$this->assign('title', __('Demande n°{0}', $applicationform->id));
?>

<div class="container-fluid mt-2 mb-4 px-3">

    <!-- =================================================================== -->
    <!-- 1. EN-TÊTE DE PAGE : Titre principal & Poste à pourvoir            -->
    <!-- =================================================================== -->
    <div class="d-flex justify-content-between align-items-center mb-3 pb-2 border-bottom">
        <div class="d-flex align-items-center gap-2">
            <h1 class="h4 mb-0 text-dark fw-bold">
                <i class="fa-solid fa-file-signature text-primary me-1" aria-hidden="true"></i>
                <?= __('Demande n°{0}', $applicationform->id) ?>
            </h1>

            <!-- Badge Intitulé du poste (jobtitle / hiringreason) -->
            <?php
            $jobTitleDisplay = $applicationform->jobtitle
                ?: ($applicationform->hiringreason->name ?? $applicationform->applicantname ?? null);
            ?>
            <?php if ($jobTitleDisplay): ?>
                <span class="badge bg-primary-subtle text-primary border border-primary-subtle fs-7 px-2 py-1">
                    <i class="fa-solid fa-user-tag me-1" aria-hidden="true"></i>
                    <?= h($jobTitleDisplay) ?>
                </span>
            <?php endif; ?>
        </div>

        <!-- Actions contextuelles (soumises aux Policies via $identity) -->
        <div class="d-flex gap-2">
            <?= $this->Html->link(
                '<i class="fa-solid fa-arrow-left me-1" aria-hidden="true"></i> ' . __('Retour'),
                ['action' => 'index'],
                ['class' => 'btn btn-sm btn-outline-secondary', 'escape' => false]
            ) ?>

            <?php if ($identity?->can('edit', $applicationform)): ?>
                <?= $this->Html->link(
                    '<i class="fa-solid fa-pen me-1" aria-hidden="true"></i> ' . __('Éditer'),
                    ['action' => 'edit', $applicationform->id],
                    ['class' => 'btn btn-sm btn-primary', 'escape' => false]
                ) ?>
            <?php endif; ?>

            <?php if ($identity?->can('delete', $applicationform)): ?>
                <?= $this->Form->postLink(
                    '<i class="fa-solid fa-trash me-1" aria-hidden="true"></i> ' . __('Supprimer'),
                    ['action' => 'delete', $applicationform->id],
                    [
                        'confirm' => __('⚠️ Supprimer la demande n° {0} ?', $applicationform->id),
                        'class' => 'btn btn-sm btn-outline-danger',
                        'escape' => false,
                    ]
                ) ?>
            <?php endif; ?>
        </div>
    </div>

    <!-- =================================================================== -->
    <!-- 2. BANDEAU SUPÉRIEUR DE TRAÇABILITÉ & ORGANIGRAMME                -->
    <!-- =================================================================== -->
    <div class="card border-0 bg-body-tertiary shadow-sm mb-3">
        <div class="card-body py-2 px-3">
            <div class="row align-items-center g-2 fs-7">

                <!-- Fil d'Ariane dynamique via TreeBehavior find('path') -->
                <div class="col-md-5">
                    <span class="text-muted fs-8 d-block fw-semibold text-uppercase">
                        <i class="fa-solid fa-sitemap me-1 text-primary"></i><?= __('Structure & Département') ?>
                    </span>
                    <nav aria-label="breadcrumb" class="mb-0">
                        <ol class="breadcrumb mb-0 fs-8">
                            <?php if (!empty($departmentPath)): ?>
                                <?php
                                $lastIndex = count($departmentPath) - 1;
                                foreach ($departmentPath as $index => $dept):
                                    $isLast = ($index === $lastIndex);
                                ?>
                                    <li class="breadcrumb-item <?= $isLast ? 'active fw-bold text-dark' : 'text-secondary' ?>" <?= $isLast ? 'aria-current="page"' : '' ?>>
                                        <?php if ($isLast): ?>
                                            <!-- Dernier élément : Nom complet -->
                                            <?= h($dept->name ?? $dept->code) ?>
                                        <?php else: ?>
                                            <!-- Parents / Ancêtres : Code le plus court -->
                                            <?= h($dept->code ?: $dept->name) ?>
                                        <?php endif; ?>
                                    </li>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <li class="breadcrumb-item active fw-bold text-dark">
                                    <?= h($applicationform->department->name ?? '-') ?>
                                </li>
                            <?php endif; ?>
                        </ol>
                    </nav>
                </div>

                <!-- Chef de service rattaché ($department->manager) -->
                <div class="col-md-3 border-start">
                    <span class="text-muted fs-8 d-block fw-semibold text-uppercase">
                        <i class="fa-solid fa-user-tie me-1 text-primary"></i><?= __('Chef de service') ?>
                    </span>
                    <span class="fw-medium text-dark">
                        <?php
                        $manager = $applicationform->department->manager ?? null;
                        if ($manager) {
                            echo h($manager->full_name ?? $manager->display_name ?? $manager->email);
                        } else {
                            echo __('Non assigné');
                        }
                        ?>
                    </span>
                </div>

                <!-- Demandeur & Traçabilité dates -->
                <div class="col-md-4 border-start">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <span class="text-muted fs-8 d-block fw-semibold text-uppercase">
                                <i class="fa-solid fa-user me-1 text-primary"></i><?= __('Demandeur') ?>
                            </span>
                            <span class="fw-medium text-dark"><?= h($applicationform->user->email ?? '-') ?></span>
                        </div>
                        <div class="text-end border-start ps-2">
                            <span class="text-muted fs-8 d-block">
                                <?= __('Créée le') ?> <strong><?= $applicationform->created?->format('d/m/Y H:i') ?? '-' ?></strong>
                            </span>
                            <span class="text-muted fs-8 d-block">
                                <?= __('Modifiée le') ?> <strong><?= $applicationform->modified?->format('d/m/Y H:i') ?? '-' ?></strong>
                            </span>
                        </div>
                    </div>
                </div>

            </div>
        </div>

        <!-- =================================================================== -->
        <!-- 3. ZONE PRINCIPALE : ONGLETS MÉTIERS (PLEINE LARGEUR)             -->
        <!-- =================================================================== -->
        <ul class="nav nav-tabs nav-tabs-sm border-bottom-0 mb-0" id="viewTabs" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-item nav-link active py-2 fs-7 fw-semibold" id="details-tab" data-bs-toggle="tab" data-bs-target="#details-pane" type="button" role="tab" aria-controls="details-pane" aria-selected="true">
                    <i class="fa-solid fa-align-left me-1 text-primary"></i><?= __('Détails de la demande') ?>
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-item nav-link py-2 fs-7 fw-semibold" id="comments-tab" data-bs-toggle="tab" data-bs-target="#comments-pane" type="button" role="tab" aria-controls="comments-pane" aria-selected="false">
                    <i class="fa-solid fa-comments me-1 text-primary"></i><?= __('Commentaires') ?>
                    <?php if (!empty($applicationform->comments)): ?>
                        <span class="badge rounded-pill bg-primary fs-8 ms-1"><?= count($applicationform->comments) ?></span>
                    <?php endif; ?>
                </button>
            </li>
        </ul>

        <!-- Contenu des Onglets -->
        <div class="tab-content border rounded-bottom bg-white p-3 shadow-sm" id="viewTabsContent">

            <!-- ONGLET 1 : DÉTAILS DE LA DEMANDE -->
            <div class="tab-pane fade show active" id="details-pane" role="tabpanel" aria-labelledby="details-tab" tabindex="0">

                <!-- Section A : Informations sur le Poste -->
                <h6 class="text-primary border-bottom pb-1 mb-2 fs-7 text-uppercase fw-bold">
                    <i class="fa-solid fa-briefcase me-1"></i><?= __('Informations sur le poste') ?>
                </h6>
                <div class="row row-cols-1 row-cols-md-4 g-2 mb-3">
                    <div class="col">
                        <div class="col">
                            <span class="text-muted fs-8 d-block"><?= __('Intitulé / Candidat') ?></span>
                            <span class="fw-semibold text-dark fs-7">
                                <?= h($applicationform->candidate_name) ?>
                            </span>
                        </div>
                    </div>
                    <div class="col">
                        <span class="text-muted fs-8 d-block"><?= __('Motif de recrutement') ?></span>
                        <span class="fw-medium text-dark fs-7"><?= h($applicationform->hiringreason->name ?? $applicationform->hiringreason->label ?? '-') ?></span>
                    </div>
                    <div class="col">
                        <span class="text-muted fs-8 d-block"><?= __('Type de contrat') ?></span>
                        <span class="badge bg-info-subtle text-info-emphasis border border-info-subtle fs-8"><?= h($applicationform->contracttype->name ?? $applicationform->contracttype->code ?? '-') ?></span>
                    </div>
                    <div class="col">
                        <span class="text-muted fs-8 d-block"><?= __('Catégorie pro.') ?></span>
                        <span class="fw-medium text-dark fs-7"><?= h($applicationform->professionalcategory->name ?? $applicationform->professionalcategory->label ?? '-') ?></span>
                    </div>
                    <div class="col">
                        <span class="text-muted fs-8 d-block"><?= __('Temps de travail') ?></span>
                        <span class="fw-medium text-dark fs-7"><?= h($applicationform->worktime->name ?? $applicationform->worktime->label ?? '-') ?></span>
                    </div>
                </div>

                <!-- Section B : Conditions Financières & Temporelles -->
                <h6 class="text-primary border-bottom pb-1 mb-2 fs-7 text-uppercase fw-bold">
                    <i class="fa-solid fa-euro-sign me-1"></i><?= __('Conditions financières & Calendrier') ?>
                </h6>
                <div class="row row-cols-1 row-cols-md-4 g-2">
                    <div class="col">
                        <span class="text-muted fs-8 d-block"><?= __('Code CGR') ?></span>
                        <span class="fw-medium font-monospace text-dark fs-7"><?= h($applicationform->cgr ?: '-') ?></span>
                    </div>
                    <div class="col">
                        <span class="text-muted fs-8 d-block"><?= __('Caractéristique budgétaire') ?></span>
                        <span class="fw-medium text-dark fs-7"><?= h($applicationform->budgetfeature->name ?? $applicationform->budgetfeature->label ?? '-') ?></span>
                    </div>
                    <div class="col">
                        <span class="text-muted fs-8 d-block"><?= __('Rémunération Brute') ?></span>
                        <span class="fw-bold text-dark fs-7">
                            <?= $applicationform->grossremuneration !== null
                                ? Number::currency($applicationform->grossremuneration, 'EUR', ['locale' => 'fr_FR'])
                                : '-' ?>
                            <small class="text-muted font-normal fs-8">(<?= h($applicationform->period->name ?? '-') ?>)</small>
                        </span>
                    </div>
                    <div class="col">
                        <span class="text-muted fs-8 d-block"><?= __('Période d\'activité') ?></span>
                        <span class="fw-medium text-dark fs-7">
                            <i class="fa-regular fa-calendar-check text-success me-1"></i><?= $applicationform->begin_at ? h($applicationform->begin_at->format('d/m/Y')) : '-' ?>
                            <span class="text-muted mx-1">→</span>
                            <i class="fa-regular fa-calendar-xmark text-danger me-1"></i><?= $applicationform->end_at ? h($applicationform->end_at->format('d/m/Y')) : '-' ?>
                        </span>
                    </div>
                </div>

            </div>

            <!-- ONGLET 2 : COMMENTAIRES -->
            <div class="tab-pane fade" id="comments-pane" role="tabpanel" aria-labelledby="comments-tab" tabindex="0">
                <?php if (!empty($applicationform->comments)): ?>
                    <div class="vstack gap-2">
                        <?php foreach ($applicationform->comments as $comment): ?>
                            <div class="p-2 rounded bg-light border">
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <span class="fw-semibold text-dark fs-8">
                                        <i class="fa-solid fa-user me-1 text-secondary"></i>
                                        <?= h($comment->user->email ?? __('Inconnu')) ?>
                                    </span>
                                    <span class="text-muted fs-8">
                                        <?= $comment->created ? h($comment->created->format('d/m/Y H:i')) : '-' ?>
                                    </span>
                                </div>
                                <div class="text-secondary fs-7 mb-0">
                                    <?= nl2br(h($comment->body ?? $comment->content ?? '')) ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="text-center py-3 text-muted fs-7">
                        <i class="fa-regular fa-comment-dots me-1"></i><?= __('Aucun commentaire pour cette demande.') ?>
                    </div>
                <?php endif; ?>
            </div>

        </div>
    </div>

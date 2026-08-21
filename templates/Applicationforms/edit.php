<?php
/**
 * Vue : Édition d'une demande de recrutement (Applicationforms)
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

$this->assign('title', __('Demande de recrutement #{0}', $applicationform->id));

// Inclusion des assets CSS et JS requis
$this->Html->css('vendor/treeselect/treeselectjs', ['block' => true]);
$this->Html->script('vendor/treeselect/treeselectjs.umd', ['block' => true]);
$this->Html->script('views/Applicationforms/applicationform-cgr', ['block' => true]);
$this->Html->script('views/Applicationforms/applicationform-treeselect', ['block' => true]);
$this->Html->script('views/Applicationforms/applicationform-comments', ['block' => true]);
?>

<div class="container-fluid mt-3">
    <!-- Barre d'entête & Actions -->
    <div class="d-flex justify-content-between align-items-center mb-3 pb-2 border-bottom">
        <h2>
            <span class="badge bg-primary me-2">#<?= h($applicationform->id) ?></span>
            <?= h($applicationform->jobtitle ?? __('Nouvelle Demande')) ?>
        </h2>

        <div class="btn-toolbar gap-2">
            <!-- Zone Commentaires : Bouton de déclenchement du volet latéral -->
            <?php if ($identity->can('viewZoneCommentaires', $applicationform)): ?>
                <button class="btn btn-outline-primary position-relative" type="button" data-bs-toggle="offcanvas" data-bs-target="#offcanvasComments" aria-controls="offcanvasComments">
                    <i class="fa-regular fa-comments me-1"></i> <?= __('Commentaires') ?>
                    <span id="comment-badge-count" class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger d-none">
                        0
                    </span>
                </button>
            <?php endif; ?>

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
            <?php $canEditReserves = $identity->can('editZoneReserves', $applicationform); ?>
            <div class="col-12 col-lg-6">
                <div class="card h-100 shadow-sm border-0 border-start border-4 border-warning">
                    <div class="card-header bg-light fw-bold text-uppercase fs-7 text-warning-emphasis">
                        <i class="fa-solid fa-lock me-1"></i> <?= __('4. Champs Réservés Administration/RH') ?>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <?= $this->Form->control('budgetfeature_id', [
                                    'label' => __('Caractéristique budgétaire'),
                                    'class' => 'form-select',
                                    'disabled' => !$canEditReserves,
                                ]) ?>
                            </div>
                            <div class="col-md-6">
                                <?= $this->Form->control('yesno_id', [
                                    'label' => __('Inscrit au budget'),
                                    'class' => 'form-select',
                                    'disabled' => !$canEditReserves,
                                ]) ?>
                            </div>
                            <div class="col-md-12">
                                <?= $this->Form->control('qualification', [
                                    'label' => __('Qualification retenue'),
                                    'class' => 'form-control',
                                    'disabled' => !$canEditReserves,
                                ]) ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>

    </div>

    <!-- Actions bas de page -->
    <div class="mt-4 mb-5 text-end">
        <?= $this->Form->button(
            '<i class="fa-solid fa-floppy-disk me-1"></i> ' . __('Enregistrer les modifications'),
            [
                'type' => 'submit',
                'class' => 'btn btn-primary px-4',
                'escapeTitle' => false,
            ]
        ) ?>
    </div>
    <?= $this->Form->end() ?>
</div>

<!-- ZONE 5 : COMMENTAIRES (Volet Latéral Offcanvas) -->
<?php if ($identity->can('viewZoneCommentaires', $applicationform)): ?>
    <div class="offcanvas offcanvas-end shadow-lg" tabindex="-1" id="offcanvasComments" aria-labelledby="offcanvasCommentsLabel" style="width: 450px;">
        <div class="offcanvas-header bg-primary text-white">
            <h5 class="offcanvas-title" id="offcanvasCommentsLabel">
                <i class="fa-solid fa-comments me-2"></i><?= __('Fil de discussion') ?>
            </h5>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas" aria-label="Close"></button>
        </div>
        <div class="offcanvas-body d-flex flex-column p-0">
            <!-- Zone dynamique des messages -->
            <div id="comments-container" class="flex-grow-1 p-3 overflow-auto">
                <div class="text-center text-muted py-4" id="comments-loading">
                    <div class="spinner-border spinner-border-sm text-primary me-2" role="status"></div>
                    <?= __('Chargement des commentaires...') ?>
                </div>
            </div>

            <!-- Formulaire d'ajout rapide -->
            <?php if ($identity->can('editZoneCommentaires', $applicationform)): ?>
                <div class="p-3 bg-light border-top">
                    <form id="add-comment-form">
                        <div class="mb-2">
                            <textarea id="comment-content" class="form-control" rows="2" placeholder="<?= __('Écrire un commentaire...') ?>" required></textarea>
                        </div>
                        <div class="d-flex justify-content-between align-items-center">
                            <select id="comment-type" class="form-select form-select-sm w-auto">
                                <option value="GENERAL"><?= __('Général') ?></option>
                                <option value="OBSERVATION"><?= __('Observation') ?></option>
                                <option value="HIRING_REASON"><?= __('Motif') ?></option>
                            </select>
                            <button type="submit" class="btn btn-sm btn-primary">
                                <i class="fa-solid fa-paper-plane me-1"></i><?= __('Publier') ?>
                            </button>
                        </div>
                    </form>
                </div>
            <?php endif; ?>
        </div>
    </div>
<?php endif; ?>

<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Applicationform $applicationform
 * @var \Authorization\IdentityInterface $identity
 */

$this->assign('title', __('Demande de recrutement #{0}', $applicationform->id));
?>

<div class="container-fluid mt-3">
    <!-- Barre d'entête & Actions -->
    <div class="d-flex justify-content-between align-items-center mb-3 pb-2 border-bottom">
        <h2>
            <i class="bi bi-file-earmark-text bg-primary text-white p-2 rounded me-2"></i>
            <i class="bi bi-file-earmark-text bg-primary text-white p-2 rounded me-2"><?=  h($applicationform->id) ?></i>
            <?= h($applicationform->jobtitle ?? __('Nouvelle Demande')) ?>
        </h2>
        
        <div class="btn-toolbar gap-2">
            <!-- Zone Commentaires : Bouton de déclenchement du volet latéral -->
            <?php if ($identity->can('viewZoneCommentaires', $applicationform)): ?>
                <button class="btn btn-outline-primary position-relative" type="button" data-bs-toggle="offcanvas" data-bs-target="#offcanvasComments" aria-controls="offcanvasComments">
                    <i class="bi bi-chat-left-text me-1"></i> <?= __('Commentaires') ?>
                    <span id="comment-badge-count" class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger d-none">
                        0
                    </span>
                </button>
            <?php endif; ?>

            <?= $this->Html->link(__('Retour à la liste'), ['action' => 'index'], ['class' => 'btn btn-outline-secondary']) ?>
        </div>
    </div>

    <!-- Formulaire Principal -->
    <?= $this->Form->create($applicationform, ['id' => 'applicationform-main-form', 'class' => 'needs-validation']) ?>
    
    <div class="row g-4">

        <!-- ZONE 1 : ADMINISTRATION -->
        <?php if ($identity->can('viewZoneAdmin', $applicationform)): ?>
            <?php $canEditAdmin = $identity->can('editZoneAdmin', $applicationform); ?>
            <div class="col-12 col-lg-6">
                <div class="card h-100 shadow-sm border-0">
                    <div class="card-header bg-light fw-bold text-uppercase fs-7 text-secondary">
                        <i class="bi bi-shield-lock me-1"></i> <?= __('1. Informations Admin') ?>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <?= $this->Form->control('department_id', [
                                    'label' => __('Département'),
                                    'class' => 'form-select',
                                    'id' => 'department-id',
                                    'disabled' => !$canEditAdmin,
                                ]) ?>
                            </div>

                            <!-- Zone d'injection dynamique des composants CGR -->
                            <div class="col-md-6">
                                <?= $this->Form->label('cgr', __('Code CGR')) ?>
                                
                                <!-- Conteneur généré en JS (plusieurs selects selon la stratégie) -->
                                <div id="cgr-components-container" class="d-flex gap-2 mb-2"></div>

                                <!-- Champ réel persistant en base -->
                                <?= $this->Form->control('cgr', [
                                    'type' => 'text',
                                    'id' => 'cgr-final-input',
                                    'class' => 'form-control bg-light',
                                    'readonly' => true,
                                    'disabled' => !$canEditAdmin, // désactivé uniquement si pas les droits sur la zone Admin
                                    'label' => false,
                                ]) ?>
                            </div>


                            <div class="col-md-12">
                                <?= $this->Form->control('jobtitle', [
                                    'label' => __('Intitulé du poste'),
                                    'class' => 'form-control',
                                    'disabled' => !$canEditAdmin,
                                ]) ?>
                            </div>
                            <div class="col-md-6">
                                <?= $this->Form->control('applicantname', [
                                    'label' => __('Nom du candidat pressenti'),
                                    'class' => 'form-control',
                                    'disabled' => !$canEditAdmin,
                                ]) ?>
                            </div>
                            <div class="col-md-6">
                                <?= $this->Form->control('collaborator_id', [
                                    'label' => __('Collaborateur concerné'),
                                    'class' => 'form-select',
                                    'empty' => __('-- Sélectionner --'),
                                    'disabled' => !$canEditAdmin,
                                ]) ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <!-- ZONE 2 : CONTRAT -->
        <?php if ($identity->can('viewZoneContrat', $applicationform)): ?>
            <?php $canEditContrat = $identity->can('editZoneContrat', $applicationform); ?>
            <div class="col-12 col-lg-6">
                <div class="card h-100 shadow-sm border-0">
                    <div class="card-header bg-light fw-bold text-uppercase fs-7 text-secondary">
                        <i class="bi bi-file-earmark-text me-1"></i> <?= __('2. Caractéristiques du Contrat') ?>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <?= $this->Form->control('contracttype_id', [
                                    'label' => __('Type de contrat'),
                                    'class' => 'form-select',
                                    'disabled' => !$canEditContrat,
                                ]) ?>
                            </div>
                            <div class="col-md-6">
                                <?= $this->Form->control('hiringreason_id', [
                                    'label' => __('Motif de recrutement'),
                                    'class' => 'form-select',
                                    'disabled' => !$canEditContrat,
                                ]) ?>
                            </div>
                            <div class="col-md-12">
                                <?= $this->Form->control('reasonforreplacement', [
                                    'label' => __('Précisions motif / Remplacement'),
                                    'class' => 'form-control',
                                    'disabled' => !$canEditContrat,
                                ]) ?>
                            </div>
                            <div class="col-md-6">
                                <?= $this->Form->control('begin_at', [
                                    'label' => __('Date de début'),
                                    'type' => 'date',
                                    'class' => 'form-control',
                                    'disabled' => !$canEditContrat,
                                ]) ?>
                            </div>
                            <div class="col-md-6">
                                <?= $this->Form->control('end_at', [
                                    'label' => __('Date de fin'),
                                    'type' => 'date',
                                    'class' => 'form-control',
                                    'disabled' => !$canEditContrat,
                                ]) ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <!-- ZONE 3 : RÉMUNÉRATION -->
        <?php if ($identity->can('viewZoneRemuneration', $applicationform)): ?>
            <?php $canEditRemuneration = $identity->can('editZoneRemuneration', $applicationform); ?>
            <div class="col-12 col-lg-6">
                <div class="card h-100 shadow-sm border-0">
                    <div class="card-header bg-light fw-bold text-uppercase fs-7 text-secondary">
                        <i class="bi bi-currency-euro me-1"></i> <?= __('3. Temps de travail & Rémunération') ?>
                    </div>
                    <div class="card-body">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <?= $this->Form->control('professionalcategory_id', [
                                    'label' => __('Catégorie professionnelle'),
                                    'class' => 'form-select',
                                    'disabled' => !$canEditRemuneration,
                                ]) ?>
                            </div>
                            <div class="col-md-6">
                                <?= $this->Form->control('worktime_id', [
                                    'label' => __('Temps de travail'),
                                    'class' => 'form-select',
                                    'disabled' => !$canEditRemuneration,
                                ]) ?>
                            </div>
                            <div class="col-md-6">
                                <?= $this->Form->control('grossremuneration', [
                                    'label' => __('Rémunération Brute'),
                                    'type' => 'number',
                                    'step' => '0.01',
                                    'class' => 'form-control',
                                    'disabled' => !$canEditRemuneration,
                                ]) ?>
                            </div>
                            <div class="col-md-6">
                                <?= $this->Form->control('period_id', [
                                    'label' => __('Périodicité'),
                                    'class' => 'form-select',
                                    'disabled' => !$canEditRemuneration,
                                ]) ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <!-- ZONE 4 : RÉSERVÉS RH / ADMIN -->
        <?php if ($identity->can('viewZoneReserves', $applicationform)): ?>
            <?php $canEditReserves = $identity->can('editZoneReserves', $applicationform); ?>
            <div class="col-12 col-lg-6">
                <div class="card h-100 shadow-sm border-0 border-start border-4 border-warning">
                    <div class="card-header bg-light fw-bold text-uppercase fs-7 text-warning-emphasis">
                        <i class="bi bi-lock me-1"></i> <?= __('4. Champs Réservés Administration/RH') ?>
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

    <div class="mt-4 mb-5 text-end">
        <?= $this->Form->button(__('Enregistrer les modifications'), ['class' => 'btn btn-primary px-4']) ?>
    </div>
    <?= $this->Form->end() ?>
</div>

<!-- ZONE 5 : COMMENTAIRES (Volet Latéral Offcanvas) -->
<?php if ($identity->can('viewZoneCommentaires', $applicationform)): ?>
    <div class="offcanvas offcanvas-end shadow-lg" tabindex="-1" id="offcanvasComments" aria-labelledby="offcanvasCommentsLabel" style="width: 450px;">
        <div class="offcanvas-header bg-primary text-white">
            <h5 class="offcanvas-title" id="offcanvasCommentsLabel">
                <i class="bi bi-chat-square-dots me-2"></i><?= __('Fil de discussion') ?>
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
                                <i class="bi bi-send me-1"></i><?= __('Publier') ?>
                            </button>
                        </div>
                    </form>
                </div>
            <?php endif; ?>
        </div>
    </div>
<?php endif; ?>
<?= $this->Html->script('views/Applicationforms/applicationform-comments', ['block' => true]) ?>
<?= $this->Html->script('views/Applicationforms/applicationform-cgr', ['block' => true]) ?>
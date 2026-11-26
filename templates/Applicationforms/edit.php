<?php
/**
 * Vue : Édition d'une demande de recrutement (Applicationforms)
 *
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Applicationform $applicationform
 * @var \Authorization\IdentityInterface $identity
 * @var array<int, string> $contracttypes
 * @var array<int, string> $hiringreasons
 * @var array<int, string> $professionalcategories
 * @var array<int, string> $worktimes
 * @var array<int, string> $periods
 * @var array<int, string> $budgetfeatures
 * @var array<int, string> $yesnos
 * @var array<int, string> $collaborators
 * @var array<int, string> $assistants
 * @var int $assistantDefaultId
 * @var list<\App\Model\Entity\User> $departmentManagers
 * @var array<string, string> $fieldSchema
 * @var bool $circuitLocked
 */

$this->assign('title', __('Demande de recrutement #{0}', $applicationform->id));

// Inclusion des assets CSS et JS requis
$this->Html->css('applicationforms', ['block' => true]);
$this->Html->css('/assets/treeselectjs/treeselectjs', ['block' => true]);
$this->Html->script('/assets/treeselectjs/treeselectjs.umd', ['block' => true]);
$this->Html->script('views/Applicationforms/applicationform-cgr', ['type' => 'module', 'block' => true]);
$this->Html->script('views/Applicationforms/applicationform-treeselect', ['type' => 'module', 'block' => true]);
$this->Html->script('views/Applicationforms/applicationform-comments', ['block' => true]);
$this->Html->script('views/Applicationforms/applicationform-candidate', ['block' => true]);
$this->Html->script('views/Applicationforms/salary-calculation', ['type' => 'module', 'block' => true]);
?>

<div class="container-fluid applicationform-form-page">
    <!-- Barre d'entête & Actions -->
    <div class="applicationform-form-header d-flex justify-content-between align-items-center mb-3 pb-2 border-bottom">
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

            <?= $this->Action->render(\App\View\Action\ApplicationformsActions::index(__('Retour à la liste'), 'btn btn-outline-secondary')) ?>
        </div>
    </div>

    <!-- Formulaire Principal -->
    <?= $this->Form->create($applicationform, ['id' => 'applicationform-main-form', 'class' => 'needs-validation applicationform-form']) ?>

    <?php if ($circuitLocked): ?>
        <p class="alert alert-info"><?= __('Le contrat et le département sont verrouillés tant qu’un cycle de validation existe.') ?></p>
    <?php endif; ?>

    <div class="row applicationform-form-grid">

        <!-- ZONE 1 : ADMINISTRATION -->
        <?php if ($identity->can('viewZoneAdmin', $applicationform)): ?>
            <section class="applicationform-form-zone" role="region" tabindex="0" aria-label="<?= h(__('Zone 1 : Administration et candidat')) ?>">
                <?= $this->element('Applicationforms/zone_admin', [
                    'applicationform' => $applicationform,
                    'canLoadCgrConfig' => $canLoadCgrConfig,
                    'collaborators' => $collaborators ?? [],
                    'assistants' => $assistants ?? [],
                    'assistantDefaultId' => $assistantDefaultId ?? 0,
                    'departmentManagers' => $departmentManagers ?? [],
                    'fieldSchema' => $fieldSchema ?? [],
                    'canEditAdmin' => $identity->can('editZoneAdmin', $applicationform),
                    'circuitLocked' => $circuitLocked,
                ]) ?>
            </section>
        <?php endif; ?>

        <!-- ZONE 2 : CARACTÉRISTIQUES DU CONTRAT -->
        <?php if ($identity->can('viewZoneContrat', $applicationform)): ?>
            <section class="applicationform-form-zone" role="region" tabindex="0" aria-label="<?= h(__('Zone 2 : Caractéristiques du contrat')) ?>">
                <?= $this->element('Applicationforms/zone_contract', [
                    'applicationform' => $applicationform,
                    'contracttypes' => $contracttypes ?? [],
                    'hiringreasons' => $hiringreasons ?? [],
                    'fieldSchema' => $fieldSchema ?? [],
                ]) ?>
            </section>
        <?php endif; ?>

        <!-- ZONE 3 : RÉMUNÉRATION & TEMPS DE TRAVAIL -->
        <?php if ($identity->can('viewZoneRemuneration', $applicationform)): ?>
            <section class="applicationform-form-zone" role="region" tabindex="0" aria-label="<?= h(__('Zone 3 : Temps de travail et rémunération')) ?>">
                <?= $this->element('Applicationforms/zone_remuneration', [
                    'applicationform' => $applicationform,
                    'professionalcategories' => $professionalcategories ?? [],
                    'worktimes' => $worktimes ?? [],
                    'periods' => $periods ?? [],
                    'fieldSchema' => $fieldSchema ?? [],
                    'canEditRemuneration' => $identity->can('editZoneRemuneration', $applicationform),
                ]) ?>
            </section>
        <?php endif; ?>

        <!-- ZONE 4 : RÉSERVÉS RH / ADMIN -->
        <?php if ($identity->can('viewZoneReserves', $applicationform)): ?>
            <section class="applicationform-form-zone" role="region" tabindex="0" aria-label="<?= h(__('Zone 4 : Champs réservés Administration et RH')) ?>">
                <?= $this->element('Applicationforms/zone_reserves', [
                    'applicationform' => $applicationform,
                    'budgetfeatures' => $budgetfeatures ?? [],
                    'yesnos' => $yesnos ?? [],
                    'fieldSchema' => $fieldSchema ?? [],
                    'canEditReserves' => $identity->can('editZoneReserves', $applicationform),
                ]) ?>
            </section>
        <?php endif; ?>

    </div>

    <!-- Actions bas de page -->
    <div class="applicationform-form-actions text-end">
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
<?= $this->element('Applicationforms/zone_comments', [
    'applicationform' => $applicationform,
    'identity' => $identity,
]) ?>

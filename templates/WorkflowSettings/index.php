<?php
declare(strict_types=1);

/**
 * @var \App\View\AppView $this
 */
use App\View\Action\WorkflowSettingsActions;
use App\Model\Entity\WorkflowSetting;
use App\Model\Entity\FieldAuthorization;
use App\Service\Security\FieldAuthorizationService;

/**
 * @var \Authorization\IdentityInterface|null $identity
 */
$canManageKCoefficient = $identity?->can('manageKCoefficient', new WorkflowSetting()) ?? false;
$canManageValidationSettings = $this->Action->isAllowed(WorkflowSettingsActions::saveDefaultDueHours());
$workflowSettingsFieldSchema = $identity !== null
    ? (new FieldAuthorizationService())->getFieldSchema($identity, 'WorkflowSettings')
    : [];
$canEditWorkflowSetting = static fn(string $field): bool => $canManageValidationSettings
    && ($workflowSettingsFieldSchema === []
        || ($workflowSettingsFieldSchema[$field] ?? FieldAuthorization::ACCESS_LEVEL_NONE)
            === FieldAuthorization::ACCESS_LEVEL_EDIT);
$this->assign('title', __('Paramétrage global du workflow'));
$this->Html->css('workflow-settings', ['block' => true]);
$this->Html->script('views/WorkflowSettings/index.js', ['type' => 'module', 'block' => 'scriptBottom']);
?>
<div class="content workflow-settings-page d-flex flex-column flex-grow-1 overflow-y-auto">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h3 class="mb-1"><?= __('Paramétrage global du workflow') ?></h3>
            <p class="text-muted mb-0"><?= __('Gérez les règles de validation, les commentaires proposés et les paramètres des DAE.') ?></p>
        </div>
    </div>

    <ul class="nav nav-tabs mb-3" id="workflow-settings-tabs" role="tablist" aria-label="<?= __('Paramétrage du workflow') ?>">
        <li class="nav-item" role="presentation">
            <button class="nav-link active" id="workflow-settings-validation-tab" data-bs-toggle="tab" data-bs-target="#workflow-settings-validation-panel" type="button" role="tab" aria-controls="workflow-settings-validation-panel" aria-selected="true">
                <?= __('Validation') ?>
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="workflow-settings-comments-tab" data-bs-toggle="tab" data-bs-target="#workflow-settings-comments-panel" type="button" role="tab" aria-controls="workflow-settings-comments-panel" aria-selected="false">
                <?= __('Commentaires') ?>
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" id="workflow-settings-applicationforms-tab" data-bs-toggle="tab" data-bs-target="#workflow-settings-applicationforms-panel" type="button" role="tab" aria-controls="workflow-settings-applicationforms-panel" aria-selected="false">
                <?= __('Paramètres des DAE') ?>
            </button>
        </li>
    </ul>

    <div class="tab-content" id="workflow-settings-tab-content">
        <section class="tab-pane fade show active" id="workflow-settings-validation-panel" role="tabpanel" aria-labelledby="workflow-settings-validation-tab" tabindex="0">
            <div class="d-flex justify-content-between align-items-start flex-wrap gap-2 mb-3">
                <div>
                    <h4 class="h5 mb-1"><?= __('Règles de validation') ?></h4>
                    <p class="text-muted mb-0"><?= __('Chaque valeur est enregistrée dès la validation de sa cellule.') ?></p>
                </div>
                <?= $this->Action->render(WorkflowSettingsActions::validationSequences()) ?>
            </div>
            <section class="card" aria-labelledby="workflow-validation-settings-title">
                <div class="card-body">
                    <h5 class="h6" id="workflow-validation-settings-title"><?= __('Paramètres des votes et délais') ?></h5>
                    <div
                        id="workflow-validation-settings-grid"
                        data-can-edit-default-due-hours="<?= $canEditWorkflowSetting('default_due_hours') ? 'true' : 'false' ?>"
                        data-can-edit-blocked-after-business-days="<?= $canEditWorkflowSetting('blocked_after_business_days') ? 'true' : 'false' ?>"
                        data-can-edit-comment-accept="<?= $canEditWorkflowSetting('accepter') ? 'true' : 'false' ?>"
                        data-can-edit-comment-reject="<?= $canEditWorkflowSetting('refuser') ? 'true' : 'false' ?>"
                    ></div>
                </div>
            </section>
        </section>

        <section class="tab-pane fade" id="workflow-settings-comments-panel" role="tabpanel" aria-labelledby="workflow-settings-comments-tab" tabindex="0">
            <section class="card" aria-labelledby="workflow-settings-comments-title">
                <div class="card-body">
                    <h4 class="h5" id="workflow-settings-comments-title"><?= __('Commentaires prédéfinis') ?></h4>
                    <p class="text-muted"><?= __('Créez, modifiez et ordonnez les commentaires proposés aux validateurs.') ?></p>
                    <form id="validation-comment-template-form" class="row g-2 mt-3 d-none">
                        <input type="hidden" name="id">
                        <div class="col-md-2"><select class="form-select" name="decision" aria-label="<?= __('Décision') ?>"><option value="accepter"><?= __('Acceptation') ?></option><option value="refuser"><?= __('Refus') ?></option></select></div>
                        <div class="col-md-3"><input class="form-control" name="label" required maxlength="120" placeholder="<?= __('Libellé') ?>" aria-label="<?= __('Libellé') ?>"></div>
                        <div class="col-md-3"><input class="form-control" name="content" required placeholder="<?= __('Commentaire') ?>" aria-label="<?= __('Commentaire') ?>"></div>
                        <div class="col-md-2"><input class="form-control" name="position" type="number" min="0" value="0" required placeholder="<?= __('Position') ?>" aria-label="<?= __('Position') ?>"></div>
                        <div class="col-md-1 form-check d-flex align-items-center"><input class="form-check-input" id="validation-comment-template-active" name="active" type="checkbox" checked><label class="form-check-label ms-1" for="validation-comment-template-active"><?= __('Actif') ?></label></div>
                        <div class="col-md-1"><?= $this->Action->render(WorkflowSettingsActions::saveCommentTemplate()) ?></div>
                        <div class="col-12"><button class="btn btn-link p-0" type="button" id="cancel-validation-comment-template"><?= __('Annuler') ?></button></div>
                    </form>
                    <?= $this->Tabulator->renderGrid('validation-comment-templates-grid', 'ValidationCommentTemplates') ?>
                </div>
            </section>
        </section>

        <section class="tab-pane fade" id="workflow-settings-applicationforms-panel" role="tabpanel" aria-labelledby="workflow-settings-applicationforms-tab" tabindex="0">
            <section class="card" aria-labelledby="workflow-applicationforms-settings-title">
                <div class="card-body">
                    <h4 class="h5" id="workflow-applicationforms-settings-title"><?= __('Conversion des montants des DAE') ?></h4>
                    <p class="text-muted"><?= __('Le coefficient k/mois sert à convertir les milliers mensuels.') ?></p>
                    <div id="workflow-applicationforms-settings-grid" data-can-manage="<?= $canManageKCoefficient ? 'true' : 'false' ?>"></div>
                </div>
            </section>
            <section class="card mt-3" aria-labelledby="workflow-duplication-settings-title">
                <div class="card-body">
                    <h4 class="h5" id="workflow-duplication-settings-title"><?= __('Paramètres de duplication des DAE') ?></h4>
                    <p class="text-muted mb-0"><?= __('Les choix de champs à réinitialiser lors d’une duplication seront regroupés dans cette section.') ?></p>
                </div>
            </section>
        </section>
    </div>
</div>

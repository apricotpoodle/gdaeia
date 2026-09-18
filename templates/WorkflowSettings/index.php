<?php
declare(strict_types=1);

use App\View\Action\WorkflowSettingsActions;

/**
 * @var \App\View\AppView $this
 */
$this->assign('title', __('Paramétrage global du workflow'));
$this->Html->script('views/WorkflowSettings/index.js', ['type' => 'module', 'block' => 'scriptBottom']);
?>
<div class="content">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h3 class="mb-1"><?= __('Paramétrage global du workflow') ?></h3>
            <p class="text-muted mb-0"><?= __('Définissez le délai par défaut et les commentaires proposés aux validateurs.') ?></p>
        </div>
        <?= $this->Action->render(WorkflowSettingsActions::validationSequences()) ?>
    </div>

    <section class="card mb-4">
        <div class="card-body">
            <h4 class="h6"><?= __('Délai global de validation') ?></h4>
            <div class="input-group">
                <input class="form-control" id="validation-default-due-hours" type="number" min="1" required aria-label="<?= __('Délai global de validation en heures') ?>">
                <?= $this->Action->render(WorkflowSettingsActions::saveDefaultDueHours()) ?>
            </div>
        </div>
    </section>

    <section class="card">
        <div class="card-body">
            <h4 class="h6"><?= __('Commentaires prédéfinis') ?></h4>
            <?= $this->Tabulator->renderGrid('validation-comment-templates-grid', 'ValidationCommentTemplates') ?>
            <form id="validation-comment-template-form" class="row g-2 mt-3 d-none">
                <input type="hidden" name="id">
                <div class="col-md-2"><select class="form-select" name="decision" aria-label="<?= __('Décision') ?>"><option value="accepter"><?= __('Acceptation') ?></option><option value="refuser"><?= __('Refus') ?></option></select></div>
                <div class="col-md-3"><input class="form-control" name="label" required maxlength="120" placeholder="<?= __('Libellé') ?>" aria-label="<?= __('Libellé') ?>"></div>
                <div class="col-md-3"><input class="form-control" name="content" required placeholder="<?= __('Commentaire') ?>" aria-label="<?= __('Commentaire') ?>"></div>
                <div class="col-md-2"><input class="form-control" name="position" type="number" min="0" value="0" required aria-label="<?= __('Position') ?>"></div>
                <div class="col-md-1 form-check d-flex align-items-center"><input class="form-check-input" id="validation-comment-template-active" name="active" type="checkbox" checked><label class="form-check-label ms-1" for="validation-comment-template-active"><?= __('Actif') ?></label></div>
                <div class="col-md-1"><?= $this->Action->render(WorkflowSettingsActions::saveCommentTemplate()) ?></div>
                <div class="col-12"><button class="btn btn-link p-0" type="button" id="cancel-validation-comment-template"><?= __('Annuler') ?></button></div>
            </form>
        </div>
    </section>
</div>

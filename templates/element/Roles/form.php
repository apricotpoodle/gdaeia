<?php
/** @var \App\View\AppView $this */
/** @var \App\Model\Entity\Role $role */
?>
<?= $this->Form->create($role, ['class' => 'needs-validation', 'novalidate' => true]) ?>
<div class="row">
    <div class="col-md-4 mb-3"><?= $this->Form->control('code', ['label' => __('Code'), 'class' => 'form-control', 'maxlength' => 16, 'required' => true]) ?></div>
    <div class="col-md-8 mb-3"><?= $this->Form->control('name', ['label' => __('Libellé'), 'class' => 'form-control', 'maxlength' => 64, 'required' => true]) ?></div>
</div>
<div class="row">
    <div class="col-md-6 mb-3"><?= $this->Form->control('sort', ['label' => __('Clé de tri'), 'class' => 'form-control', 'maxlength' => 64, 'required' => true]) ?></div>
</div>
<p class="text-muted small"><?= __('Les rôles créés depuis cette interface ne sont pas des rôles socles.') ?></p>
<div class="d-flex justify-content-end gap-2 mt-4 pt-3 border-top">
    <?= $this->Action->render(\App\View\Action\RolesActions::index(__('Annuler'), 'btn btn-secondary')) ?>
    <?= $this->Form->button('<i class="fa-solid fa-floppy-disk me-1"></i> ' . __('Enregistrer'), ['class' => 'btn btn-success', 'escapeTitle' => false]) ?>
</div>
<?= $this->Form->end() ?>

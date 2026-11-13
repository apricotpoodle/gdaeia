<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Role $role
 */
/** @var \App\Model\Entity\Role $role */
/** @var \Authorization\IdentityInterface|null $identity */
$operator = $identity?->getOriginalData();
$isSuperAdministrator = $operator instanceof \App\Model\Entity\User
    && (bool)$operator->get(\App\Model\Entity\User::FIELD_ISSUPERUSER);
$isBase = !$role->isNew() && (bool)$role->get('base');
?>
<?= $this->Form->create($role, ['class' => 'needs-validation', 'novalidate' => true]) ?>
<div class="row">
    <div class="col-md-4 mb-3"><?= $this->Form->control('code', ['label' => __('Code'), 'class' => 'form-control', 'maxlength' => 16, 'required' => true, 'readonly' => $isBase]) ?></div>
    <div class="col-md-8 mb-3"><?= $this->Form->control('name', ['label' => __('Libellé'), 'class' => 'form-control', 'maxlength' => 64, 'required' => true, 'readonly' => $isBase]) ?></div>
</div>
<div class="row">
    <div class="col-md-6 mb-3"><?= $this->Form->control('sort', ['label' => __('Clé de tri'), 'class' => 'form-control', 'maxlength' => 64, 'required' => true, 'readonly' => $isBase]) ?></div>
    <?php if ($isSuperAdministrator): ?>
        <div class="col-md-6 mb-3"><?= $this->Form->control('base', ['label' => __('Rôle socle'), 'type' => 'checkbox']) ?></div>
    <?php endif; ?>
</div>
<div class="d-flex justify-content-end gap-2 mt-4 pt-3 border-top">
    <?= $this->Action->render(\App\View\Action\RolesActions::index(__('Annuler'), 'btn btn-secondary')) ?>
    <?= $this->Form->button('<i class="fa-solid fa-floppy-disk me-1"></i> ' . __('Enregistrer'), ['class' => 'btn btn-success', 'escapeTitle' => false]) ?>
</div>
<?= $this->Form->end() ?>

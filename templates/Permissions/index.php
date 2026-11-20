<?php
declare(strict_types=1);

/**
 * @var \App\View\AppView $this
 * @var array<int, string> $roles
 */
$this->assign('title', __('Catalogue des permissions'));
$this->Html->script('views/Permissions/index.js', ['type' => 'module', 'block' => 'scriptBottom']);
?>
<div class="content flex-grow-1 overflow-y-auto">
    <h1 class="h3 mb-3"><?= __('Catalogue des permissions') ?></h1>
    <div class="row mb-3">
        <div class="col-md-6 col-xl-4">
            <label class="form-label" for="permissions-role"><?= __('Rôle') ?></label>
            <?= $this->Form->select('role_id', $roles, [
                'id' => 'permissions-role',
                'class' => 'form-select',
                'empty' => __('Sélectionner un rôle'),
                'autocomplete' => 'off',
            ]) ?>
        </div>
    </div>
    <div id="permissions-catalog" aria-label="<?= h(__('Permissions du rôle')) ?>" hidden></div>
</div>

<?php
/** @var \App\View\AppView $this */
$this->assign('title', __('Gestion des rôles'));
$this->Html->script('views/Roles/index.js', ['type' => 'module', 'block' => 'scriptBottom']);
?>
<div class="roles index content d-flex flex-column h-100">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1 class="h3 mb-0 text-gray-800"><?= __('Gestion des rôles') ?></h1>
        <div class="d-flex gap-2">
            <?= $this->Action->render(\App\View\Action\RolesActions::menuAccess()) ?>
            <?= $this->Action->render(\App\View\Action\RolesActions::add()) ?>
        </div>
    </div>
    <div class="flex-grow-1" style="min-height: 0;">
        <?= $this->Tabulator->renderGrid('#roles-table', 'Roles') ?>
    </div>
</div>

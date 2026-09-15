<?php
/**
 * @var \App\View\AppView $this
 */
$this->assign('title', __('Gestion des Menus'));
// Inclusion stricte du script JS dédié (Pattern d'isolation)
// $this->Html->script('views/Menus/index.js', ['block' => true]);
$this->Html->script('views/Menus/index.js', ['type' => 'module', 'block' => 'scriptBottom']);
?>
<div class="menus index content">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h3><?= __('Menus') ?></h3>
        <div class="d-flex gap-2">
            <?= $this->Action->render(\App\View\Action\MenusActions::roleAccess()) ?>
            <?= $this->Action->render(\App\View\Action\MenusActions::add()) ?>
        </div>
    </div>

    <!-- Injection via le composant métier existant (TabulatorHelper) -->
    <?= $this->Tabulator->renderGrid('#menus-grid', 'Menus') ?>
</div>

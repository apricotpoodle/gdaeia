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
        <?= $this->Html->link('<i class="fas fa-plus"></i> ' . __('Nouveau Menu'), ['action' => 'add'], ['escape' => false, 'class' => 'btn btn-primary']) ?>
    </div>

    <!-- Injection via le composant métier existant (TabulatorHelper) -->
    <?= $this->Tabulator->renderGrid('#menus-grid', 'Menus') ?>
</div>

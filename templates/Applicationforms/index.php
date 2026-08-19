<?php
/**
 * Vue pour l'index des Applicationforms
 * @var \App\View\AppView $this
 */

$this->Html->script('views/Applicationforms/index.js', ['type' => 'module', 'block' => true]);
?>

<div class="applicationforms index content h-100 d-flex flex-column">
    <div class="d-flex justify-content-between align-items-center mb-3 flex-shrink-0">
        <h3><?= __('Demandes de Recrutement') ?></h3>
    </div>

    <div class="flex-grow-1 overflow-hidden">
        <?= $this->Tabulator->renderGrid('#applicationforms-table', 'Applicationforms') ?>
    </div>
</div>

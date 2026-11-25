<?php

/**
 * Vue pour l'index des Applicationforms
 * @var \App\View\AppView $this
 * @var bool $archivePage
 */

$this->Html->script('views/Applicationforms/index.js?v=20261125160000', ['type' => 'module', 'block' => true]);
?>

<div class="applicationforms index content h-100 d-flex flex-column">
    <div class="d-flex justify-content-between align-items-center mb-3 flex-shrink-0">
        <h3><?= $archivePage ? __('Archives des demandes') : __('Demandes de Recrutement') ?></h3>
        <div class="d-flex gap-2">
            <?php if ($archivePage): ?>
                <?= $this->Action->render(\App\View\Action\ApplicationformsActions::index('Retour aux demandes')) ?>
                <?= $this->Action->render(\App\View\Action\ApplicationformsActions::restoreSelected()) ?>
            <?php else: ?>
                <?= $this->Action->render(\App\View\Action\ApplicationformsActions::archives()) ?>
                <?= $this->Action->render(\App\View\Action\ApplicationformsActions::archiveSelected()) ?>
                <?= $this->Action->render(\App\View\Action\ApplicationformsActions::add()) ?>
            <?php endif; ?>
        </div>
    </div>

    <?php if (!$archivePage): ?>
        <div class="form-check mb-2 flex-shrink-0">
            <input class="form-check-input" type="checkbox" id="include-archived">
            <label class="form-check-label" for="include-archived">
                <?= __('Inclure les demandes archivées dans la recherche') ?>
            </label>
        </div>
    <?php endif; ?>

    <div class="flex-grow-1 overflow-hidden">
        <?= $this->Tabulator->renderGrid('#applicationforms-table', 'Applicationforms') ?>
    </div>
</div>

<?php
/** @var \App\View\AppView $this */
$this->assign('title', __('Cycles bloqués'));
$this->Html->script('views/Applicationforms/blocked-validations.js', ['type' => 'module', 'block' => true]);
?>
<div class="applicationforms blocked-validations content h-100 d-flex flex-column">
    <div class="d-flex justify-content-between align-items-center mb-3 flex-shrink-0">
        <h3><?= __('Cycles bloqués') ?></h3>
    </div>
    <div class="flex-grow-1 overflow-hidden">
        <div id="blocked-validations-table"></div>
    </div>
</div>

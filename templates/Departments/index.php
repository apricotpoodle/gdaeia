<?php
declare(strict_types=1);

/** @var \App\View\AppView $this */
use App\View\Action\DepartmentsActions;

$this->assign('title', __('Départements'));
$this->Html->script('views/Departments/index.js', ['type' => 'module', 'block' => 'scriptBottom']);
?>
<div class="content">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <div>
            <h3 class="mb-1"><?= __('Départements') ?></h3>
            <p class="text-muted mb-0"><?= __('Gérez l’arbre organisationnel et les périmètres associés.') ?></p>
        </div>
        <?= $this->Action->render(DepartmentsActions::add()) ?>
    </div>
    <?= $this->Tabulator->renderGrid('departments-table', 'Departments') ?>
</div>

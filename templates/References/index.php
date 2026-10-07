<?php
declare(strict_types=1);

/**
 * @var \App\View\AppView $this
 * @var mixed $referenceAlias
 * @var mixed $referenceLabel
 */
use App\View\Action\ReferencesActions;

/** @var \App\View\AppView $this */
/** @var string $referenceAlias */
/** @var string $referenceLabel */
$this->assign('title', $referenceLabel);
$this->Html->script('views/References/index', ['type' => 'module', 'block' => 'scriptBottom']);
?>
<div class="references index content d-flex flex-column h-100">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1 class="h3 mb-0 text-gray-800"><?= h($referenceLabel) ?></h1>
        <?= $this->Action->render(ReferencesActions::add($referenceAlias)) ?>
    </div>
    <div class="flex-grow-1" style="min-height: 0;">
        <?= $this->Tabulator->renderGrid('references-grid', $referenceAlias) ?>
    </div>
</div>

<?php
declare(strict_types=1);

/**
 * @var \App\View\AppView $this
 * @var mixed $mode
 * @var mixed $reference
 * @var mixed $referenceAlias
 * @var mixed $referenceLabel
 */
use App\View\Action\ReferencesActions;

/** @var \App\View\AppView $this */
/** @var \Cake\Datasource\EntityInterface $reference */
/** @var string $referenceAlias */
/** @var string $referenceLabel */
/** @var string $mode */
$isEdit = $mode === 'edit';
$this->assign('title', $isEdit ? __('Modifier : {0}', $referenceLabel) : __('Nouvelle référence : {0}', $referenceLabel));
?>
<div class="references form content">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0"><?= h($this->fetch('title')) ?></h1>
        <?= $this->Action->render(ReferencesActions::index($referenceAlias)) ?>
    </div>
    <?= $this->Form->create($reference) ?>
    <div class="row g-3">
        <div class="col-md-4">
            <?= $this->Form->control('code', ['label' => __('Code'), 'maxlength' => 16, 'required' => true]) ?>
        </div>
        <div class="col-md-4">
            <?= $this->Form->control('name', ['label' => __('Libellé'), 'maxlength' => 32, 'required' => true]) ?>
        </div>
        <div class="col-md-4">
            <?= $this->Form->control('sort', ['label' => __('Clé de tri'), 'maxlength' => 32, 'required' => true]) ?>
        </div>
    </div>
    <div class="mt-4 d-flex gap-2">
        <?= $this->Form->button($isEdit ? __('Enregistrer') : __('Créer'), ['class' => 'btn btn-primary']) ?>
        <?= $this->Html->link(__('Annuler'), ['controller' => $referenceAlias, 'action' => 'index'], ['class' => 'btn btn-outline-secondary']) ?>
    </div>
    <?= $this->Form->end() ?>
</div>

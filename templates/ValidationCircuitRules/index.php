<?php
declare(strict_types=1);

/** @var \App\View\AppView $this */
$this->assign('title', __('Règles locales de circuit'));
?>
<div class="content flex-grow-1 overflow-y-auto">
    <h3><?= __('Règles locales de circuit') ?></h3>
    <p class="text-muted"><?= __('Une règle peut raccourcir le circuit pour un département et un type de contrat. Sans règle, le circuit complet est conservé.') ?></p>
    <form id="validation-circuit-rule-form" class="row g-2 mb-3">
        <div class="col-md-3"><input class="form-control" name="department_id" type="number" min="1" required placeholder="<?= __('Département') ?>"></div>
        <div class="col-md-3"><input class="form-control" name="contracttype_id" type="number" min="1" required placeholder="<?= __('Type de contrat') ?>"></div>
        <div class="col-md-3"><input class="form-control" name="final_role_id" type="number" min="1" required placeholder="<?= __('Rôle final') ?>"></div>
        <div class="col-md-3"><button class="btn btn-primary" type="submit"><?= __('Ajouter') ?></button></div>
    </form>
    <div id="validation-circuit-rules-grid"></div>
</div>
<?php $this->Html->script('views/ValidationCircuitRules/index.js', ['type' => 'module', 'block' => 'scriptBottom']); ?>

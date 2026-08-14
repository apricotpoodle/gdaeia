<?php
/**
 * @var \App\View\AppView $this
 */
?>
<div class="field-authorizations index content">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3><i class="fas fa-shield-alt text-secondary me-2"></i> <?= __('Autorisations des Champs (Field ACL)') ?></h3>
    </div>

    <!-- 1. Génération du conteneur sécurisé pour Tabulator via notre Helper -->
    <!-- Le sélecteur doit correspondre à celui attendu par TabulatorFactory -->
    <?= $this->Tabulator->renderGrid('fieldauthorizations-grid', 'FieldAuthorizations') ?>
</div>

<?php
// 2. Injection du script JS en tant que MODULE ES6
$this->Html->script('views/FieldAuthorizations/index', ['type' => 'module', 'block' => true]);
?>

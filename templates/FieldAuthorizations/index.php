<?php
/**
 * Vue Index pour FieldAuthorizations
 */
?>
<!-- Génération du conteneur HTML sécurisé pour Tabulator[cite: 3] -->
<?= $this->Tabulator->renderGrid('#fieldauthorizations-table', 'FieldAuthorizations') ?>

<!-- Chargement de l'orchestrateur JS en module ES6 (ADR 0030)[cite: 3] -->
<?php $this->Html->script('views/FieldAuthorizations/index.js', ['type' => 'module', 'block' => 'scriptBottom']); ?>
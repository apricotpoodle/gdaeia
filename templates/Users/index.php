<?php

/**
 * Vue principale pour la gestion des utilisateurs.
 * * @var \App\View\AppView $this
 * @author L'Équipe de Développement
 */

// Injection des librairies externes (Tabulator) et de notre script spécifique dans le bloc <head>
// 1. CSS
$this->Html->css('https://unpkg.com/tabulator-tables@5.5.0/dist/css/tabulator.min.css', ['block' => true]);
$this->Html->css('tabulator/custom-theme.css', ['block' => true]);

// 2. JS - Librairie externe
$this->Html->script('https://unpkg.com/tabulator-tables@5.5.0/dist/js/tabulator.min.js', ['block' => true]);

?>

<?= $this->Html->script('views/Users/index.js', ['type' => 'module']); ?>

<div class="d-flex flex-column h-100">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h1 class="h3 mb-0 text-gray-800">Gestion des Utilisateurs</h1>
    </div>

    <div class="flex-grow-1" style="min-height: 0;">
        <?= $this->Tabulator->renderGrid('#users-table', 'Users') ?>
    </div>
</div>

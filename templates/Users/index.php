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

<div class="d-flex flex-column" style="height: calc(100vh - 70px); min-height: 0;">

    <div class="view-header p-2 bg-white border-bottom flex-shrink-0 d-flex justify-content-between align-items-center">
        <h1 class="h5 mb-0 text-dark fw-bold">
            <i class="fas fa-users me-2 text-primary"></i>Gestion des Opérateurs
        </h1>
    </div>

    <div class="flex-grow-1 p-3 content-grid-wrapper" style="min-height: 0; overflow: hidden; background-color: #f8f9fa;">

        <div class="w-100 h-100">
            <?= $this->Tabulator->renderGrid('#users-table', 'Users') ?>
        </div>

    </div>
</div>

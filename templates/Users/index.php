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

// 3. JS - Notre architecture Core (L'ordre est strict !)
// $this->Html->script('core/Tabulator/TabulatorObserver.js', ['block' => true]);
// $this->Html->script('core/Tabulator/ButtonBuilder.js', ['block' => true]);
// $this->Html->script('core/Tabulator/ButtonFactory.js', ['block' => true]);
// $this->Html->script('core/Tabulator/TabulatorBuilder.js', ['block' => true]);
// $this->Html->script('core/Tabulator/TabulatorFactory.js', ['block' => true]);


$this->Html->script('views/Users/index.js', ['type' => 'module', 'block' => 'scriptBottom']);
?>
<?= $this->Tabulator->renderGrid('#users-table', 'Users') ?>

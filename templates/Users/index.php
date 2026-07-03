<?php
/**
 * Vue principale pour la gestion des utilisateurs.
 * * @var \App\View\AppView $this
 * @author L'Équipe de Développement
 */

// Injection des librairies externes (Tabulator) et de notre script spécifique dans le bloc <head>
$this->Html->css('https://unpkg.com/tabulator-tables@5.5.0/dist/css/tabulator.min.css', ['block' => true]);
$this->Html->script('https://unpkg.com/tabulator-tables@5.5.0/dist/js/tabulator.min.js', ['block' => true]);
$this->Html->script('core/Tabulator/TabulatorObserver.js', ['block' => true]);
$this->Html->script('core/Tabulator/TabulatorBuilder.js', ['block' => true]);
$this->Html->script('core/Tabulator/TabulatorFactory.js', ['block' => true]);
$this->Html->script('views/Users/index.js', ['block' => true]);
?>

<div class="users-container">
    <h2>Gestion des Utilisateurs</h2>
    <div id="users-table"></div>
</div>
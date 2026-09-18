<?php
declare(strict_types=1);

/**
 * @var \App\View\AppView $this
 */

$this->assign('title', __('Associer des départements à plusieurs utilisateurs'));
$this->Html->script('views/Users/bulk-departments.js', ['type' => 'module', 'block' => 'scriptBottom']);
?>
<div class="role-menu-access content h-100 d-flex flex-column">
    <div class="d-flex justify-content-between align-items-center mb-3 flex-shrink-0">
        <div>
            <h3 class="mb-1"><?= __('Départements et utilisateurs') ?></h3>
            <p class="text-muted mb-0"><?= __('Sélectionnez un ou plusieurs départements, puis double-cliquez un utilisateur pour l’attribuer. Double-cliquez un utilisateur associé pour le retirer.') ?></p>
        </div>
        <?= $this->Action->render(\App\View\Action\UsersActions::index()) ?>
    </div>

    <div class="row g-3 role-menu-access-tables flex-grow-1">
        <section class="col-lg-5 d-flex flex-column">
            <h4 class="h6"><?= __('Départements') ?></h4>
            <div id="bulk-departments-table" class="role-menu-access-table"></div>
        </section>
        <section class="col-lg-3 d-flex flex-column">
            <h4 class="h6"><?= __('Utilisateurs disponibles') ?></h4>
            <div id="bulk-available-users-table" class="role-menu-access-table bulk-departments-users-table"></div>
        </section>
        <section class="col-lg-4 d-flex flex-column">
            <h4 class="h6"><?= __('Utilisateurs associés à la sélection') ?></h4>
            <div id="bulk-selected-users-table" class="role-menu-access-table bulk-departments-users-table"></div>
        </section>
    </div>
</div>

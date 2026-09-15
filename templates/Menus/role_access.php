<?php
/**
 * @var \App\View\AppView $this
 */
$this->assign('title', __('Associer les options de menu aux rôles'));
$this->Html->script('views/Menus/role-access.js', ['type' => 'module', 'block' => 'scriptBottom']);
?>
<div class="role-menu-access content h-100 d-flex flex-column">
    <div class="d-flex justify-content-between align-items-center mb-3 flex-shrink-0">
        <div>
            <h3 class="mb-1"><?= __('Options de menu et rôles') ?></h3>
            <p class="text-muted mb-0"><?= __('Sélectionnez une ou plusieurs options, puis double-cliquez un rôle pour l’attribuer. Double-cliquez un rôle associé pour le retirer.') ?></p>
        </div>
        <?= $this->Action->render(\App\View\Action\MenusActions::index()) ?>
    </div>

    <div class="row g-3 role-menu-access-tables flex-grow-1">
        <section class="col-lg-5 d-flex flex-column">
            <h4 class="h6"><?= __('Options de menu') ?></h4>
            <div id="role-access-menus-table" class="role-menu-access-table"></div>
        </section>
        <section class="col-lg-3 d-flex flex-column">
            <h4 class="h6"><?= __('Rôles disponibles') ?></h4>
            <div id="role-access-available-roles-table" class="role-menu-access-table"></div>
        </section>
        <section class="col-lg-4 d-flex flex-column">
            <h4 class="h6"><?= __('Rôles associés à la sélection') ?></h4>
            <div id="role-access-selected-roles-table" class="role-menu-access-table"></div>
        </section>
    </div>
</div>

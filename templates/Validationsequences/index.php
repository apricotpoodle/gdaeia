<?php
declare(strict_types=1);

use App\View\Action\ValidationsequencesActions;

/**
 * @var \App\View\AppView $this
 */
$this->assign('title', __('Configurer les séquences de validation'));
$this->Html->script('views/Validationsequences/index.js', ['type' => 'module', 'block' => 'scriptBottom']);
?>
<div class="role-menu-access content h-100 d-flex flex-column">
    <div class="mb-3 flex-shrink-0 d-flex justify-content-between align-items-start">
        <div>
            <h3 class="mb-1"><?= __('Séquences de validation par département') ?></h3>
            <p class="text-muted mb-0"><?= __('Sélectionnez une unique racine de sous-arbre de départements, puis double-cliquez un rôle pour l’ajouter. Le numéro de séquence est modifiable manuellement dans la dernière table.') ?></p>
        </div>
        <?= $this->Action->render(ValidationsequencesActions::workflowSettings()) ?>
    </div>
    <div class="row g-3 role-menu-access-tables flex-grow-1">
        <section class="col-lg-5 d-flex flex-column">
            <h4 class="h6"><?= __('Départements') ?></h4>
            <div id="validation-sequences-departments-table" class="role-menu-access-table"></div>
        </section>
        <section class="col-lg-3 d-flex flex-column">
            <h4 class="h6"><?= __('Rôles validateurs disponibles') ?></h4>
            <div id="validation-sequences-available-roles-table" class="role-menu-access-table"></div>
        </section>
        <section class="col-lg-4 d-flex flex-column">
            <h4 class="h6"><?= __('Rôles associés à la sélection') ?></h4>
            <div id="validation-sequences-assigned-roles-table" class="role-menu-access-table"></div>
        </section>
    </div>
</div>

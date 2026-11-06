<?php
declare(strict_types=1);

use App\View\Action\ValidationCircuitRulesActions;

/**
 * @var \App\View\AppView $this
 * @var \Authorization\IdentityInterface|null $identity
 * @var bool $canManageGrants
 */
$this->assign('title', __('Circuits courts locaux'));
$this->Html->css('/assets/treeselectjs/treeselectjs.css', ['block' => true]);
$this->Html->script('views/ValidationCircuitRules/index.js', ['type' => 'module', 'block' => 'scriptBottom']);
?>
<div class="content flex-grow-1 overflow-y-auto">
    <h3><?= __('Circuits courts locaux par type de contrat') ?></h3>
    <p class="text-muted"><?= __('Une règle arrête le circuit après le rôle final choisi, en conservant tous les rôles de sa séquence. Sans règle, le circuit départemental complet s’applique.') ?></p>

    <section class="border rounded p-3 mb-4" aria-labelledby="validation-circuit-editor-title">
        <h4 class="h5" id="validation-circuit-editor-title"><?= __('Configurer une fin de circuit') ?></h4>
        <p id="validation-circuit-rule-mode" class="fw-semibold" aria-live="polite"><?= __('Création de règles pour les départements sélectionnés') ?></p>
        <form id="validation-circuit-rule-form">
            <fieldset id="validation-circuit-rule-fields" disabled>
                <div class="row g-3">
                    <div class="col-lg-5">
                        <label class="form-label" id="validation-circuit-departments-label"><?= __('Départements autorisés') ?></label>
                        <p class="small text-muted" id="validation-circuit-departments-help"><?= __('Cocher un parent sélectionne ses descendants présents. Décocher une branche l’exclut. Les futurs départements ne reçoivent aucune règle automatiquement.') ?></p>
                        <div id="validation-circuit-departments" aria-labelledby="validation-circuit-departments-label" aria-describedby="validation-circuit-departments-help"></div>
                        <p class="small text-muted mt-2" id="validation-circuit-selected-count" aria-live="polite"><?= __('Aucun département sélectionné.') ?></p>
                    </div>
                    <div class="col-lg-7">
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="validation-circuit-contracttype" class="form-label"><?= __('Type de contrat') ?></label>
                                <select id="validation-circuit-contracttype" name="contracttype_id" class="form-select" required></select>
                            </div>
                            <div class="col-md-6">
                                <label for="validation-circuit-final-role" class="form-label"><?= __('Rôle final') ?></label>
                                <select id="validation-circuit-final-role" name="final_role_id" class="form-select" required></select>
                            </div>
                        </div>
                        <p class="small text-muted mt-3"><?= __('Prévisualisez les étapes et les problèmes par département avant l’enregistrement. Un doublon ou un parcours invalide entraîne le refus de l’ensemble du lot.') ?></p>
                        <p class="small text-muted"><?= __('L’éligibilité du demandeur sera vérifiée au lancement de chaque DAE.') ?></p>
                        <div class="d-flex gap-2 flex-wrap">
                            <?= $this->Action->render(ValidationCircuitRulesActions::preview()) ?>
                            <?= $this->Action->render(ValidationCircuitRulesActions::saveRule()) ?>
                            <?= $this->Action->render(ValidationCircuitRulesActions::newRule()) ?>
                        </div>
                    </div>
                </div>
            </fieldset>
        </form>
        <div id="validation-circuit-preview" class="mt-3" aria-live="polite"></div>
    </section>

    <section class="mb-4" aria-labelledby="validation-circuit-rules-title">
        <h4 class="h5" id="validation-circuit-rules-title"><?= __('Règles enregistrées') ?></h4>
        <p class="small text-muted"><?= __('Chaque modification ou retrait porte sur une seule règle. Retirer une règle rétablit le circuit complet pour les prochains lancements ; les cycles déjà lancés conservent leurs étapes.') ?></p>
        <div id="validation-circuit-rules-grid" data-can-create="<?= $this->Action->isAllowed(ValidationCircuitRulesActions::saveRule()) ? 'true' : 'false' ?>"></div>
    </section>

    <?php if ($canManageGrants): ?>
        <section class="border rounded p-3 mb-3" aria-labelledby="validation-circuit-managers-title">
            <h4 class="h5" id="validation-circuit-managers-title"><?= __('Habilitations de gestion locale') ?></h4>
            <p class="text-muted"><?= __('Un rôle habilité peut gérer les règles uniquement dans les départements explicitement rattachés à ses utilisateurs. Ce droit est distinct du rôle final et ne donne aucun droit de vote supplémentaire.') ?></p>
            <form id="validation-circuit-manager-form" class="mb-3">
                <fieldset id="validation-circuit-manager-fields" disabled>
                    <div class="row g-3">
                        <div class="col-lg-5">
                            <label class="form-label" id="validation-circuit-manager-departments-label"><?= __('Départements à habiliter') ?></label>
                            <div id="validation-circuit-manager-departments" aria-labelledby="validation-circuit-manager-departments-label"></div>
                            <p id="validation-circuit-manager-selected-count" class="small text-muted mt-2" aria-live="polite"><?= __('Aucun département sélectionné.') ?></p>
                        </div>
                        <div class="col-lg-7">
                            <label for="validation-circuit-manager-role" class="form-label"><?= __('Rôle gestionnaire') ?></label>
                            <select id="validation-circuit-manager-role" name="role_id" class="form-select mb-3" required></select>
                            <?= $this->Action->render(ValidationCircuitRulesActions::assignManagers()) ?>
                        </div>
                    </div>
                </fieldset>
            </form>
            <div id="validation-circuit-managers-grid"></div>
        </section>
    <?php endif; ?>
</div>

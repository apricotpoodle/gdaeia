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

    <ul class="nav nav-tabs mb-3" id="validation-circuit-tabs" role="tablist" aria-label="<?= __('Administration des circuits courts') ?>">
        <li class="nav-item" role="presentation">
            <button class="nav-link active" id="validation-circuit-rules-tab" data-bs-toggle="tab" data-bs-target="#validation-circuit-rules-panel" type="button" role="tab" aria-controls="validation-circuit-rules-panel" aria-selected="true">
                <?= __('Règles locales') ?>
            </button>
        </li>
        <?php if ($canManageGrants): ?>
            <li class="nav-item" role="presentation">
                <button class="nav-link" id="validation-circuit-managers-tab" data-bs-toggle="tab" data-bs-target="#validation-circuit-managers-panel" type="button" role="tab" aria-controls="validation-circuit-managers-panel" aria-selected="false">
                    <?= __('Habilitations') ?>
                </button>
            </li>
        <?php endif; ?>
    </ul>

    <div class="tab-content" id="validation-circuit-tab-content">
        <div class="tab-pane fade show active" id="validation-circuit-rules-panel" role="tabpanel" aria-labelledby="validation-circuit-rules-tab" tabindex="0">
            <p class="text-muted"><?= __('Une règle arrête le circuit après le rôle final choisi, en conservant tous les rôles de sa séquence. Sans règle, le circuit départemental complet s’applique.') ?></p>

            <section id="validation-circuit-rules-card" class="border rounded p-3 mb-4" aria-label="<?= __('Configuration et règles locales') ?>">
                <div class="row g-3">
                    <div class="col-12 col-lg-6" id="validation-circuit-editor-column">
                        <h4 class="h5" id="validation-circuit-editor-title"><?= __('Configurer une fin de circuit') ?></h4>
                        <p id="validation-circuit-rule-mode" class="fw-semibold" aria-live="polite"><?= __('Création de règles pour les départements sélectionnés') ?></p>
                        <form id="validation-circuit-rule-form">
                            <fieldset id="validation-circuit-rule-fields" disabled>
                                <div class="row g-3">
                                    <div class="col-12" id="validation-circuit-departments-column">
                                        <label class="form-label" id="validation-circuit-departments-label"><?= __('Départements autorisés') ?></label>
                                        <p class="small text-muted" id="validation-circuit-departments-help"><?= __('Cocher un parent sélectionne ses descendants présents. Décocher une branche l’exclut. Les futurs départements ne reçoivent aucune règle automatiquement.') ?></p>
                                        <div id="validation-circuit-departments" aria-labelledby="validation-circuit-departments-label" aria-describedby="validation-circuit-departments-help"></div>
                                        <p class="small text-muted mt-2" id="validation-circuit-selected-count" aria-live="polite"><?= __('Aucun département sélectionné.') ?></p>
                                    </div>
                                    <div class="col-md-6" id="validation-circuit-contracttype-column">
                                        <label for="validation-circuit-contracttype" class="form-label"><?= __('Type de contrat') ?></label>
                                        <select id="validation-circuit-contracttype" name="contracttype_id" class="form-select" required></select>
                                    </div>
                                    <div class="col-md-6" id="validation-circuit-final-role-column">
                                        <label for="validation-circuit-final-role" class="form-label"><?= __('Rôle final') ?></label>
                                        <select id="validation-circuit-final-role" name="final_role_id" class="form-select" required></select>
                                    </div>
                                </div>
                                <div class="d-flex gap-2 flex-wrap mt-3">
                                    <?= $this->Action->render(ValidationCircuitRulesActions::preview()) ?>
                                    <?= $this->Action->render(ValidationCircuitRulesActions::saveRule()) ?>
                                    <?= $this->Action->render(ValidationCircuitRulesActions::newRule()) ?>
                                </div>
                            </fieldset>
                        </form>
                        <p class="small text-muted mt-3"><?= __('Prévisualisez les étapes et les problèmes par département avant l’enregistrement. Un doublon ou un parcours invalide entraîne le refus de l’ensemble du lot.') ?></p>
                        <p class="small text-muted"><?= __('L’éligibilité du demandeur sera vérifiée au lancement de chaque DAE.') ?></p>
                        <div id="validation-circuit-preview" class="mt-3" aria-live="polite"></div>
                    </div>
                    <div class="col-12 col-lg-6" id="validation-circuit-rules-grid-column">
                        <h4 class="h5" id="validation-circuit-rules-title"><?= __('Règles enregistrées') ?></h4>
                        <p class="small text-muted"><?= __('Chaque modification ou retrait porte sur une seule règle. Retirer une règle rétablit le circuit complet pour les prochains lancements ; les cycles déjà lancés conservent leurs étapes.') ?></p>
                        <div id="validation-circuit-rules-grid" data-can-create="<?= $this->Action->isAllowed(ValidationCircuitRulesActions::saveRule()) ? 'true' : 'false' ?>"></div>
                    </div>
                </div>
            </section>
        </div>

        <?php if ($canManageGrants): ?>
            <div class="tab-pane fade" id="validation-circuit-managers-panel" role="tabpanel" aria-labelledby="validation-circuit-managers-tab" tabindex="0">
                <section class="border rounded p-3 mb-3" aria-labelledby="validation-circuit-managers-title">
                    <h4 class="h5" id="validation-circuit-managers-title"><?= __('Habilitations de gestion locale') ?></h4>
                    <p class="text-muted"><?= __('Un rôle habilité peut gérer les règles uniquement dans les départements explicitement rattachés à ses utilisateurs. Ce droit est distinct du rôle final et ne donne aucun droit de vote supplémentaire.') ?></p>
                    <div class="row g-3">
                        <div class="col-12 col-lg-6" id="validation-circuit-manager-form-column">
                            <form id="validation-circuit-manager-form">
                                <fieldset id="validation-circuit-manager-fields" disabled>
                                    <div class="mb-3">
                                        <?= $this->Action->render(ValidationCircuitRulesActions::assignManagers()) ?>
                                    </div>
                                    <div class="mb-3">
                                        <label for="validation-circuit-manager-role" class="form-label"><?= __('Rôle gestionnaire') ?></label>
                                        <select id="validation-circuit-manager-role" name="role_id" class="form-select" required></select>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label" id="validation-circuit-manager-departments-label"><?= __('Départements à habiliter') ?></label>
                                        <div id="validation-circuit-manager-departments" aria-labelledby="validation-circuit-manager-departments-label"></div>
                                        <p id="validation-circuit-manager-selected-count" class="small text-muted mt-2" aria-live="polite"><?= __('Aucun département sélectionné.') ?></p>
                                    </div>
                                </fieldset>
                            </form>
                        </div>
                        <div class="col-12 col-lg-6" id="validation-circuit-managers-grid-column">
                            <h4 class="h5" id="validation-circuit-managers-grid-title"><?= __('Habilitations enregistrées') ?></h4>
                            <div id="validation-circuit-managers-grid"></div>
                        </div>
                    </div>
                </section>
            </div>
        <?php endif; ?>
    </div>
</div>

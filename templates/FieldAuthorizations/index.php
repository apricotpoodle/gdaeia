<?php
/**
 * @var \App\View\AppView $this
 */
$this->assign('title', __('Administration de la Sécurité des Champs'));
// 💡 On demande à CakePHP de charger la librairie Tabulator pour cette page
// $this->Html->css('tabulator.min', ['block' => true]); // ou le chemin exact de votre css
// $this->Html->script('tabulator.min', ['block' => true]); // ou le chemin exact de votre js
?>

<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h3 mb-0 text-gray-800"><?= __('Sécurité Granulaire des Champs (ACL)') ?></h1>
        <button id="btn-add-rule" class="btn btn-primary">
            <i class="fa-solid fa-plus me-2"></i><?= __('Nouvelle Règle') ?>
        </button>
    </div>

    <div class="card shadow mb-4">
        <div class="card-body p-0">
            <!-- 💡 L'identifiant '#fieldauthorizations-grid' et le modèle 'FieldAuthorizations' (PascalCase) -->
            <?= $this->Tabulator->renderGrid('#fieldauthorizations-grid', 'FieldAuthorizations') ?>
        </div>
    </div>
</div>

<div class="modal fade" id="ruleModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="ruleForm">
                <div class="modal-header">
                    <h5 class="modal-title" id="ruleModalLabel"><?= __('Règle d\'autorisation') ?></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="rule-id" name="id">
                    
                    <div class="mb-3">
                        <label class="form-label"><?= __('Rôle') ?></label>
                        <select class="form-select" id="rule-role-id" name="role_id" required></select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label"><?= __('Ressource (Table)') ?></label>
                        <select class="form-select" id="rule-resource" name="resource" required></select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label"><?= __('Champ (Colonne)') ?></label>
                        <select class="form-select" id="rule-field" name="field" required></select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label"><?= __('Niveau d\'Accès') ?></label>
                        <select class="form-select" id="rule-access-level" name="access_level" required></select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><?= __('Annuler') ?></button>
                    <button type="submit" class="btn btn-primary"><?= __('Enregistrer') ?></button>
                </div>
            </form>
        </div>
    </div>
</div>

<?= $this->Html->script('views/FieldAuthorizations/index.js', ['type' => 'module', 'block' => 'scriptBottom']) ?>
<?php
/**
 * @var \App\View\AppView $this
 */
$this->assign('title', __('Administration de la Sécurité des Champs'));
?>

<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 text-gray-800"><?= __('Sécurité Granulaire des Champs (ACL)') ?></h1>
            <p class="text-muted mb-0"><?= __('Gérez les niveaux d\'accès (Édition, Lecture seule, Masqué) par rôle et par champ.') ?></p>
        </div>
        <button id="btn-add-rule" class="btn btn-primary">
            <i class="fa-solid fa-plus me-2"></i><?= __('Nouvelle Règle') ?>
        </button>
    </div>

    <!-- Conteneur de la Grille Tabulator -->
    <div class="card shadow mb-4">
        <div class="card-body p-0">
            <?= $this->Tabulator->renderGrid('#field-authorizations-table', 'FieldAuthorizations') ?>
        </div>
    </div>
</div>

<!-- Modale de Création / Édition de Règle -->
<div class="modal fade" id="ruleModal" tabindex="-1" aria-labelledby="ruleModalLabel" aria-hidden="true">
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
                        <label for="rule-role-id" class="form-label"><?= __('Rôle') ?></label>
                        <select class="form-select" id="rule-role-id" name="role_id" required></select>
                    </div>

                    <div class="mb-3">
                        <label for="rule-resource" class="form-label"><?= __('Ressource (Table)') ?></label>
                        <select class="form-select" id="rule-resource" name="resource" required></select>
                    </div>

                    <div class="mb-3">
                        <label for="rule-field" class="form-label"><?= __('Champ (Colonne)') ?></label>
                        <select class="form-select" id="rule-field" name="field" required></select>
                    </div>

                    <div class="mb-3">
                        <label for="rule-access-level" class="form-label"><?= __('Niveau d\'Accès') ?></label>
                        <select class="form-select" id="rule-access-level" name="access_level" required></select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><?= __('Annuler') ?></button>
                    <button type="submit" class="btn btn-primary" id="btn-save-rule"><?= __('Enregistrer') ?></button>
                </div>
            </form>
        </div>
    </div>
</div>

<?= $this->Html->script('views/FieldAuthorizations/index.js', ['type' => 'module', 'block' => 'scriptBottom']) ?>
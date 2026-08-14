
<?php
/**
 * @var \App\View\AppView $this
 */
?>
<div class="field-authorizations form content">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3><?= __('Ajouter une Règle d\'Autorisation') ?></h3>
        <?= $this->Html->link(__('Retour à la liste'), ['action' => 'index'], ['class' => 'btn btn-outline-secondary']) ?>
    </div>

    <div id="fieldauth-create-form-container">
        <!-- Le formulaire sera piloté par webroot/js/views/FieldAuthorizations/create.js -->
        <p class="text-muted">Formulaire de création en cours d'intégration...</p>
    </div>
</div>

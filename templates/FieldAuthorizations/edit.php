
<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\FieldAuthorization $fieldAuthorization
 */
?>
<div class="field-authorizations form content">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3><?= __('Éditer la Règle') ?> #<?= $fieldAuthorization->id ?></h3>
        <?= $this->Html->link(__('Retour à la liste'), ['action' => 'index'], ['class' => 'btn btn-outline-secondary']) ?>
    </div>

    <div id="fieldauth-edit-form-container">
        <!-- Le formulaire sera piloté par webroot/js/views/FieldAuthorizations/edit.js -->
        <p class="text-muted">Formulaire d'édition en cours d'intégration...</p>
    </div>
</div>

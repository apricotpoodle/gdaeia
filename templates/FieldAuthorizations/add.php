<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\FieldAuthorization $fieldAuthorization
 * @var array<int|string, string> $roles
 */

$this->assign('title', __('Ajouter une règle d’autorisation'));
?>
<div class="row">
    <div class="col-md-8">
        <div class="card shadow-sm">
            <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                <h3 class="card-title mb-0"><i class="fa-solid fa-shield-halved me-2"></i><?= h($this->fetch('title')) ?></h3>
                <?= $this->Action->render(\App\View\Action\FieldAuthorizationsActions::index()) ?>
            </div>
            <div class="card-body">
                <?= $this->Form->create($fieldAuthorization, ['id' => 'field-authorization-form', 'novalidate' => true, 'data-schema-url' => '/api/field-authorizations/get-form-schema.json']) ?>
                <?= $this->Form->control('role_id', [
                    'label' => __('Rôle applicatif'),
                    'options' => $roles,
                    'empty' => __('-- Sélectionner un rôle --'),
                    'class' => 'form-select mb-3',
                ]) ?>
                <?= $this->Form->control('target_type', ['label' => __('Type de cible'), 'options' => ['field' => __('Champ ORM'), 'section' => __('Zone fonctionnelle')], 'empty' => __('-- Sélectionner un type --'), 'class' => 'form-select mb-3']) ?>
                <?= $this->Form->control('resource', ['label' => __('Ressource'), 'options' => [], 'empty' => __('Chargement…'), 'class' => 'form-select mb-3', 'data-target-resource' => true]) ?>
                <?= $this->Form->control('target', ['label' => __('Cible'), 'options' => [], 'empty' => __('Sélectionnez d’abord le type et la ressource'), 'class' => 'form-select mb-3', 'data-target-select' => true]) ?>
                <p class="form-text" data-target-description></p>
                <?= $this->Form->control('access_level', [
                    'label' => __('Niveau d’accès'),
                    'options' => ['EDIT' => __('Modification'), 'VIEW' => __('Lecture seule'), 'NONE' => __('Aucun accès')],
                    'default' => 'EDIT',
                    'class' => 'form-select mb-4',
                ]) ?>
                <div class="d-flex justify-content-end gap-2">
                    <?= $this->Action->render(\App\View\Action\FieldAuthorizationsActions::index(__('Annuler'), 'btn btn-secondary')) ?>
                    <?= $this->Form->button(__('Enregistrer'), ['type' => 'submit', 'class' => 'btn btn-success']) ?>
                </div>
                <?= $this->Form->end() ?>
            </div>
        </div>
    </div>
</div>

<?php $this->Html->script('views/FieldAuthorizations/create', ['type' => 'module', 'block' => 'scriptBottom']); ?>

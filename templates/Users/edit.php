<?php
declare(strict_types=1);

/**
 * @file templates/Users/edit.php
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\User $user
 * @var array $roles
 * @var array $fieldSchema
 * @var array $departmentsTree
 * @var array $selectedDepartmentIds
 */

$this->assign('title', __('Éditer l\'Utilisateur #{0}', $user->id));

// 🚀 CHARGEMENT DES ASSETS TREESELECTJS ET DU SCRIPT DE VUE
$this->Html->css('vendor/treeselect/treeselectjs.css', ['block' => true]);
$this->Html->script('vendor/treeselect/treeselectjs.umd.js', ['block' => 'scriptBottom']);
$this->Html->script('views/Users/user-departments-tree.js', ['block' => 'scriptBottom']);
?>

<div class="row">
    <div class="col-md-12">
        <div class="card shadow-sm mb-4">
            <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                <h3 class="card-title mb-0">
                    <i class="fa-solid fa-user-pen me-2"></i>
                    <?= h($this->fetch('title')) ?>
                </h3>
                <div>
                    <?= $this->Html->link(
                        '<i class="fa-solid fa-arrow-left me-1"></i> ' . __('Retour à la liste'),
                        ['action' => 'index'],
                        ['class' => 'btn btn-light btn-sm', 'escape' => false]
                    ) ?>
                </div>
            </div>

            <div class="card-body">
                <?= $this->Form->create($user, [
                    'id' => 'user-form',
                    'class' => 'needs-validation',
                    'novalidate' => true,
                ]) ?>

                <fieldset>
                    <legend class="text-secondary border-bottom pb-2 mb-3">
                        <i class="fa-solid fa-id-card me-1"></i> <?= __('Informations Générales') ?>
                    </legend>

                    <div class="row">
                        <?php if (($fieldSchema['firstname'] ?? 'EDIT') !== 'HIDE'): ?>
                            <div class="col-md-6 mb-3">
                                <?= $this->Form->control('firstname', [
                                    'label' => __('Prénom'),
                                    'class' => 'form-control',
                                    'disabled' => ($fieldSchema['firstname'] ?? 'EDIT') === 'READ',
                                ]) ?>
                            </div>
                        <?php endif; ?>

                        <?php if (($fieldSchema['lastname'] ?? 'EDIT') !== 'HIDE'): ?>
                            <div class="col-md-6 mb-3">
                                <?= $this->Form->control('lastname', [
                                    'label' => __('Nom'),
                                    'class' => 'form-control',
                                    'disabled' => ($fieldSchema['lastname'] ?? 'EDIT') === 'READ',
                                ]) ?>
                            </div>
                        <?php endif; ?>
                    </div>

                    <div class="row">
                        <?php if (($fieldSchema['email'] ?? 'EDIT') !== 'HIDE'): ?>
                            <div class="col-md-6 mb-3">
                                <?= $this->Form->control('email', [
                                    'label' => __('Adresse Courriel'),
                                    'type' => 'email',
                                    'class' => 'form-control',
                                    'required' => true,
                                    'autocomplete' => 'username',
                                    'disabled' => ($fieldSchema['email'] ?? 'EDIT') === 'READ',
                                ]) ?>
                            </div>
                        <?php endif; ?>

                        <?php if (($fieldSchema['role_id'] ?? 'EDIT') !== 'HIDE'): ?>
                            <div class="col-md-6 mb-3">
                                <?= $this->Form->control('role_id', [
                                    'id' => 'role-id',
                                    'label' => __('Rôle Applicatif'),
                                    'options' => $roles ?? [],
                                    'empty' => __('-- Sélectionner un rôle --'),
                                    'class' => 'form-select',
                                    'disabled' => ($fieldSchema['role_id'] ?? 'EDIT') === 'READ',
                                ]) ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </fieldset>

                <fieldset class="mt-4">
                    <legend class="text-secondary border-bottom pb-2 mb-3">
                        <i class="fa-solid fa-sitemap me-1"></i> <?= __('Périmètre d\'Accès Organisationnel') ?>
                    </legend>

                    <?= $this->element('Users/department_select', [
                        'fieldSchema' => $fieldSchema ?? [],
                        'departmentsTree' => $departmentsTree ?? [],
                        'selectedDepartmentIds' => $selectedDepartmentIds ?? [],
                    ]) ?>
                </fieldset>

                <div class="d-flex justify-content-end gap-2 mt-4 pt-3 border-top">
                    <?= $this->Html->link(
                        '<i class="fa-solid fa-xmark me-1"></i> ' . __('Annuler'),
                        ['action' => 'index'],
                        ['class' => 'btn btn-secondary', 'escape' => false]
                    ) ?>
                    <?= $this->Form->button(
                        '<i class="fa-solid fa-floppy-disk me-1"></i> ' . __('Enregistrer'),
                        ['type' => 'submit', 'class' => 'btn btn-success', 'escapeTitle' => false]
                    ) ?>
                </div>

                <?= $this->Form->end() ?>
            </div>
        </div>
    </div>
</div>

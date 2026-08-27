<?php
/**
 * @var \App\Model\Entity\User $user
 * @var \App\Model\Entity\User $identity
 * @var bool $isImpersonating
 * @var array $fieldSchema
 * @var array $departmentsTree
 * @var array $selectedDepartmentIds
 */

$this->assign('title', __('Profil Utilisateur #{0}', $user->id));

// Ingestion des assets TreeselectJS
$this->Html->css('vendor/treeselect/treeselectjs.css', ['block' => true]);
$this->Html->script('vendor/treeselect/treeselectjs.umd.js', ['block' => 'scriptBottom']);
$this->Html->script('views/Users/user-departments-tree.js', ['block' => 'scriptBottom']);
?>

<div class="row">
    <div class="col-md-12">
        <div class="card shadow-sm mb-4">
            <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                <h3 class="card-title mb-0">
                    <i class="fa-solid fa-user me-2"></i>
                    <?= h($user->display_name) ?>
                </h3>
                <div class="d-flex gap-2">
                    <!-- 1. Bouton Impersonate -->
                    <?php if (!$isImpersonating && $identity->can('impersonate', $user)): ?>
                        <?= $this->Html->link(
                            '<i class="fa-solid fa-user-secret me-1"></i> ' . __('Incarner'),
                            ['action' => 'impersonate', $user->id],
                            [
                                'class' => 'btn btn-warning btn-sm shadow-sm',
                                'escape' => false,
                                'confirm' => __('Voulez-vous vraiment vous connecter sous l\'identité de {0} ?', $user->display_name),
                            ]
                        ) ?>
                    <?php endif; ?>

                    <!-- 2. Bouton Éditer -->
                    <?php if ($identity->can('edit', $user)): ?>
                        <?= $this->Html->link(
                            '<i class="fa-solid fa-pen-to-square me-1"></i> ' . __('Éditer'),
                            ['action' => 'edit', $user->id],
                            ['class' => 'btn btn-light btn-sm shadow-sm', 'escape' => false]
                        ) ?>
                    <?php endif; ?>

                    <!-- 3. Bouton Retour à la liste -->
                    <?= $this->Html->link(
                        '<i class="fa-solid fa-arrow-left me-1"></i> ' . __('Retour à la liste'),
                        ['action' => 'index'],
                        ['class' => 'btn btn-outline-light btn-sm shadow-sm', 'escape' => false]
                    ) ?>
                </div>
            </div>

            <div class="card-body">
                <fieldset class="mb-4">
                    <legend class="text-secondary border-bottom pb-2 mb-3">
                        <i class="fa-solid fa-id-card me-1"></i> <?= __('Informations Générales') ?>
                    </legend>

                    <div class="row g-3">
                        <div class="col-md-4">
                            <strong><?= __('Prénom') ?> :</strong>
                            <p class="text-muted mb-0"><?= h($user->firstname ?? '-') ?></p>
                        </div>

                        <div class="col-md-4">
                            <strong><?= __('Nom') ?> :</strong>
                            <p class="text-muted mb-0"><?= h($user->lastname ?? '-') ?></p>
                        </div>

                        <div class="col-md-4">
                            <strong><?= __('Adresse Courriel') ?> :</strong>
                            <p class="text-muted mb-0"><?= h($user->email) ?></p>
                        </div>

                        <div class="col-md-4">
                            <strong><?= __('Nom d\'utilisateur') ?> :</strong>
                            <p class="text-muted mb-0"><?= h($user->username ?? '-') ?></p>
                        </div>

                        <div class="col-md-4">
                            <strong><?= __('Rôle Applicatif') ?> :</strong>
                            <p class="text-muted mb-0"><?= h($user->role->name ?? '-') ?></p>
                        </div>

                        <!-- 4. Affichage conditionnel du badge Superutilisateur -->
                        <div class="col-md-4">
                            <strong><?= __('Statut privilège') ?> :</strong>
                            <div>
                                <?php if ($user->issuperuser): ?>
                                    <span class="badge bg-danger fs-6">
                                        <i class="fa-solid fa-shield-halved me-1"></i> <?= __('Superutilisateur') ?>
                                    </span>
                                <?php else: ?>
                                    <span class="badge bg-secondary fs-6">
                                        <i class="fa-solid fa-user me-1"></i> <?= __('Utilisateur Standard') ?>
                                    </span>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </fieldset>

                <!-- 5. Périmètre des départements en lecture seule via TreeselectJS -->
                <fieldset>
                    <legend class="text-secondary border-bottom pb-2 mb-3">
                        <i class="fa-solid fa-sitemap me-1"></i> <?= __('Périmètre d\'Accès Organisationnel') ?>
                    </legend>

                    <?= $this->element('Users/department_select', [
                        'fieldSchema' => array_merge($fieldSchema ?? [], ['user_departments' => 'READ']),
                        'departmentsTree' => $departmentsTree ?? [],
                        'selectedDepartmentIds' => $selectedDepartmentIds ?? [],
                    ]) ?>
                </fieldset>
            </div>
        </div>
    </div>
</div>

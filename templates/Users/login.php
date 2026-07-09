<?php

/**
 * @var \App\View\AppView $this
 */
?>
<div class="container d-flex align-items-center justify-content-center" style="min-height: 85vh;">
    <div class="card shadow-lg border-0 rounded-3" style="width: 100%; max-width: 450px;">
        <div class="card-body p-5">

            <div class="text-center mb-4">
                <div class="d-inline-flex align-items-center justify-content-center bg-primary bg-opacity-10 text-primary rounded-circle p-4 mb-3" style="width: 80px; height: 80px;">
                    <i class="fas fa-shield-check fa-3x"></i>
                </div>
                <h2 class="fw-bold h4 text-dark mb-1">Portail d'Infrastructures</h2>
                <p class="text-muted small">Système de validation et gestion des flux</p>
            </div>

            <?= $this->Flash->render() ?>

            <?= $this->Form->create(null, ['class' => 'needs-validation']) ?>

            <div class="mb-3">
                <?= $this->Form->label('email', __('Adresse Email'), ['class' => 'form-label small fw-bold text-muted']) ?>
                <div class="input-group">
                    <span class="input-group-text bg-light border-end-0 text-muted"><i class="fas fa-envelope fa-fw"></i></span>
                    <?= $this->Form->email('email', [
                        'required' => true,
                        'class' => 'form-control bg-light border-start-0 shadow-none',
                        'placeholder' => 'nom@entreprise.com'
                    ]) ?>
                </div>
            </div>

            <div class="mb-3">
                <?= $this->Form->label('password', __('Mot de passe'), ['class' => 'form-label small fw-bold text-muted']) ?>
                <div class="input-group">
                    <span class="input-group-text bg-light border-end-0 text-muted"><i class="fas fa-lock fa-fw"></i></span>
                    <?= $this->Form->password('password', [
                        'required' => true,
                        'class' => 'form-control bg-light border-start-0 shadow-none',
                        'placeholder' => '••••••••'
                    ]) ?>
                </div>
            </div>

            <div class="d-flex align-items-center justify-content-between mb-4">
                <div class="form-check m-0">
                    <?= $this->Form->checkbox('remember_me', [
                        'id' => 'remember-me',
                        'class' => 'form-check-input shadow-none'
                    ]) ?>
                    <?= $this->Form->label('remember_me', __('Se souvenir de moi'), [
                        'for' => 'remember-me',
                        'class' => 'form-check-label small text-muted cursor-pointer'
                    ]) ?>
                </div>
                <div>
                    <?= $this->Html->link(
                        __('Mot de passe oublié ?'),
                        ['controller' => 'Users', 'action' => 'forgotPassword'],
                        ['class' => 'small text-primary text-decoration-none fw-semibold']
                    ) ?>
                </div>
            </div>

            <div class="d-grid">
                <?= $this->Form->button(
                    __('<i class="fas fa-sign-in-alt me-2"></i> Connexion sécurisée'),
                    ['class' => 'btn btn-primary btn-lg shadow-sm fw-bold', 'escapeTitle' => false]
                ) ?>
            </div>

            <?= $this->Form->end() ?>

        </div>
    </div>
</div>

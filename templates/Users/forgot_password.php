<?php

/**
 * @var \App\View\AppView $this
 */
?>
<div class="container d-flex align-items-center justify-content-center" style="min-height: 85vh;">
    <div class="card shadow-lg border-0 rounded-3 w-100" style="max-width: 440px;">
        <div class="card-body p-5">

            <div class="text-center mb-4">
                <div class="d-inline-flex align-items-center justify-content-center bg-warning bg-opacity-10 text-warning rounded-circle p-3 mb-3" style="width: 72px; height: 72px;">
                    <i class="fas fa-user-lock fa-2x"></i>
                </div>
                <h2 class="fw-bold h4 text-dark mb-1"><?= __('Récupération de compte') ?></h2>
                <p class="text-muted small mb-0"><?= __('Saisissez votre email pour recevoir un lien de réinitialisation') ?></p>
            </div>

            <?= $this->Flash->render() ?>

            <?= $this->Form->create() ?>

            <div class="mb-4">
                <?= $this->Form->label('email', __('Adresse Email Professionnelle'), ['class' => 'form-label small fw-bold text-muted']) ?>
                <div class="input-group">
                    <span class="input-group-text bg-light text-muted border-end-0"><i class="fas fa-envelope fa-fw"></i></span>
                    <?= $this->Form->email('email', [
                        'required' => true,
                        'class' => 'form-control bg-light border-start-0 shadow-none',
                        'placeholder' => 'username@entreprise.com',
                        'autofocus' => true
                    ]) ?>
                </div>
            </div>

            <div class="d-grid mb-3">
                <?= $this->Form->button(
                    __('<i class="fas fa-paper-plane me-2"></i> Envoyer le lien de récupération'),
                    ['class' => 'btn btn-primary btn-lg shadow-sm fw-bold', 'escapeTitle' => false]
                ) ?>
            </div>

            <div class="text-center">
                <?= $this->Html->link(
                    __('<i class="fas fa-arrow-left me-1"></i> Retour à la connexion'),
                    ['action' => 'login'],
                    ['class' => 'small text-muted text-decoration-none fw-semibold', 'escape' => false]
                ) ?>
            </div>

            <?= $this->Form->end() ?>

        </div>
    </div>
</div>

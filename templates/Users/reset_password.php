<?php

/**
 * @var \App\View\AppView $this
 * @var string $token
 */
?>
<div class="container d-flex align-items-center justify-content-center" style="min-height: 85vh;">
    <div class="card shadow-lg border-0 rounded-3 w-100" style="max-width: 440px;">
        <div class="card-body p-5">

            <div class="text-center mb-4">
                <div class="d-inline-flex align-items-center justify-content-center bg-success bg-opacity-10 text-success rounded-circle p-3 mb-3" style="width: 72px; height: 72px;">
                    <i class="fas fa-key fa-2x"></i>
                </div>
                <h2 class="fw-bold h4 text-dark mb-1"><?= __('Nouveau mot de passe') ?></h2>
                <p class="text-muted small mb-0"><?= __('Saisissez et confirmez votre nouveau mot de passe de sécurité') ?></p>
            </div>

            <?= $this->Flash->render() ?>

            <?= $this->Form->create() ?>

            <div class="mb-3">
                <?= $this->Form->label('password', __('Nouveau mot de passe'), ['class' => 'form-label small fw-bold text-muted']) ?>
                <div class="input-group">
                    <span class="input-group-text bg-light text-muted border-end-0"><i class="fas fa-lock fa-fw"></i></span>
                    <?= $this->Form->password('password', [
                        'required' => true,
                        'class' => 'form-control bg-light border-start-0 shadow-none',
                        'placeholder' => '••••••••',
                        'autofocus' => true
                    ]) ?>
                </div>
            </div>

            <div class="d-grid">
                <?= $this->Form->button(
                    __('<i class="fas fa-check-circle me-2"></i> Mettre à jour le mot de passe'),
                    ['class' => 'btn btn-success btn-lg shadow-sm fw-bold', 'escapeTitle' => false]
                ) ?>
            </div>

            <?= $this->Form->end() ?>

        </div>
    </div>
</div>

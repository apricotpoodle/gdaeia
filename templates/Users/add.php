<?php

/**
 * @var \App\View\AppView $this
 */
?>
<div class="d-flex flex-column h-100">
    <div class="mb-4">
        <a href="<?= $this->Url->build(['action' => 'index']) ?>" class="btn btn-sm btn-secondary shadow-sm">
            <i class="fas fa-arrow-left me-1"></i> Retour à la liste
        </a>
    </div>

    <div class="card shadow-sm col-md-8 mx-auto">
        <div class="card-header bg-primary text-white">
            <h1 class="h5 mb-0"><i class="fas fa-user-plus me-2"></i> Créer un nouvel utilisateur</h1>
        </div>
        <div class="card-body bg-light">
            <?= $this->Form->create(null, ['id' => 'user-create-form', 'class' => 'needs-validation']) ?>

            <div class="row">
                <div class="col-md-6 form-group-wrapper mb-3">
                    <label for="firstname" class="form-label fw-bold">Prénom</label>
                    <input type="text" name="firstname" id="firstname" class="form-control" required>
                </div>

                <div class="col-md-6 form-group-wrapper mb-3">
                    <label for="lastname" class="form-label fw-bold">Nom</label>
                    <input type="text" name="lastname" id="lastname" class="form-control" required>
                </div>
            </div>

            <div class="form-group-wrapper mb-3">
                <label for="username" class="form-label fw-bold">Identifiant (Username)</label>
                <input type="text" name="username" id="username" class="form-control" autocomplete="off" required>
            </div>

            <div class="form-group-wrapper mb-3">
                <label for="email" class="form-label fw-bold">Adresse Email</label>
                <input type="email" name="email" id="email" class="form-control" required>
            </div>

            <div class="form-group-wrapper mb-3">
                <label for="password" class="form-label fw-bold">Mot de passe</label>
                <input type="password" name="password" id="password" class="form-control" autocomplete="new-password" required>
            </div>

            <div class="row">
                <div class="col-md-6 form-group-wrapper mb-3" id="wrapper-role_id">
                    <label for="role-id" class="form-label fw-bold">Rôle Applicatif</label>
                    <select name="role_id" id="role-id" class="form-select">
                    </select>
                </div>

                <div class="col-md-6 form-group-wrapper mb-3 d-flex align-items-end" id="wrapper-issuperuser">
                    <div class="form-check mb-2">
                        <input type="hidden" name="issuperuser" value="0">
                        <input type="checkbox" name="issuperuser" value="1" id="issuperuser" class="form-check-input">
                        <label for="issuperuser" class="form-check-label fw-bold text-danger">
                            <i class="fas fa-shield-alt me-1"></i> Super Administrateur
                        </label>
                    </div>
                </div>
            </div>

            <div class="hr-line-dashed my-4"></div>

            <div class="text-end">
                <button type="submit" class="btn btn-success shadow-sm fw-bold">
                    <i class="fas fa-save me-1"></i> Enregistrer l'utilisateur
                </button>
            </div>

            <?= $this->Form->end() ?>
        </div>
    </div>
</div>

<?php $this->Html->script('views/Users/create.js', ['type' => 'module', 'block' => 'scriptBottom']); ?>

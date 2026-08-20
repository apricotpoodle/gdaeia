<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\Applicationform $applicationform
 */
?>
<div class="row">
    <div class="column-responsive column-80">
        <div class="applicationforms form content">
            <h3><?= __('Modifier la demande #{0}', h($applicationform->id)) ?></h3>
            <div id="applicationform-edit-app" data-id="<?= $applicationform->id ?>">
                <p><em><?= __('Chargement du formulaire de modification sécurisé...') ?></em></p>
            </div>
        </div>
    </div>
</div>
Bonjour <?= $recipient->display_name ?? $recipient->email ?>,

La demande n°<?= $applicationform->id ?> est <?= $state === 'acceptee' ? 'acceptée' : 'refusée' ?>.
<?php if ($comment): ?>

Motif : <?= $comment ?>
<?php endif; ?>

Consulter la demande :
<?= $url ?>

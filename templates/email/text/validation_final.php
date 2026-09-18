<?php
/**
 * @var \App\View\AppView $this
 * @var string $comment
 * @var object $recipient
 * @var mixed $state
 * @var string $url
 * @var \App\Model\Entity\Applicationform $applicationform
 */
?>
Bonjour <?= $recipient->display_name ?? $recipient->email ?>,

La demande n°<?= $applicationform->id ?> est <?= $state === 'acceptee' ? 'acceptée' : 'refusée' ?>.
<?php if ($comment): ?>

Motif : <?= $comment ?>
<?php endif; ?>

Consulter la demande :
<?= $url ?>

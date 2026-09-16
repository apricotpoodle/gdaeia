<p>Bonjour <?= h($recipient->display_name ?? $recipient->email) ?>,</p>
<p>La demande n°<?= h($applicationform->id) ?> est <?= h($state === 'acceptee' ? 'acceptée' : 'refusée') ?>.</p>
<?php if ($comment): ?><p>Motif : <?= h($comment) ?></p><?php endif; ?>
<p><a href="<?= h($url) ?>">Consulter la demande</a></p>

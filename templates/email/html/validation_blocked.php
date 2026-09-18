<?php
/**
 * @var \App\View\AppView $this
 * @var mixed $issues
 * @var object $recipient
 * @var mixed $url
 * @var \App\Model\Entity\Applicationform $applicationform
 */
?>
<p>Bonjour <?= h($recipient->display_name ?? $recipient->email) ?>,</p>
<p>Le cycle de validation de la demande n°<?= h($applicationform->id) ?> ne peut pas être lancé, car sa configuration est incomplète.</p>
<ul>
<?php foreach ($issues as $issue): ?>
    <li><?= h($issue) ?></li>
<?php endforeach; ?>
</ul>
<p><a href="<?= h($url) ?>">Consulter la demande et corriger la configuration</a></p>

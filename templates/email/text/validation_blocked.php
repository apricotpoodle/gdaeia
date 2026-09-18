<?php
/**
 * @var \App\View\AppView $this
 * @var mixed $issues
 * @var object $recipient
 * @var string $url
 * @var \App\Model\Entity\Applicationform $applicationform
 */
?>
Bonjour <?= $recipient->display_name ?? $recipient->email ?>,

Le cycle de validation de la demande n°<?= $applicationform->id ?> ne peut pas être lancé, car sa configuration est incomplète.

<?php foreach ($issues as $issue): ?>
- <?= $issue ?>
<?php endforeach; ?>

Consulter la demande et corriger la configuration :
<?= $url ?>

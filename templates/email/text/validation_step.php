<?php
/**
 * @var \App\View\AppView $this
 * @var object $recipient
 * @var string $url
 * @var \App\Model\Entity\Applicationform $applicationform
 */
?>
Bonjour <?= $recipient->display_name ?? $recipient->email ?>,

Votre validation est attendue pour la demande n°<?= $applicationform->id ?>.

Consulter la demande et voter :
<?= $url ?>

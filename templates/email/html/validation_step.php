<?php
/**
 * @var \App\View\AppView $this
 * @var object $recipient
 * @var mixed $url
 * @var \App\Model\Entity\Applicationform $applicationform
 */
?>
<p>Bonjour <?= h($recipient->display_name ?? $recipient->email) ?>,</p>
<p>Votre validation est attendue pour la demande n°<?= h($applicationform->id) ?>.</p>
<p><a href="<?= h($url) ?>">Consulter la demande et voter</a></p>

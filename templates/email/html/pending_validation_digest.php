<?php
/** @var \App\Model\Entity\User $recipient @var list<\App\Model\Entity\Applicationform> $applicationforms */
?>
<p>Bonjour <?= h($recipient->display_name ?? $recipient->email) ?>,</p>
<p>Voici les demandes nécessitant votre action, classées par date de début :</p>
<ul><?php foreach ($applicationforms as $applicationform): ?><li>DAE n°<?= h($applicationform->id) ?> — début <?= h($applicationform->begin_at?->format('d/m/Y')) ?></li><?php endforeach; ?></ul>

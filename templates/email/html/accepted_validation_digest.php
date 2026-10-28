<?php
/** @var \App\Model\Entity\User $recipient @var list<\App\Model\Entity\Applicationform> $applicationforms */
?>
<p>Bonjour <?= h($recipient->display_name ?? $recipient->email) ?>,</p>
<p>Demandes complètement validées cette semaine :</p>
<ul><?php foreach ($applicationforms as $applicationform): ?><li>DAE n°<?= h($applicationform->id) ?></li><?php endforeach; ?></ul>

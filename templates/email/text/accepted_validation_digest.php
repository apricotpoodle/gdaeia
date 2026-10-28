<?php
/** @var \App\Model\Entity\User $recipient @var list<\App\Model\Entity\Applicationform> $applicationforms */
?>
Bonjour <?= $recipient->display_name ?? $recipient->email ?>,

Demandes complètement validées cette semaine :
<?php foreach ($applicationforms as $applicationform): ?>
- DAE n°<?= $applicationform->id ?>
<?php endforeach; ?>

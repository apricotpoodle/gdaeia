<?php
/** @var \App\Model\Entity\User $recipient @var list<\App\Model\Entity\Applicationform> $applicationforms */
?>
Bonjour <?= $recipient->display_name ?? $recipient->email ?>,

Voici les demandes nécessitant votre action, classées par date de début :
<?php foreach ($applicationforms as $applicationform): ?>
- DAE n°<?= $applicationform->id ?> — début <?= $applicationform->begin_at?->format('d/m/Y') ?>
<?php endforeach; ?>

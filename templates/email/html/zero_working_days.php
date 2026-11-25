<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\User $recipient
 * @var \App\Model\Entity\Applicationform $applicationform
 * @var string $url
 */
?>
<p><?= h(__('Bonjour {0},', $recipient->get('display_name'))) ?></p>
<p><?= h(__('La demande n°{0} a été enregistrée avec zéro jour travaillé.', $applicationform->id)) ?></p>
<p><?= h(__('Vérifiez les dates et le nombre de jours déclarés.')) ?></p>
<p><a href="<?= h($url) ?>"><?= h(__('Consulter la demande')) ?></a></p>

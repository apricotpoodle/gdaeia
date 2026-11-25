<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\User $recipient
 * @var \App\Model\Entity\Applicationform $applicationform
 * @var string $url
 */
?>
<?= __('Bonjour {0},', $recipient->get('display_name')) ?>

<?= __('La demande n°{0} a été enregistrée avec zéro jour travaillé.', $applicationform->id) ?>
<?= __('Vérifiez les dates et le nombre de jours déclarés.') ?>

<?= __('Consulter la demande : {0}', $url) ?>

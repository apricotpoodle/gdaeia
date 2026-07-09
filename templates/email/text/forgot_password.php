<?php

/**
 * Template texte brut pour la réinitialisation de mot de passe
 *
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\User $user
 * @var string $resetUrl
 */
?>
Bonjour <?= $user->firstname ?: 'Utilisateur' ?>,

Nous avons reçu une demande de réinitialisation de mot de passe pour le compte associé à cette adresse e-mail.

Vous pouvez réinitialiser votre mot de passe en visitant le lien ci-dessous. Ce lien est valide pendant 1 heure :

<?= $resetUrl ?>


Si vous n'êtes pas à l'origine de cette demande, vous pouvez ignorer cet e-mail en toute sécurité. Votre mot de passe restera inchangé.

Cordialement,
L'équipe de l'Application

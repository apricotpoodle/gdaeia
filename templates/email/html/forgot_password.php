<?php

/**
 * Template HTML pour la réinitialisation de mot de passe
 *
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\User $user
 * @var string $resetUrl
 */
?>
<div style="font-family: Arial, sans-serif; line-height: 1.6; color: #333; max-width: 600px; margin: 0 auto; padding: 20px;">
    <p>Bonjour <strong><?= h($user->firstname ?: 'Utilisateur') ?></strong>,</p>

    <p>Nous avons reçu une demande de réinitialisation de mot de passe pour le compte associé à cette adresse e-mail.</p>

    <p>Vous pouvez réinitialiser votre mot de passe en cliquant sur le bouton ci-dessous. <em>Ce lien est valide pendant 1 heure.</em></p>

    <p style="text-align: center; margin: 30px 0;">
        <a href="<?= $resetUrl ?>" style="display: inline-block; padding: 12px 24px; background-color: #0d6efd; color: #ffffff; text-decoration: none; border-radius: 6px; font-weight: bold;">
            Réinitialiser mon mot de passe
        </a>
    </p>

    <p>Si le bouton ne fonctionne pas, veuillez copier et coller l'URL suivante dans votre navigateur :</p>
    <p style="word-break: break-all; background-color: #f8f9fa; padding: 10px; border-radius: 4px; font-size: 0.9em;">
        <a href="<?= $resetUrl ?>"><?= $resetUrl ?></a>
    </p>

    <hr style="border: none; border-top: 1px solid #eee; margin: 30px 0;">

    <p style="font-size: 0.9em; color: #6c757d;">
        Si vous n'êtes pas à l'origine de cette demande, vous pouvez ignorer cet e-mail en toute sécurité. Votre mot de passe restera inchangé.
    </p>

    <p style="font-size: 0.9em; color: #6c757d;">
        Cordialement,<br>
        <strong>L'équipe de l'Application</strong>
    </p>
</div>

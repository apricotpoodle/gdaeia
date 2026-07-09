<?php

declare(strict_types=1);

namespace App\Mailer;

use App\Model\Entity\User;
use Cake\Core\Configure;

/**
 * @class UserMailer
 * @description Gère l'expédition des courriels transactionnels liés au domaine Utilisateur.
 * @package App\Mailer
 */
class UserMailer extends AppMailer
{
    /**
     * Construit et expédie le courriel de réinitialisation de mot de passe.
     *
     * @param \App\Model\Entity\User $user L'entité utilisateur contenant le jeton.
     * @return void
     */
    public function forgotPassword(User $user): void
    {
        // Récupération sécurisée de l'URL de base depuis la configuration
        $fullBaseUrl = Configure::read('App.fullBaseUrl', 'http://localhost');
        $resetUrl = $fullBaseUrl . '/users/reset-password/' . $user->token;

        $this->setTo($user->email)
            ->setSubject('Demande de réinitialisation de votre mot de passe')
            ->setViewVars([
                'user' => $user,
                'resetUrl' => $resetUrl,
            ])
            ->viewBuilder()
            ->setTemplate('forgot_password'); // Cible: templates/email/html/forgot_password.php
    }
}

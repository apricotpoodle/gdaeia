<?php
declare(strict_types=1);

namespace App\Service;

use App\Mailer\TreeIntegrityMailer;
use Cake\Core\Configure;
use Cake\I18n\FrozenTime;

/** Centralise la configuration et l’expédition des alertes TreeBehavior. */
final class TreeIntegrityAlertService
{
    /**
     * Retourne les paramètres obligatoires absents ou invalides.
     *
     * @return list<string> Messages de configuration en erreur.
     */
    public function configurationErrors(): array
    {
        $errors = [];
        $recipient = $this->recipient();
        if ($recipient === '') {
            $errors[] = __('TREE_INTEGRITY_ALERT_RECIPIENT est obligatoire.');
        } elseif (filter_var($recipient, FILTER_VALIDATE_EMAIL) === false) {
            $errors[] = __('TREE_INTEGRITY_ALERT_RECIPIENT ne contient pas une adresse électronique valide.');
        }
        if ($this->instanceName() === '') {
            $errors[] = __('APP_INSTANCE_NAME est obligatoire pour identifier l’instance dans les alertes.');
        }

        return $errors;
    }

    /**
     * Envoie une alerte correspondant à des incohérences réellement détectées.
     *
     * @param list<array<string, mixed>> $reports Rapports en échec.
     * @return bool Indique si le relais SMTP a accepté le courriel.
     */
    public function send(array $reports): bool
    {
        return $this->sendReport($reports, false);
    }

    /**
     * Envoie un courriel de test sans interroger ni modifier les arbres.
     *
     * @return bool Indique si le relais SMTP a accepté le courriel.
     */
    public function sendTest(): bool
    {
        return $this->sendReport([[
            'table' => 'test de connectivité',
            'node_count' => 0,
            'issues' => [[
                'code' => 'TEST_ALERTE',
                'message' => 'Ceci est un courriel de test ; aucune incohérence n’a été détectée.',
                'details' => ['purpose' => 'Validation du destinataire, du relais SMTP et du contenu des alertes.'],
            ]],
        ]], true);
    }

    /**
     * @param list<array<string, mixed>> $reports Rapports à présenter.
     * @param bool $isTest Indique un envoi de test sans anomalie réelle.
     * @return bool Indique si le relais SMTP a accepté le courriel.
     */
    private function sendReport(array $reports, bool $isTest): bool
    {
        if ($this->configurationErrors() !== []) {
            return false;
        }

        return (new TreeIntegrityMailer())->safeSend('integrityAlert', [
            $this->recipient(),
            $reports,
            $this->instanceName(),
            (string)env('APP_HOST_HOSTNAME', 'non renseigné'),
            gethostname() ?: 'inconnu',
            FrozenTime::now()->format(DATE_ATOM),
            $isTest,
        ]);
    }

    /** @return string Adresse destinataire nettoyée. */
    private function recipient(): string
    {
        return trim((string)Configure::read('TreeIntegrity.alertRecipient', ''));
    }

    /** @return string Nom stable de l’instance nettoyé. */
    private function instanceName(): string
    {
        return trim((string)Configure::read('TreeIntegrity.instanceName', ''));
    }
}

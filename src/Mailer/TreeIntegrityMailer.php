<?php
declare(strict_types=1);

namespace App\Mailer;

/** Courriel d'alerte destiné aux opérateurs en cas d'incohérence TreeBehavior. */
final class TreeIntegrityMailer extends AppMailer
{
    /**
     * Compose une alerte pédagogique sans modifier les données concernées.
     *
     * @param string $recipient Adresse configurée de l'opérateur.
     * @param list<array<string, mixed>> $reports Rapports d'intégrité en échec.
     * @param string $instanceName Nom stable de l'instance applicative.
     * @param string $hostName Nom de la machine hôte lorsque disponible.
     * @param string $containerName Nom du conteneur qui a exécuté le diagnostic.
     * @param string $reportedAt Date et heure ISO 8601 du diagnostic.
     * @param bool $isTest Indique un courriel de test sans anomalie réelle.
     * @return void
     */
    public function integrityAlert(
        string $recipient,
        array $reports,
        string $instanceName,
        string $hostName,
        string $containerName,
        string $reportedAt,
        bool $isTest,
    ): void {
        $issueCount = array_sum(array_map(static fn(array $report): int => count($report['issues']), $reports));
        $this->setTo($recipient)
            ->setSubject($isTest
                ? __('[TEST] Alerte intégrité Tree — {0}', $instanceName)
                : __('Alerte intégrité Tree — {0} — {1} anomalie(s)', $instanceName, $issueCount))
            ->setViewVars([
                'reports' => $reports,
                'instanceName' => $instanceName,
                'hostName' => $hostName,
                'containerName' => $containerName,
                'reportedAt' => $reportedAt,
                'issueCount' => $issueCount,
                'isTest' => $isTest,
            ])
            ->viewBuilder()->setTemplate('tree_integrity_alert');
    }
}

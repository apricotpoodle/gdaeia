<?php
declare(strict_types=1);

namespace App\Service\Integrity;

/** Contrat d’un contrôle d’intégrité indépendant. */
interface IntegrityCheckInterface
{
    /** Identifiant stable du domaine contrôlé. */
    public function id(): string;

    /**
     * Exécute le contrôle sans modifier la base de données.
     *
     * @return array{
     *     id: string,
     *     status: 'ok'|'warning'|'error',
     *     rows_scanned: int,
     *     violations: int,
     *     issues: list<array<string, mixed>>,
     *     allowed?: int
     * }
     */
    public function check(): array;
}

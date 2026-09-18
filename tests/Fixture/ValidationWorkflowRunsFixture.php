<?php
declare(strict_types=1);

namespace App\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

/**
 * Réinitialise les exécutions de workflow entre les scénarios d'intégration.
 *
 * Les scénarios créent explicitement les exécutions dont ils ont besoin afin
 * de conserver un périmètre de données lisible.
 */
class ValidationWorkflowRunsFixture extends TestFixture
{
    /**
     * @var list<array<string, mixed>>
     */
    public array $records = [];
}

<?php
declare(strict_types=1);

namespace App\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

/** Règles locales isolées pour chaque scénario. */
class ValidationCircuitRulesFixture extends TestFixture
{
    public array $records = [];

    /** Réfléchit la table de test même si la navigation l'a déjà chargée. */
    public function init(): void
    {
        $this->getTableLocator()->remove('ValidationCircuitRules');
        parent::init();
    }
}

<?php
declare(strict_types=1);

namespace App\Test\Fixture;

use Cake\TestSuite\Fixture\TestFixture;

/** Habilitations volontairement vides : aucun droit implicite. */
class ValidationCircuitManagersFixture extends TestFixture
{
    public array $records = [];

    /** Réfléchit la table de test sans réutiliser une configuration antérieure. */
    public function init(): void
    {
        $this->getTableLocator()->remove('ValidationCircuitManagers');
        parent::init();
    }
}

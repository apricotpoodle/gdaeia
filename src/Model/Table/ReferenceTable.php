<?php
declare(strict_types=1);

namespace App\Model\Table;

/** Socle des tables de nomenclature qui protègent leurs références communes. */
class ReferenceTable extends AppTable
{
    /**
     * Attache la protection aux sept nomenclatures génériques.
     *
     * @param array<string, mixed> $config Configuration de la table.
     * @return void
     */
    public function initialize(array $config): void
    {
        parent::initialize($config);
        $this->addBehavior('ProtectBaseRecords', ['priority' => 1]);
    }
}

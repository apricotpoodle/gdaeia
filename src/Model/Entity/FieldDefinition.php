<?php
declare(strict_types=1);

namespace App\Model\Entity;

use Cake\ORM\Entity;

/** Définition métier d'un champ affichable. */
final class FieldDefinition extends Entity
{
    /**
     * @var array<string, bool>
     */
    protected array $_accessible = [
        'resource' => true, 'field' => true, 'label' => true,
        'description' => true, 'active' => true, 'position' => true,
        'created' => true, 'modified' => true,
    ];
}

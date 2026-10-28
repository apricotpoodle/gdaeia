<?php
declare(strict_types=1);

namespace App\Model\Entity;

use Cake\ORM\Entity;

/**
 * Règle locale déterminant la dernière séquence d'un circuit.
 *
 * @property int $id
 * @property int $department_id
 * @property int $contracttype_id
 * @property int $final_role_id
 */
class ValidationCircuitRule extends Entity
{
    protected array $_accessible = [
        'department_id' => true,
        'contracttype_id' => true,
        'final_role_id' => true,
        'department' => true,
        'contracttype' => true,
        'final_role' => true,
    ];

    public const FIELD_ID = 'id';
    public const FIELD_DEPARTMENT_ID = 'department_id';
    public const FIELD_CONTRACTTYPE_ID = 'contracttype_id';
    public const FIELD_FINAL_ROLE_ID = 'final_role_id';
    public const FIELD_DEPARTMENT = 'department';
    public const FIELD_CONTRACTTYPE = 'contracttype';
    public const FIELD_FINAL_ROLE = 'final_role';
}

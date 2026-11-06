<?php
declare(strict_types=1);

namespace App\Model\Entity;

use Cake\ORM\Entity;

/**
 * Habilitation d'un rôle à administrer les règles d'un département exact.
 *
 * @property int $id
 * @property int $department_id
 * @property int $role_id
 * @property \App\Model\Entity\Department $department
 * @property \App\Model\Entity\Role $role
 */
class ValidationCircuitManager extends Entity
{
    protected array $_accessible = ['department_id' => true, 'role_id' => true];
}

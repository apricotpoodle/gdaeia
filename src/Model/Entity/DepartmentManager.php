<?php
declare(strict_types=1);

namespace App\Model\Entity;

use Cake\ORM\Entity;

/** Association entre un département et un responsable. */
class DepartmentManager extends Entity
{
    /**
     * @var array<string, bool>
     */
    protected array $_accessible = [
        'department_id' => true,
        'user_id' => true,
        'department' => true,
        'user' => true,
    ];

    public const FIELD_DEPARTMENT_ID = 'department_id';
    public const FIELD_USER_ID = 'user_id';
}

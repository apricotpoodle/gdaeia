<?php
declare(strict_types=1);

namespace App\Model\Entity;

use Cake\ORM\Entity;

/** Attribution d’un périmètre de permission à un rôle. */
class RolePermission extends Entity
{
    protected array $_accessible = [
        'role_id' => true,
        'permission_id' => true,
        'scope_type' => true,
        'role' => true,
        'permission' => true,
    ];

    public const FIELD_ID = 'id';
    public const FIELD_ROLE_ID = 'role_id';
    public const FIELD_PERMISSION_ID = 'permission_id';
    public const FIELD_SCOPE_TYPE = 'scope_type';
    public const FIELD_ROLE = 'role';
    public const FIELD_PERMISSION = 'permission';
}

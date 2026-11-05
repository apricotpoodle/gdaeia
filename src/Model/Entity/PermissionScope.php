<?php
declare(strict_types=1);

namespace App\Model\Entity;

use Cake\ORM\Entity;

/** Périmètre admissible pour une permission. */
class PermissionScope extends Entity
{
    protected array $_accessible = [
        'permission_id' => true,
        'scope_type' => true,
        'permission' => true,
    ];

    public const FIELD_ID = 'id';
    public const FIELD_PERMISSION_ID = 'permission_id';
    public const FIELD_SCOPE_TYPE = 'scope_type';
    public const FIELD_PERMISSION = 'permission';
}

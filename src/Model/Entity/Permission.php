<?php
declare(strict_types=1);

namespace App\Model\Entity;

use Cake\ORM\Entity;

/** Permission du catalogue des capacités applicatives. */
class Permission extends Entity
{
    protected array $_accessible = [
        'resource' => true,
        'action' => true,
        'label' => true,
        'permission_scopes' => true,
        'role_permissions' => true,
    ];

    public const FIELD_ID = 'id';
    public const FIELD_RESOURCE = 'resource';
    public const FIELD_ACTION = 'action';
    public const FIELD_CODE = 'code';
    public const FIELD_LABEL = 'label';
    public const FIELD_PERMISSION_SCOPES = 'permission_scopes';
    public const FIELD_ROLE_PERMISSIONS = 'role_permissions';
}

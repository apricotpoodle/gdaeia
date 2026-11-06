<?php
declare(strict_types=1);

namespace App\Model\Entity;

use Cake\ORM\Entity;

/** Cible contrôlée d'une autorisation de champ ou de zone. */
final class AuthorizationTarget extends Entity
{
    public const TYPE_FIELD = 'field';
    public const TYPE_SECTION = 'section';
    public const TYPES = [self::TYPE_FIELD, self::TYPE_SECTION];
    public const FIELD_ID = 'id';
    public const FIELD_RESOURCE = 'resource';
    public const FIELD_TARGET_TYPE = 'target_type';
    public const FIELD_TARGET = 'target';
    public const FIELD_LABEL = 'label';
    public const FIELD_DESCRIPTION = 'description';
    public const FIELD_ACTIVE = 'active';
    public const FIELD_POSITION = 'position';
    public const FIELD_CREATED = 'created';
    public const FIELD_MODIFIED = 'modified';

    /**
     * @var array<string, bool>
     */
    protected array $_accessible = [
        'resource' => true, 'target_type' => true, 'target' => true, 'label' => true,
        'description' => true, 'active' => true, 'position' => true,
        'created' => true, 'modified' => true,
    ];
}

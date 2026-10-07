<?php
declare(strict_types=1);

namespace App\Model\Entity;

use Cake\ORM\Entity;

/**
 * Configuration globale du workflow de validation.
 *
 * @property int $id
 * @property string $name
 * @property string $value
 * @property \Cake\I18n\DateTime $created
 * @property \Cake\I18n\DateTime $modified
 */
final class WorkflowSetting extends Entity
{
    public const FIELD_ID = 'id';
    public const FIELD_NAME = 'name';
    public const FIELD_VALUE = 'value';
    public const FIELD_CREATED = 'created';
    public const FIELD_MODIFIED = 'modified';

    /**
     * @var array<string, bool>
     */
    protected array $_accessible = [
        'name' => true,
        'value' => true,
        'created' => true,
        'modified' => true,
    ];
}

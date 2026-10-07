<?php
declare(strict_types=1);

namespace App\Model\Entity;

use Cake\ORM\Entity;

/** Définition métier d'un champ affichable. */
/**
 * @property int $id
 * @property string $resource
 * @property string $field
 * @property string $label
 * @property string|null $description
 * @property bool $active
 * @property int $position
 * @property \Cake\I18n\DateTime $created
 * @property \Cake\I18n\DateTime $modified
 */
final class FieldDefinition extends Entity
{
    public const FIELD_ID = 'id';
    public const FIELD_RESOURCE = 'resource';
    public const FIELD_FIELD = 'field';
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
        'resource' => true, 'field' => true, 'label' => true,
        'description' => true, 'active' => true, 'position' => true,
        'created' => true, 'modified' => true,
    ];
}

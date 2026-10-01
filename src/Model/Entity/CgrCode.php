<?php
declare(strict_types=1);

namespace App\Model\Entity;

use Cake\ORM\Entity;

/**
 * CgrCode Entity
 *
 * @property int $id
 * @property int $department_id
 * @property string $type
 * @property string $code
 * @property string $label
 * @property bool $active
 * @property bool $is_system
 * @property \Cake\I18n\DateTime $created
 * @property \Cake\I18n\DateTime|null $modified
 *
 * @property \App\Model\Entity\Department $department
 * @property \App\Model\Entity\Department[] $using_departments
 */
class CgrCode extends Entity
{
    /**
     * Fields that can be mass assigned using newEntity() or patchEntity().
     *
     * Note that when '*' is set to true, this allows all unspecified fields to
     * be mass assigned. For security purposes, it is advised to set '*' to false
     * (or remove it), and explicitly make individual fields accessible as needed.
     *
     * @var array<string, bool>
     */
    protected array $_accessible = [
        'department_id' => true,
        'type' => true,
        'code' => true,
        'label' => true,
        'active' => true,
        'is_system' => true,
        'created' => true,
        'modified' => true,
        'department' => true,
        'cgr_codes' => true,
    ];

    public const FIELD_ID = 'id';
    public const FIELD_DEPARTMENT_ID = 'department_id';
    public const FIELD_TYPE = 'type';
    public const FIELD_CODE = 'code';
    public const FIELD_LABEL = 'label';
    public const FIELD_ACTIVE = 'active';
    public const FIELD_IS_SYSTEM = 'is_system';
    public const FIELD_CREATED = 'created';
    public const FIELD_MODIFIED = 'modified';
    public const FIELD_DEPARTMENT = 'department';
    public const FIELD_USING_DEPARTMENTS = 'using_departments';
}

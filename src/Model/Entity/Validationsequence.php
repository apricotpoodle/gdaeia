<?php
declare(strict_types=1);

namespace App\Model\Entity;

use Cake\ORM\Entity;

/**
 * Validationsequence Entity
 *
 * @property int $id
 * @property int $department_id
 * @property string $name
 * @property string|null $description
 * @property int $role_id
 * @property int $sequence
 * @property int|null $sequence_max
 * @property int|null $reminder_delay_hours
 * @property \Cake\I18n\DateTime|null $deleted
 * @property \Cake\I18n\DateTime|null $modified
 * @property \Cake\I18n\DateTime $created
 *
 * @property \App\Model\Entity\Department $department
 * @property \App\Model\Entity\Role $role
 * @property \App\Model\Entity\Applicationvalidationstep[] $applicationvalidationsteps
 */
class Validationsequence extends Entity
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
        'name' => true,
        'description' => true,
        'role_id' => true,
        'sequence' => true,
        'reminder_delay_hours' => true,
        'deleted' => true,
        'modified' => true,
        'created' => true,
        'department' => true,
        'role' => true,
        'applicationvalidationsteps' => true,
    ];

    public const FIELD_ID = 'id';
    public const FIELD_DEPARTMENT_ID = 'department_id';
    public const FIELD_NAME = 'name';
    public const FIELD_DESCRIPTION = 'description';
    public const FIELD_ROLE_ID = 'role_id';
    public const FIELD_SEQUENCE = 'sequence';
    public const FIELD_REMINDER_DELAY_HOURS = 'reminder_delay_hours';
    public const FIELD_DELETED = 'deleted';
    public const FIELD_MODIFIED = 'modified';
    public const FIELD_CREATED = 'created';
    public const FIELD_DEPARTMENT = 'department';
    public const FIELD_ROLE = 'role';
    public const FIELD_APPLICATIONVALIDATIONSTEPS = 'applicationvalidationsteps';
}

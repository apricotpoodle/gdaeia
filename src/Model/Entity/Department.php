<?php
declare(strict_types=1);

namespace App\Model\Entity;

use Cake\ORM\Entity;

/**
 * Department Entity
 *
 * @property int $id
 * @property int|null $parent_id
 * @property int|null $lft
 * @property int|null $rght
 * @property int|null $level
 * @property bool $base
 * @property string $code
 * @property string $name
 * @property string|null $sort
 * @property int $department_type_id
 * @property int|null $cgr_strategy_id
 * @property \Cake\I18n\DateTime|null $deleted
 * @property \Cake\I18n\DateTime $created
 * @property \Cake\I18n\DateTime|null $modified
 *
 * @property \App\Model\Entity\Department|null $parent_department
 * @property list<\App\Model\Entity\Department> $child_departments
 * @property list<\App\Model\Entity\CgrCode> $cgr_codes
 * @property \App\Model\Entity\CgrStrategy $cgr_strategy
 * @property list<\App\Model\Entity\User> $managers
 * @property \App\Model\Entity\Applicationform[] $applicationforms
 */
class Department extends Entity
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
        'parent_id' => true,
        'lft' => true,
        'rght' => true,
        'level' => true,
        'base' => true,
        'code' => true,
        'name' => true,
        'sort' => true,
        'department_type_id' => true,
        'cgr_strategy_id' => true,
        'deleted' => true,
        'created' => true,
        'modified' => true,
        'parent_department' => true,
        'child_departments' => true,
        'cgr_codes' => true,
        'managers' => true,
        'cgr_strategy' => true,
        'applicationforms' => true,
    ];

    public const FIELD_ID = 'id';
    public const FIELD_PARENT_ID = 'parent_id';
    public const FIELD_LFT = 'lft';
    public const FIELD_RGHT = 'rght';
    public const FIELD_LEVEL = 'level';
    public const FIELD_BASE = 'base';
    public const FIELD_CODE = 'code';
    public const FIELD_NAME = 'name';
    public const FIELD_SORT = 'sort';
    public const FIELD_DEPARTMENT_TYPE_ID = 'department_type_id';
    public const FIELD_CGR_STRATEGY_ID = 'cgr_strategy_id';
    public const FIELD_DELETED = 'deleted';
    public const FIELD_CREATED = 'created';
    public const FIELD_MODIFIED = 'modified';
    public const FIELD_PARENT_DEPARTMENT = 'parent_department';
    public const FIELD_CHILD_DEPARTMENTS = 'child_departments';
    public const FIELD_CGR_CODES = 'cgr_codes';
    public const FIELD_CGR_STRATEGY = 'cgr_strategy';
    public const FIELD_MANAGERS = 'managers';
    public const FIELD_APPLICATIONFORMS = 'applicationforms';
}

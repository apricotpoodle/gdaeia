<?php
declare(strict_types=1);

namespace App\Model\Entity;

use Cake\ORM\Entity;

/**
 * Department Entity
 *
 * @property int $id
 * @property int|null $parent_id
 * @property int|null $cgr_code_id
 * @property int|null $lft
 * @property int|null $rght
 * @property int|null $level
 * @property bool $base
 * @property string $code
 * @property string $name
 * @property string|null $sort
 * @property int $department_type_id
 * @property int|null $cgr_strategy_id
 * @property string|null $default_cgr
 * @property int|null $current_manager_id
 * @property \Cake\I18n\DateTime|null $deleted
 * @property \Cake\I18n\DateTime $created
 * @property \Cake\I18n\DateTime|null $modified
 *
 * @property \App\Model\Entity\ParentDepartment $parent_department
 * @property \App\Model\Entity\ChildDepartment[] $child_departments
 * @property \App\Model\Entity\CgrCode $default_cgr_code
 * @property \App\Model\Entity\CgrCode[] $owned_cgr_codes
 * @property \App\Model\Entity\CgrStrategy $cgr_strategy
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
        'cgr_code_id' => true,
        'lft' => true,
        'rght' => true,
        'level' => true,
        'base' => true,
        'code' => true,
        'name' => true,
        'sort' => true,
        'department_type_id' => true,
        'cgr_strategy_id' => true,
        'default_cgr' => true,
        'current_manager_id' => true,
        'deleted' => true,
        'created' => true,
        'modified' => true,
        'parent_department' => true,
        'child_departments' => true,
        'default_cgr_code' => true,
        'owned_cgr_codes' => true,
        'cgr_strategy' => true,
        'applicationforms' => true,
    ];
}

<?php
declare(strict_types=1);

namespace App\Model\Entity;

use Cake\ORM\Entity;

/**
 * RoleMenu Entity
 *
 * @property int $id
 * @property int $role_id
 * @property int $menu_id
 * @property int|null $department_id
 * @property \Cake\I18n\DateTime $created
 * @property \Cake\I18n\DateTime|null $modified
 *
 * @property \App\Model\Entity\Role $role
 * @property \App\Model\Entity\Menu $menu
 * @property \App\Model\Entity\Department $department
 */
class RoleMenu extends Entity
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
        'role_id' => true,
        'menu_id' => true,
        'department_id' => true,
        'created' => true,
        'modified' => true,
        'role' => true,
        'menu' => true,
        'department' => true,
    ];
}

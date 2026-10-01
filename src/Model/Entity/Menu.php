<?php
declare(strict_types=1);

namespace App\Model\Entity;

use Cake\ORM\Entity;

/**
 * Menu Entity
 *
 * @property int $id
 * @property int|null $parent_id
 * @property int $lft
 * @property int $rght
 * @property int|null $level
 * @property string|null $name
 * @property string|null $url
 * @property bool|null $active
 * @property bool|null $disabled
 * @property bool|null $dividor_before
 * @property list<\App\Model\Entity\Menu> $children
 * @property array<string, mixed> $grid_rights
 *
 * @property \App\Model\Entity\Menu $parent_menu
 * @property \App\Model\Entity\Menu[] $child_menus
 * @property \App\Model\Entity\RoleMenu[] $role_menus
 */
class Menu extends Entity
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
        'name' => true,
        'url' => true,
        'active' => true,
        'disabled' => true,
        'dividor_before' => true,
        'parent_menu' => true,
        'child_menus' => true,
        'role_menus' => true,
    ];

    public const FIELD_ID = 'id';
    public const FIELD_PARENT_ID = 'parent_id';
    public const FIELD_LFT = 'lft';
    public const FIELD_RGHT = 'rght';
    public const FIELD_LEVEL = 'level';
    public const FIELD_NAME = 'name';
    public const FIELD_URL = 'url';
    public const FIELD_ACTIVE = 'active';
    public const FIELD_DISABLED = 'disabled';
    public const FIELD_DIVIDOR_BEFORE = 'dividor_before';
    public const FIELD_CHILDREN = 'children';
    public const FIELD_GRID_RIGHTS = 'grid_rights';
    public const FIELD_PARENT_MENU = 'parent_menu';
    public const FIELD_CHILD_MENUS = 'child_menus';
    public const FIELD_ROLE_MENUS = 'role_menus';
}

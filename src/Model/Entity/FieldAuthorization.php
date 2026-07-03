<?php
declare(strict_types=1);

namespace App\Model\Entity;

use Cake\ORM\Entity;

/**
 * FieldAuthorization Entity
 *
 * @property int $id
 * @property int $role_id
 * @property string $resource
 * @property string $field
 * @property string $access_level
 * @property \Cake\I18n\DateTime $created
 * @property \Cake\I18n\DateTime|null $modified
 *
 * @property \App\Model\Entity\Role $role
 */
class FieldAuthorization extends Entity
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
        'resource' => true,
        'field' => true,
        'access_level' => true,
        'created' => true,
        'modified' => true,
        'role' => true,
    ];
}

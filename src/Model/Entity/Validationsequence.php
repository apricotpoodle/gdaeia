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
        'deleted' => true,
        'modified' => true,
        'created' => true,
        'department' => true,
        'role' => true,
        'applicationvalidationsteps' => true,
    ];
}

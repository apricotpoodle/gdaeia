<?php
declare(strict_types=1);

namespace App\Model\Entity;

use Cake\ORM\Entity;

/**
 * ValidationVisa Entity
 *
 * @property int $applicationform_id
 * @property int $sequence
 * @property int $role_id
 * @property string|null $op_name
 * @property string $role_name
 * @property string|null $status_name
 * @property \Cake\I18n\DateTime|null $validated_at
 *
 * @property \App\Model\Entity\Applicationform $applicationform
 * @property \App\Model\Entity\Role $role
 */
class ValidationVisa extends Entity
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
        'applicationform_id' => true,
        'sequence' => true,
        'role_id' => true,
        'op_name' => true,
        'role_name' => true,
        'status_name' => true,
        'validated_at' => true,
        'applicationform' => true,
        'role' => true,
    ];
}

<?php
declare(strict_types=1);

namespace App\Model\Entity;

use Cake\ORM\Entity;

/**
 * Validation Entity
 *
 * @property int $id
 * @property int $applicationform_id
 * @property int $user_id
 * @property int $role_id
 * @property \Cake\I18n\DateTime|null $validated
 * @property int|null $validationstatus_id
 * @property string|null $obs
 * @property \Cake\I18n\DateTime|null $deleted
 * @property \Cake\I18n\DateTime $created
 * @property \Cake\I18n\DateTime $modified
 *
 * @property \App\Model\Entity\Applicationform $applicationform
 * @property \App\Model\Entity\User $user
 * @property \App\Model\Entity\Role $role
 * @property \App\Model\Entity\Validationstatus $validationstatus
 */
class Validation extends Entity
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
        'user_id' => true,
        'role_id' => true,
        'validated' => true,
        'validationstatus_id' => true,
        'obs' => true,
        'deleted' => true,
        'created' => true,
        'modified' => true,
        'applicationform' => true,
        'user' => true,
        'role' => true,
        'validationstatus' => true,
    ];
}

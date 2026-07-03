<?php
declare(strict_types=1);

namespace App\Model\Entity;

use Cake\ORM\Entity;

/**
 * Currentvalidationrole Entity
 *
 * @property int $applicationform_id
 * @property int $department_id
 * @property int $validator_role_id
 * @property int $validation_sequence
 * @property int|null $validationstatus_id
 * @property int $en_cours
 * @property int $accepted
 * @property int $rejected
 *
 * @property \App\Model\Entity\Applicationform $applicationform
 * @property \App\Model\Entity\Department $department
 * @property \App\Model\Entity\Validationstatus $validationstatus
 */
class Currentvalidationrole extends Entity
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
        'department_id' => true,
        'validator_role_id' => true,
        'validation_sequence' => true,
        'validationstatus_id' => true,
        'en_cours' => true,
        'accepted' => true,
        'rejected' => true,
        'applicationform' => true,
        'department' => true,
        'validationstatus' => true,
    ];
}

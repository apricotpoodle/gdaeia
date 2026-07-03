<?php
declare(strict_types=1);

namespace App\Model\Entity;

use Cake\ORM\Entity;

/**
 * Applicationvalidationstep Entity
 *
 * @property int $id
 * @property int $applicationform_id
 * @property int $role_id
 * @property int $validationstatus_id
 * @property string|null $comment
 * @property int $validationsequence_id
 * @property \Cake\I18n\DateTime|null $deleted
 * @property \Cake\I18n\DateTime|null $modified
 * @property \Cake\I18n\DateTime $created
 *
 * @property \App\Model\Entity\Applicationform $applicationform
 * @property \App\Model\Entity\Role $role
 * @property \App\Model\Entity\Validationstatus $validationstatus
 * @property \App\Model\Entity\Validationsequence $validationsequence
 */
class Applicationvalidationstep extends Entity
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
        'role_id' => true,
        'validationstatus_id' => true,
        'comment' => true,
        'validationsequence_id' => true,
        'deleted' => true,
        'modified' => true,
        'created' => true,
        'applicationform' => true,
        'role' => true,
        'validationstatus' => true,
        'validationsequence' => true,
    ];
}

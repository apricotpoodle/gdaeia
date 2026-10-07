<?php
declare(strict_types=1);

namespace App\Model\Entity;

use Cake\ORM\Entity;

/**
 * Applicationformstatus Entity
 *
 * @property int $applicationform_id
 * @property int $has_validations
 * @property int $validationstatus_id
 * @property string|null $valid_percentage
 * @property int|null $current_sequence
 * @property int $en_cours
 * @property int $accepted
 * @property int $rejected
 *
 * @property \App\Model\Entity\Applicationform $applicationform
 * @property \App\Model\Entity\Validationstatus $validationstatus
 */
class Applicationformstatus extends Entity
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
        'has_validations' => true,
        'validationstatus_id' => true,
        'valid_percentage' => true,
        'current_sequence' => true,
        'en_cours' => true,
        'accepted' => true,
        'rejected' => true,
        'applicationform' => true,
        'validationstatus' => true,
    ];

    public const FIELD_APPLICATIONFORM_ID = 'applicationform_id';
    public const FIELD_HAS_VALIDATIONS = 'has_validations';
    public const FIELD_VALIDATIONSTATUS_ID = 'validationstatus_id';
    public const FIELD_VALID_PERCENTAGE = 'valid_percentage';
    public const FIELD_CURRENT_SEQUENCE = 'current_sequence';
    public const FIELD_EN_COURS = 'en_cours';
    public const FIELD_ACCEPTED = 'accepted';
    public const FIELD_REJECTED = 'rejected';
    public const FIELD_APPLICATIONFORM = 'applicationform';
    public const FIELD_VALIDATIONSTATUS = 'validationstatus';
}

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
 * @property \App\Model\Entity\Validationstatus|null $validationstatus
 * @property int|null $applicationvalidationstep_id
 * @property bool $is_proxy
 * @property \App\Model\Entity\Applicationvalidationstep|null $applicationvalidationstep
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
        'applicationvalidationstep_id' => true,
        'is_proxy' => true,
        'obs' => true,
        'deleted' => true,
        'created' => true,
        'modified' => true,
        'applicationform' => true,
        'user' => true,
        'role' => true,
        'validationstatus' => true,
    ];

    public const FIELD_ID = 'id';
    public const FIELD_APPLICATIONFORM_ID = 'applicationform_id';
    public const FIELD_USER_ID = 'user_id';
    public const FIELD_ROLE_ID = 'role_id';
    public const FIELD_VALIDATED = 'validated';
    public const FIELD_VALIDATIONSTATUS_ID = 'validationstatus_id';
    public const FIELD_OBS = 'obs';
    public const FIELD_DELETED = 'deleted';
    public const FIELD_CREATED = 'created';
    public const FIELD_MODIFIED = 'modified';
    public const FIELD_APPLICATIONFORM = 'applicationform';
    public const FIELD_USER = 'user';
    public const FIELD_ROLE = 'role';
    public const FIELD_VALIDATIONSTATUS = 'validationstatus';
    public const FIELD_APPLICATIONVALIDATIONSTEP_ID = 'applicationvalidationstep_id';
    public const FIELD_IS_PROXY = 'is_proxy';
    public const FIELD_APPLICATIONVALIDATIONSTEP = 'applicationvalidationstep';
}

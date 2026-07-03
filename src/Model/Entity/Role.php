<?php
declare(strict_types=1);

namespace App\Model\Entity;

use Cake\ORM\Entity;

/**
 * Role Entity
 *
 * @property int $id
 * @property bool $base
 * @property string $code
 * @property string $name
 * @property string $sort
 * @property \Cake\I18n\DateTime|null $deleted
 * @property \Cake\I18n\DateTime $created
 * @property \Cake\I18n\DateTime|null $modified
 *
 * @property \App\Model\Entity\Applicationvalidationstep[] $applicationvalidationsteps
 * @property \App\Model\Entity\FieldAuthorization[] $field_authorizations
 * @property \App\Model\Entity\RoleMenu[] $role_menus
 * @property \App\Model\Entity\Urd[] $urds
 * @property \App\Model\Entity\User[] $users
 * @property \App\Model\Entity\ValidationVisa[] $validation_visas
 * @property \App\Model\Entity\Validation[] $validations
 * @property \App\Model\Entity\Validationsequence[] $validationsequences
 */
class Role extends Entity
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
        'base' => true,
        'code' => true,
        'name' => true,
        'sort' => true,
        'deleted' => true,
        'created' => true,
        'modified' => true,
        'applicationvalidationsteps' => true,
        'field_authorizations' => true,
        'role_menus' => true,
        'urds' => true,
        'users' => true,
        'validation_visas' => true,
        'validations' => true,
        'validationsequences' => true,
    ];
}

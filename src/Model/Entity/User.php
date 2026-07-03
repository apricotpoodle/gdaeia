<?php
declare(strict_types=1);

namespace App\Model\Entity;

use Cake\ORM\Entity;

/**
 * User Entity
 *
 * @property int $id
 * @property string|null $username
 * @property string $email
 * @property string $password
 * @property string|null $firstname
 * @property string|null $lastname
 * @property string|null $token
 * @property bool $issuperuser
 * @property int $role_id
 * @property \Cake\I18n\DateTime|null $token_expires
 * @property \Cake\I18n\DateTime|null $deleted
 * @property \Cake\I18n\DateTime $created
 * @property \Cake\I18n\DateTime|null $modified
 *
 * @property \App\Model\Entity\Role $role
 * @property \App\Model\Entity\Applicationform[] $applicationforms
 * @property \App\Model\Entity\Urd[] $urds
 * @property \App\Model\Entity\UserDepartment[] $user_departments
 * @property \App\Model\Entity\Validation[] $validations
 */
class User extends Entity
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
        'username' => true,
        'email' => true,
        'password' => true,
        'firstname' => true,
        'lastname' => true,
        'token' => true,
        'issuperuser' => true,
        'role_id' => true,
        'token_expires' => true,
        'deleted' => true,
        'created' => true,
        'modified' => true,
        'role' => true,
        'applicationforms' => true,
        'urds' => true,
        'user_departments' => true,
        'validations' => true,
    ];

    /**
     * Fields that are excluded from JSON versions of the entity.
     *
     * @var array<string>
     */
    protected array $_hidden = [
        'password',
        'token',
    ];
}

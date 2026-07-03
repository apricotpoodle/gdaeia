<?php
declare(strict_types=1);

namespace App\Model\Entity;

use Cake\ORM\Entity;

/**
 * Validationstatus Entity
 *
 * @property int $id
 * @property string $code
 * @property string|null $name
 * @property \Cake\I18n\DateTime|null $deleted
 * @property \Cake\I18n\DateTime|null $modified
 * @property \Cake\I18n\DateTime $created
 *
 * @property \App\Model\Entity\Applicationformstatus[] $applicationformstatuses
 * @property \App\Model\Entity\Applicationvalidationstep[] $applicationvalidationsteps
 * @property \App\Model\Entity\Currentvalidationrole[] $currentvalidationroles
 * @property \App\Model\Entity\Validation[] $validations
 */
class Validationstatus extends Entity
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
        'code' => true,
        'name' => true,
        'deleted' => true,
        'modified' => true,
        'created' => true,
        'applicationformstatuses' => true,
        'applicationvalidationsteps' => true,
        'currentvalidationroles' => true,
        'validations' => true,
    ];
}

<?php
declare(strict_types=1);

namespace App\Model\Entity;

use Cake\ORM\Entity;

/**
 * CgrCode Entity
 *
 * @property int $id
 * @property int $department_id
 * @property string $type
 * @property string $code
 * @property string $label
 * @property bool $active
 * @property bool $is_system
 * @property \Cake\I18n\DateTime $created
 * @property \Cake\I18n\DateTime|null $modified
 *
 * @property \App\Model\Entity\Department $department
 * @property \App\Model\Entity\CgrCode[] $cgr_codes
 */
class CgrCode extends Entity
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
        'type' => true,
        'code' => true,
        'label' => true,
        'active' => true,
        'is_system' => true,
        'created' => true,
        'modified' => true,
        'department' => true,
        'cgr_codes' => true,
    ];
}

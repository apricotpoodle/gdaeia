<?php
declare(strict_types=1);

namespace App\Model\Entity;

use Cake\ORM\Entity;

/**
 * Contracttype Entity
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
 * @property \App\Model\Entity\Applicationform[] $applicationforms
 */
class Contracttype extends Entity
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
        'applicationforms' => true,
    ];
}

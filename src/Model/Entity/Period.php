<?php
declare(strict_types=1);

namespace App\Model\Entity;

use Cake\ORM\Entity;

/**
 * Period Entity
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
class Period extends Entity
{
    public const CODE_EUR_PER_DAY = 'EUR_PER_DAY';
    public const CODE_K_PER_MONTH = 'K_PER_MONTH';
    public const CODE_EUR_PER_MONTH = 'EUR_PER_MONTH';
    public const CODE_EUR_PER_HOUR = 'EUR_PER_HOUR';

    public const CODES = [
        self::CODE_EUR_PER_DAY,
        self::CODE_K_PER_MONTH,
        self::CODE_EUR_PER_MONTH,
        self::CODE_EUR_PER_HOUR,
    ];

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
        self::FIELD_BASE => true,
        self::FIELD_CODE => true,
        self::FIELD_NAME => true,
        self::FIELD_SORT => true,
        self::FIELD_DELETED => true,
        self::FIELD_CREATED => true,
        self::FIELD_MODIFIED => true,
        self::FIELD_APPLICATIONFORMS => true,
    ];

    public const FIELD_ID = 'id';
    public const FIELD_BASE = 'base';
    public const FIELD_CODE = 'code';
    public const FIELD_NAME = 'name';
    public const FIELD_SORT = 'sort';
    public const FIELD_DELETED = 'deleted';
    public const FIELD_CREATED = 'created';
    public const FIELD_MODIFIED = 'modified';
    public const FIELD_APPLICATIONFORMS = 'applicationforms';
}

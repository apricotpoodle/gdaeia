<?php
declare(strict_types=1);

namespace App\Model\Entity;

use Cake\ORM\Entity;

/**
 * Commentaire prédéfini proposé lors d'un vote de validation.
 *
 * @property int $id
 * @property string $decision
 * @property string $label
 * @property string $content
 * @property int $position
 * @property bool $active
 * @property \Cake\I18n\DateTime $created
 * @property \Cake\I18n\DateTime $modified
 */
final class ValidationCommentTemplate extends Entity
{
    public const FIELD_ID = 'id';
    public const FIELD_DECISION = 'decision';
    public const FIELD_LABEL = 'label';
    public const FIELD_CONTENT = 'content';
    public const FIELD_POSITION = 'position';
    public const FIELD_ACTIVE = 'active';
    public const FIELD_CREATED = 'created';
    public const FIELD_MODIFIED = 'modified';

    /**
     * @var array<string, bool>
     */
    protected array $_accessible = [
        'decision' => true,
        'label' => true,
        'content' => true,
        'position' => true,
        'active' => true,
        'created' => true,
        'modified' => true,
    ];
}

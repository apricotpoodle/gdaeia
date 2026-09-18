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

<?php
declare(strict_types=1);

namespace App\Model\Entity;

use Cake\ORM\Entity;

/**
 * Comment Entity
 *
 * @property int $id
 * @property int|null $parent_id
 * @property string $model
 * @property int $foreign_key
 * @property string $type
 * @property string $content
 * @property int $user_id
 * @property \Cake\I18n\DateTime $created
 * @property \Cake\I18n\DateTime|null $modified
 *
 * @property \App\Model\Entity\Comment|null $parent_comment
 * @property \App\Model\Entity\Comment[] $child_comments
 * @property \App\Model\Entity\User $user
 */
class Comment extends Entity
{
    protected array $_accessible = [
        'parent_id' => true,
        'model' => true,
        'foreign_key' => true,
        'type' => true,
        'content' => true,
        'user_id' => true,
        'created' => true,
        'modified' => true,
        'parent_comment' => true,
        'child_comments' => true,
        'user' => true,
    ];
}

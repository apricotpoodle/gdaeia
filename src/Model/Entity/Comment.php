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

    public const FIELD_ID = 'id';
    public const FIELD_PARENT_ID = 'parent_id';
    public const FIELD_MODEL = 'model';
    public const FIELD_FOREIGN_KEY = 'foreign_key';
    public const FIELD_TYPE = 'type';
    public const FIELD_CONTENT = 'content';
    public const FIELD_USER_ID = 'user_id';
    public const FIELD_CREATED = 'created';
    public const FIELD_MODIFIED = 'modified';
    public const FIELD_PARENT_COMMENT = 'parent_comment';
    public const FIELD_CHILD_COMMENTS = 'child_comments';
    public const FIELD_USER = 'user';
}

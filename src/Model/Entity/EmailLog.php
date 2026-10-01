<?php
declare(strict_types=1);

namespace App\Model\Entity;

use Cake\ORM\Entity;

/**
 * EmailLog Entity
 *
 * @property int $id
 * @property string $subject
 * @property string|null $content_text
 * @property string|null $content_html
 * @property string|null $error_message
 * @property \Cake\I18n\DateTime|null $created
 * @property \Cake\I18n\DateTime|null $modified
 *
 * @property \App\Model\Entity\EmailRecipient[] $email_recipients
 */
class EmailLog extends Entity
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
        'subject' => true,
        'content_text' => true,
        'content_html' => true,
        'error_message' => true,
        'created' => true,
        'modified' => true,
        'email_recipients' => true,
    ];

    public const FIELD_ID = 'id';
    public const FIELD_SUBJECT = 'subject';
    public const FIELD_CONTENT_TEXT = 'content_text';
    public const FIELD_CONTENT_HTML = 'content_html';
    public const FIELD_ERROR_MESSAGE = 'error_message';
    public const FIELD_CREATED = 'created';
    public const FIELD_MODIFIED = 'modified';
    public const FIELD_EMAIL_RECIPIENTS = 'email_recipients';
}

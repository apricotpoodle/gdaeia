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
}

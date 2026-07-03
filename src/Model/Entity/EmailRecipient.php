<?php
declare(strict_types=1);

namespace App\Model\Entity;

use Cake\ORM\Entity;

/**
 * EmailRecipient Entity
 *
 * @property int $id
 * @property int $email_log_id
 * @property string $recipient_email
 *
 * @property \App\Model\Entity\EmailLog $email_log
 */
class EmailRecipient extends Entity
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
        'email_log_id' => true,
        'recipient_email' => true,
        'email_log' => true,
    ];
}

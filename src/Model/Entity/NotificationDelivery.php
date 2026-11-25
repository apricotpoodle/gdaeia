<?php
declare(strict_types=1);

namespace App\Model\Entity;

use Cake\ORM\Entity;

/** Tentative d’expédition d’une notification GDAE. */
final class NotificationDelivery extends Entity
{
    public const FIELD_ID = 'id';
    public const FIELD_IDEMPOTENCY_KEY = 'idempotency_key';
    public const FIELD_NOTIFICATION_TYPE = 'notification_type';
    public const FIELD_RECIPIENT_HASH = 'recipient_hash';
    public const FIELD_STATE = 'state';
    public const FIELD_ATTEMPT_COUNT = 'attempt_count';
    public const FIELD_CLAIMED_AT = 'claimed_at';
    public const FIELD_LAST_ATTEMPT_AT = 'last_attempt_at';
    public const FIELD_SENT_AT = 'sent_at';
    public const FIELD_CREATED = 'created';
    public const FIELD_MODIFIED = 'modified';

    public const STATE_IN_PROGRESS = 'en_cours';
    public const STATE_SENT = 'envoyee';
    public const STATE_FAILED = 'echec';

    /**
     * @var array<string, bool>
     */
    protected array $_accessible = [
        self::FIELD_IDEMPOTENCY_KEY => true,
        self::FIELD_NOTIFICATION_TYPE => true,
        self::FIELD_RECIPIENT_HASH => true,
        self::FIELD_STATE => true,
        self::FIELD_ATTEMPT_COUNT => true,
        self::FIELD_CLAIMED_AT => true,
        self::FIELD_LAST_ATTEMPT_AT => true,
        self::FIELD_SENT_AT => true,
        self::FIELD_CREATED => true,
        self::FIELD_MODIFIED => true,
    ];
}

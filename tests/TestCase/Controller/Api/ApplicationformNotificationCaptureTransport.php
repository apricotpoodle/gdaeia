<?php
declare(strict_types=1);

namespace App\Test\TestCase\Controller\Api;

use Cake\Mailer\AbstractTransport;
use Cake\Mailer\Message;

/** Transport de capture utilisé par les tests de notification des DAE. */
final class ApplicationformNotificationCaptureTransport extends AbstractTransport
{
    /**
     * @var list<string>
     */
    public static array $recipients = [];

    /** @param \Cake\Mailer\Message $message */
    public function send(Message $message): array
    {
        self::$recipients = [...self::$recipients, ...array_keys($message->getTo())];

        return ['headers' => '', 'message' => ''];
    }
}

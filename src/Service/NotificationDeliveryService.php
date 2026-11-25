<?php
declare(strict_types=1);

namespace App\Service;

use App\Model\Entity\User;
use App\Model\Table\NotificationDeliveriesTable;
use Closure;
use Throwable;

/** Réserve et suit les courriels envoyés aux destinataires GDAE. */
final class NotificationDeliveryService
{
    /** Crée le service avec le registre persistant des livraisons. */
    public function __construct(private NotificationDeliveriesTable $deliveries)
    {
    }

    /** Envoie une fois par événement et destinataire normalisés. */
    public function sendOnce(string $type, string $eventKey, User $recipient, Closure $send): bool
    {
        $email = $recipient->get(User::FIELD_EMAIL);
        $normalizedEmail = is_string($email) ? strtolower(trim($email)) : '';
        if ($normalizedEmail === '') {
            return false;
        }

        $key = hash('sha256', $type . "\0" . $eventKey . "\0" . $normalizedEmail);
        $recipientHash = hash('sha256', $normalizedEmail);
        $claim = $this->deliveries->claim($key, $type, $recipientHash, 900);
        if ($claim === null) {
            return $this->deliveries->isSent($key);
        }

        try {
            $sent = $send();
        } catch (Throwable) {
            $sent = false;
        }
        $this->deliveries->complete($claim, $sent);

        return $sent;
    }
}

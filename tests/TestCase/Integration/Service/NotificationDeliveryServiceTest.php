<?php
declare(strict_types=1);

namespace App\Test\TestCase\Integration\Service;

use App\Model\Entity\User;
use App\Service\NotificationDeliveryService;
use Cake\ORM\TableRegistry;
use Cake\TestSuite\TestCase;

/** Vérifie l’idempotence des expéditions de notifications sur MySQL. */
class NotificationDeliveryServiceTest extends TestCase
{
    /**
     * @var array<string>
     */
    protected array $fixtures = ['app.NotificationDeliveries'];

    /** Un succès confirmé n’appelle plus le transport lors d’une relance. */
    public function testNeRenvoiePasUnCourrielDejaAccepte(): void
    {
        $deliveries = TableRegistry::getTableLocator()->get('NotificationDeliveries');
        $service = new NotificationDeliveryService($deliveries);
        $recipient = new User(['email' => ' Admin@Example.Test ']);
        $eventKey = '2026-W48-' . bin2hex(random_bytes(8));
        $sendCount = 0;

        $firstResult = $service->sendOnce(
            'accepted_digest',
            $eventKey,
            $recipient,
            static function () use (&$sendCount): bool {
                $sendCount++;

                return true;
            },
        );
        $secondResult = $service->sendOnce(
            'accepted_digest',
            $eventKey,
            $recipient,
            static function () use (&$sendCount): bool {
                $sendCount++;

                return true;
            },
        );

        $this->assertTrue($firstResult);
        $this->assertTrue($secondResult);
        $this->assertSame(1, $sendCount);
        $this->assertTrue($deliveries->isSent(hash(
            'sha256',
            "accepted_digest\0" . $eventKey . "\0admin@example.test",
        )));
    }

    /** Un échec SMTP reste enregistrable et ne conserve pas l’adresse en clair. */
    public function testEnregistreUnEchecSansAdresseEnClair(): void
    {
        $deliveries = TableRegistry::getTableLocator()->get('NotificationDeliveries');
        $service = new NotificationDeliveryService($deliveries);
        $eventKey = '2026-11-25-' . bin2hex(random_bytes(8));

        $sent = $service->sendOnce(
            'pending_digest',
            $eventKey,
            new User(['email' => 'Destinataire@Example.Test']),
            static fn(): bool => false,
        );

        $key = hash('sha256', "pending_digest\0" . $eventKey . "\0destinataire@example.test");
        $row = $deliveries->find()->where(['idempotency_key' => $key])->firstOrFail();
        $this->assertFalse($sent);
        $this->assertSame('echec', $row->get('state'));
        $this->assertSame(hash('sha256', 'destinataire@example.test'), $row->get('recipient_hash'));
        $this->assertStringNotContainsString('Destinataire@Example.Test', serialize($row->toArray()));
    }

    /** Une relance après échec rappelle le transport et confirme le succès. */
    public function testReessaieUnEchecEtEnregistreLeSucces(): void
    {
        $deliveries = TableRegistry::getTableLocator()->get('NotificationDeliveries');
        $service = new NotificationDeliveryService($deliveries);
        $recipient = new User(['email' => 'destinataire@example.test']);
        $eventKey = '2026-11-25-' . bin2hex(random_bytes(8));
        $sendCount = 0;

        $firstResult = $service->sendOnce(
            'pending_digest',
            $eventKey,
            $recipient,
            static function () use (&$sendCount): bool {
                $sendCount++;

                return false;
            },
        );
        $secondResult = $service->sendOnce(
            'pending_digest',
            $eventKey,
            $recipient,
            static function () use (&$sendCount): bool {
                $sendCount++;

                return true;
            },
        );

        $key = hash('sha256', "pending_digest\0" . $eventKey . "\0destinataire@example.test");
        $row = $deliveries->find()->where(['idempotency_key' => $key])->firstOrFail();
        $this->assertFalse($firstResult);
        $this->assertTrue($secondResult);
        $this->assertSame(2, $sendCount);
        $this->assertSame(2, (int)$row->get('attempt_count'));
        $this->assertSame('envoyee', $row->get('state'));
    }
}

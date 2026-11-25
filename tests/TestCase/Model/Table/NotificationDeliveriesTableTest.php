<?php
declare(strict_types=1);

namespace App\Test\TestCase\Model\Table;

use Cake\Database\Connection;
use Cake\Database\Exception\QueryException;
use Cake\Datasource\ConnectionManager;
use Cake\I18n\DateTime;
use Cake\ORM\TableRegistry;
use Cake\TestSuite\TestCase;
use LogicException;

/** Vérifie les contraintes de persistance du registre des notifications. */
class NotificationDeliveriesTableTest extends TestCase
{
    /**
     * @var array<string>
     */
    protected array $fixtures = ['app.NotificationDeliveries'];

    /** Le registre conserve seulement les empreintes des destinataires. */
    public function testEnregistreUneLivraisonSansAdresseEnClair(): void
    {
        $deliveries = TableRegistry::getTableLocator()->get('NotificationDeliveries');
        $recipientHash = hash('sha256', 'destinataire@example.test');
        $delivery = $deliveries->newEntity([
            'idempotency_key' => hash('sha256', 'pending:2026-11-25:' . $recipientHash),
            'notification_type' => 'pending_digest',
            'recipient_hash' => $recipientHash,
            'state' => 'en_cours',
            'attempt_count' => 1,
            'claimed_at' => '2026-11-25 07:00:00',
            'last_attempt_at' => '2026-11-25 07:00:00',
        ]);

        $saved = $deliveries->saveOrFail($delivery);

        $this->assertSame(64, strlen((string)$saved->get('idempotency_key')));
        $this->assertSame($recipientHash, $saved->get('recipient_hash'));
        $this->assertStringNotContainsString('destinataire@example.test', serialize($saved->toArray()));
    }

    /** Une clé d’idempotence ne peut identifier deux livraisons. */
    public function testRefuseUneCleDIdempotenceDupliquee(): void
    {
        $connection = ConnectionManager::get('test');
        $key = hash('sha256', __METHOD__ . bin2hex(random_bytes(8)));
        $this->insertDelivery($connection, $key);

        $this->expectException(QueryException::class);
        $this->insertDelivery($connection, $key);
    }

    /** Une claim active ne doit pas pouvoir être réservée une seconde fois. */
    public function testRefuseUneDeuxiemeClaimPendantLeBail(): void
    {
        $deliveries = TableRegistry::getTableLocator()->get('NotificationDeliveries');
        $key = hash('sha256', __METHOD__ . bin2hex(random_bytes(8)));
        $recipientHash = hash('sha256', 'destinataire@example.test');

        $firstClaim = $deliveries->claim($key, 'pending_digest', $recipientHash, 900);
        $secondClaim = $deliveries->claim($key, 'pending_digest', $recipientHash, 900);

        $this->assertNotNull($firstClaim);
        $this->assertSame(1, (int)$firstClaim->get('attempt_count'));
        $this->assertNull($secondClaim);
        $this->assertSame(1, (int)$deliveries->get($firstClaim->id)->get('attempt_count'));
    }

    /** Une clé existante ne peut être réutilisée pour un autre destinataire. */
    public function testRefuseUneCollisionDeCleAvecUnAutreDestinataire(): void
    {
        $deliveries = TableRegistry::getTableLocator()->get('NotificationDeliveries');
        $key = hash('sha256', __METHOD__ . bin2hex(random_bytes(8)));
        $deliveries->claim($key, 'pending_digest', hash('sha256', 'premier@example.test'), 900);

        $this->expectException(LogicException::class);
        $deliveries->claim($key, 'pending_digest', hash('sha256', 'second@example.test'), 900);
    }

    /** Une livraison échouée peut être réclamée et incrémente ses tentatives. */
    public function testReclameUneLivraisonApresUnEchec(): void
    {
        $deliveries = TableRegistry::getTableLocator()->get('NotificationDeliveries');
        $key = hash('sha256', __METHOD__ . bin2hex(random_bytes(8)));
        $recipientHash = hash('sha256', 'destinataire@example.test');
        $firstClaim = $deliveries->claim($key, 'pending_digest', $recipientHash, 900);
        $this->assertNotNull($firstClaim);
        $deliveries->complete($firstClaim, false);

        $retry = $deliveries->claim($key, 'pending_digest', $recipientHash, 900);

        $this->assertNotNull($retry);
        $this->assertSame(2, (int)$retry->get('attempt_count'));
        $this->assertSame('en_cours', $retry->get('state'));
    }

    /** Un succès ne peut être réclamé une seconde fois. */
    public function testNeReclamePasUneLivraisonDejaEnvoyee(): void
    {
        $deliveries = TableRegistry::getTableLocator()->get('NotificationDeliveries');
        $key = hash('sha256', __METHOD__ . bin2hex(random_bytes(8)));
        $recipientHash = hash('sha256', 'admin@example.test');
        $claim = $deliveries->claim($key, 'accepted_digest', $recipientHash, 900);
        $this->assertNotNull($claim);
        $deliveries->complete($claim, true);

        $retry = $deliveries->claim($key, 'accepted_digest', $recipientHash, 900);

        $this->assertTrue($deliveries->isSent($key));
        $this->assertNull($retry);
        $this->assertSame(1, (int)$deliveries->get($claim->id)->get('attempt_count'));
    }

    /** Une claim expirée peut être reprise après la durée configurée. */
    public function testReclameUneClaimExpiree(): void
    {
        $deliveries = TableRegistry::getTableLocator()->get('NotificationDeliveries');
        $connection = ConnectionManager::get('test');
        $key = hash('sha256', __METHOD__ . bin2hex(random_bytes(8)));
        $recipientHash = hash('sha256', 'destinataire@example.test');
        $claim = $deliveries->claim($key, 'pending_digest', $recipientHash, 900);
        $this->assertNotNull($claim);
        $connection->update('notification_deliveries', [
            'claimed_at' => DateTime::now()->subMinutes(16)->format('Y-m-d H:i:s'),
        ], ['id' => $claim->id]);

        $retry = $deliveries->claim($key, 'pending_digest', $recipientHash, 900);

        $this->assertNotNull($retry);
        $this->assertSame(2, (int)$retry->get('attempt_count'));
    }

    /** Insère directement un registre pour exercer la contrainte MySQL unique. */
    private function insertDelivery(Connection $connection, string $key): void
    {
        $connection->insert('notification_deliveries', [
            'idempotency_key' => $key,
            'notification_type' => 'accepted_digest',
            'recipient_hash' => hash('sha256', 'admin@example.test'),
            'state' => 'envoyee',
            'attempt_count' => 1,
            'claimed_at' => '2026-11-24 07:00:00',
            'last_attempt_at' => '2026-11-24 07:00:00',
            'sent_at' => '2026-11-24 07:00:01',
            'created' => '2026-11-24 07:00:00',
            'modified' => '2026-11-24 07:00:01',
        ]);
    }
}

<?php
declare(strict_types=1);

use Migrations\BaseMigration;

/** Persiste l’idempotence des notifications sans conserver leur contenu. */
final class CreateNotificationDeliveries extends BaseMigration
{
    /** Crée le registre des livraisons de courriels. */
    public function up(): void
    {
        $this->table('notification_deliveries', ['id' => false])
            ->addColumn('id', 'integer', ['autoIncrement' => true, 'null' => false, 'signed' => false])
            ->addPrimaryKey(['id'])
            ->addColumn('idempotency_key', 'string', ['limit' => 64, 'null' => false])
            ->addColumn('notification_type', 'string', ['limit' => 32, 'null' => false])
            ->addColumn('recipient_hash', 'string', ['limit' => 64, 'null' => false])
            ->addColumn('state', 'string', ['limit' => 16, 'null' => false])
            ->addColumn('attempt_count', 'integer', ['null' => false, 'default' => 0, 'signed' => false])
            ->addColumn('claimed_at', 'datetime', ['null' => true])
            ->addColumn('last_attempt_at', 'datetime', ['null' => true])
            ->addColumn('sent_at', 'datetime', ['null' => true])
            ->addColumn('created', 'datetime', ['null' => false])
            ->addColumn('modified', 'datetime', ['null' => false])
            ->addIndex(['idempotency_key'], [
                'unique' => true,
                'name' => 'notification_deliveries_key_un',
            ])
            ->addIndex(['state', 'claimed_at'], ['name' => 'notification_deliveries_claim_idx'])
            ->create();
    }

    /** Supprime uniquement le registre introduit par cette migration. */
    public function down(): void
    {
        $this->table('notification_deliveries')->drop()->save();
    }
}

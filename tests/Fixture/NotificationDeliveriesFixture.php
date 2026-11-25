<?php
declare(strict_types=1);

namespace App\Test\Fixture;

use Cake\Database\Schema\TableSchema;
use Cake\TestSuite\Fixture\TestFixture;

/** Jeu de données isolé pour les tentatives d’expédition des notifications. */
class NotificationDeliveriesFixture extends TestFixture
{
    /**
     * @var list<array<string, mixed>>
     */
    public array $records = [];

    /** Définit le schéma de test sans dépendre de l’ordre d’exécution des migrations. */
    public function init(): void
    {
        $this->table = 'notification_deliveries';
        $this->tableAlias = 'NotificationDeliveries';

        $schema = new TableSchema($this->table);
        $schema
            ->addColumn('id', [
                'type' => 'integer',
                'null' => false,
                'unsigned' => true,
                'autoIncrement' => true,
            ])
            ->addColumn('idempotency_key', ['type' => 'string', 'length' => 64, 'null' => false])
            ->addColumn('notification_type', ['type' => 'string', 'length' => 32, 'null' => false])
            ->addColumn('recipient_hash', ['type' => 'string', 'length' => 64, 'null' => false])
            ->addColumn('state', ['type' => 'string', 'length' => 16, 'null' => false])
            ->addColumn('attempt_count', ['type' => 'integer', 'unsigned' => true, 'default' => 0, 'null' => false])
            ->addColumn('claimed_at', ['type' => 'datetime', 'null' => true])
            ->addColumn('last_attempt_at', ['type' => 'datetime', 'null' => true])
            ->addColumn('sent_at', ['type' => 'datetime', 'null' => true])
            ->addColumn('created', ['type' => 'datetime', 'null' => false])
            ->addColumn('modified', ['type' => 'datetime', 'null' => false])
            ->addConstraint('PRIMARY', ['type' => 'primary', 'columns' => ['id']])
            ->addConstraint('notification_deliveries_key_un', [
                'type' => 'unique',
                'columns' => ['idempotency_key'],
            ])
            ->addIndex('notification_deliveries_claim_idx', [
                'type' => 'index',
                'columns' => ['state', 'claimed_at'],
            ]);

        $this->_schema = $schema;
    }
}

<?php
declare(strict_types=1);

namespace App\Model\Table;

use App\Model\Entity\NotificationDelivery;
use Cake\Database\Exception\QueryException;
use Cake\Database\Expression\QueryExpression;
use Cake\I18n\DateTime;
use Cake\ORM\Table;
use Cake\Validation\Validator;
use LogicException;
use PDOException;
use RuntimeException;

/** Registre idempotent des notifications envoyées par l’application. */
/**
 * @extends \Cake\ORM\Table<array{Timestamp: \Cake\ORM\Behavior\TimestampBehavior}, \App\Model\Entity\NotificationDelivery>
 * @method \App\Model\Entity\NotificationDelivery newEntity(array<string, mixed> $data, array<string, mixed> $options = [])
 * @method \App\Model\Entity\NotificationDelivery saveOrFail(\Cake\Datasource\EntityInterface $entity, array<string, mixed> $options = [])
 * @method \App\Model\Entity\NotificationDelivery get(mixed $primaryKey, array<string, mixed>|string $finder = 'all', \Psr\SimpleCache\CacheInterface|string|null $cache = null, \Closure|string|null $cacheKey = null, mixed ...$args)
 * @mixin \Cake\ORM\Behavior\TimestampBehavior
 */
final class NotificationDeliveriesTable extends Table
{
    /** @inheritDoc */
    public function initialize(array $config): void
    {
        parent::initialize($config);
        $this->setTable('notification_deliveries');
        $this->setPrimaryKey(NotificationDelivery::FIELD_ID);
        $this->setEntityClass(NotificationDelivery::class);
        $this->addBehavior('Timestamp');
    }

    /** @inheritDoc */
    public function validationDefault(Validator $validator): Validator
    {
        return $validator
            ->scalar(NotificationDelivery::FIELD_IDEMPOTENCY_KEY)
            ->maxLength(NotificationDelivery::FIELD_IDEMPOTENCY_KEY, 64)
            ->notEmptyString(NotificationDelivery::FIELD_IDEMPOTENCY_KEY)
            ->scalar(NotificationDelivery::FIELD_NOTIFICATION_TYPE)
            ->maxLength(NotificationDelivery::FIELD_NOTIFICATION_TYPE, 32)
            ->notEmptyString(NotificationDelivery::FIELD_NOTIFICATION_TYPE)
            ->scalar(NotificationDelivery::FIELD_RECIPIENT_HASH)
            ->maxLength(NotificationDelivery::FIELD_RECIPIENT_HASH, 64)
            ->notEmptyString(NotificationDelivery::FIELD_RECIPIENT_HASH)
            ->inList(NotificationDelivery::FIELD_STATE, [
                NotificationDelivery::STATE_IN_PROGRESS,
                NotificationDelivery::STATE_SENT,
                NotificationDelivery::STATE_FAILED,
            ])
            ->integer(NotificationDelivery::FIELD_ATTEMPT_COUNT)
            ->greaterThanOrEqual(NotificationDelivery::FIELD_ATTEMPT_COUNT, 0);
    }

    /** Réserve une livraison ou reprend une tentative dont l’état est réessayable. */
    public function claim(
        string $key,
        string $type,
        string $recipientHash,
        int $leaseSeconds,
    ): ?NotificationDelivery {
        $now = DateTime::now();
        $leaseSeconds = min(max($leaseSeconds, 1), 900);
        $delivery = $this->newEntity([
            NotificationDelivery::FIELD_IDEMPOTENCY_KEY => $key,
            NotificationDelivery::FIELD_NOTIFICATION_TYPE => $type,
            NotificationDelivery::FIELD_RECIPIENT_HASH => $recipientHash,
            NotificationDelivery::FIELD_STATE => NotificationDelivery::STATE_IN_PROGRESS,
            NotificationDelivery::FIELD_ATTEMPT_COUNT => 1,
            NotificationDelivery::FIELD_CLAIMED_AT => $now,
            NotificationDelivery::FIELD_LAST_ATTEMPT_AT => $now,
        ]);
        if ($delivery->getErrors() !== []) {
            throw new RuntimeException('Les données de livraison sont invalides.');
        }

        try {
            $this->getConnection()->insert(
                $this->getTable(),
                [
                    NotificationDelivery::FIELD_IDEMPOTENCY_KEY => $key,
                    NotificationDelivery::FIELD_NOTIFICATION_TYPE => $type,
                    NotificationDelivery::FIELD_RECIPIENT_HASH => $recipientHash,
                    NotificationDelivery::FIELD_STATE => NotificationDelivery::STATE_IN_PROGRESS,
                    NotificationDelivery::FIELD_ATTEMPT_COUNT => 1,
                    NotificationDelivery::FIELD_CLAIMED_AT => $now,
                    NotificationDelivery::FIELD_LAST_ATTEMPT_AT => $now,
                    NotificationDelivery::FIELD_CREATED => $now,
                    NotificationDelivery::FIELD_MODIFIED => $now,
                ],
                [
                    NotificationDelivery::FIELD_CLAIMED_AT => 'datetime',
                    NotificationDelivery::FIELD_LAST_ATTEMPT_AT => 'datetime',
                    NotificationDelivery::FIELD_CREATED => 'datetime',
                    NotificationDelivery::FIELD_MODIFIED => 'datetime',
                ],
            );
            $created = $this->find()->where([
                NotificationDelivery::FIELD_IDEMPOTENCY_KEY => $key,
            ])->first();
            if (!$created instanceof NotificationDelivery) {
                throw new RuntimeException('La livraison créée est introuvable.');
            }

            return $created;
        } catch (QueryException $exception) {
            $previous = $exception->getPrevious();
            if (!$previous instanceof PDOException || !$this->isDuplicateKeyViolation($previous)) {
                throw $exception;
            }
        }

        $existing = $this->find()
            ->where([NotificationDelivery::FIELD_IDEMPOTENCY_KEY => $key])
            ->first();
        if (!$existing instanceof NotificationDelivery) {
            throw new RuntimeException('La livraison concurrente est introuvable.');
        }
        if (
            $existing->get(NotificationDelivery::FIELD_NOTIFICATION_TYPE) !== $type
            || $existing->get(NotificationDelivery::FIELD_RECIPIENT_HASH) !== $recipientHash
        ) {
            throw new LogicException('La clé identifie déjà une autre livraison.');
        }

        $updated = $this->updateAll(
            [
                NotificationDelivery::FIELD_STATE => NotificationDelivery::STATE_IN_PROGRESS,
                NotificationDelivery::FIELD_ATTEMPT_COUNT => new QueryExpression('attempt_count + 1'),
                NotificationDelivery::FIELD_CLAIMED_AT => $now,
                NotificationDelivery::FIELD_LAST_ATTEMPT_AT => $now,
                NotificationDelivery::FIELD_SENT_AT => null,
                NotificationDelivery::FIELD_MODIFIED => $now,
            ],
            [
                NotificationDelivery::FIELD_ID => $existing->get(NotificationDelivery::FIELD_ID),
                NotificationDelivery::FIELD_ATTEMPT_COUNT => $existing->get(NotificationDelivery::FIELD_ATTEMPT_COUNT),
                'OR' => [
                    [NotificationDelivery::FIELD_STATE => NotificationDelivery::STATE_FAILED],
                    [
                        NotificationDelivery::FIELD_STATE => NotificationDelivery::STATE_IN_PROGRESS,
                        NotificationDelivery::FIELD_CLAIMED_AT . ' <=' => $now->subSeconds($leaseSeconds),
                    ],
                ],
            ],
        );

        if ($updated === 0) {
            return null;
        }

        return $this->get($existing->get(NotificationDelivery::FIELD_ID));
    }

    /** Indique si une livraison donnée a reçu un succès confirmé. */
    public function isSent(string $key): bool
    {
        return $this->exists([
            NotificationDelivery::FIELD_IDEMPOTENCY_KEY => $key,
            NotificationDelivery::FIELD_STATE => NotificationDelivery::STATE_SENT,
        ]);
    }

    /** Enregistre le résultat si cette tentative possède toujours le bail. */
    public function complete(NotificationDelivery $delivery, bool $sent): void
    {
        $now = DateTime::now();
        $this->updateAll(
            [
                NotificationDelivery::FIELD_STATE => $sent
                    ? NotificationDelivery::STATE_SENT
                    : NotificationDelivery::STATE_FAILED,
                NotificationDelivery::FIELD_SENT_AT => $sent ? $now : null,
                NotificationDelivery::FIELD_MODIFIED => $now,
            ],
            [
                NotificationDelivery::FIELD_ID => $delivery->get(NotificationDelivery::FIELD_ID),
                NotificationDelivery::FIELD_ATTEMPT_COUNT => $delivery->get(NotificationDelivery::FIELD_ATTEMPT_COUNT),
                NotificationDelivery::FIELD_STATE => NotificationDelivery::STATE_IN_PROGRESS,
            ],
        );
    }

    /** Détecte uniquement le doublon de clé unique MySQL. */
    private function isDuplicateKeyViolation(PDOException $exception): bool
    {
        return (int)($exception->errorInfo[1] ?? 0) === 1062;
    }
}

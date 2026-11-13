<?php
declare(strict_types=1);

namespace App\Model\Behavior;

use Cake\Datasource\EntityInterface;
use Cake\Event\EventInterface;
use Cake\ORM\Behavior;
use LogicException;

/** Protège les références de socle lors des écritures ORM ordinaires. */
class ProtectBaseRecordsBehavior extends Behavior
{
    /**
     * Refuse toute modification métier d'un socle, y compris pendant une bascule.
     *
     * @param \Cake\Event\EventInterface<\Cake\ORM\Table> $event Événement de sauvegarde.
     * @param \Cake\Datasource\EntityInterface $entity Référence à sauvegarder.
     * @return void
     */
    public function beforeSave(EventInterface $event, EntityInterface $entity): void
    {
        if ($entity->isNew()) {
            return;
        }

        $primaryKey = $this->primaryKey();
        if ($entity->isDirty($primaryKey) && $entity->get($primaryKey) !== $entity->getOriginal($primaryKey)) {
            $this->refuse($event, $entity, $primaryKey, 'L’identifiant d’une référence ne peut pas être modifié.');

            return;
        }

        $originalId = $entity->getOriginal($primaryKey);
        $persisted = $this->_table->find()
            ->select(['base'])
            ->where([$primaryKey => $originalId])
            ->first();
        if ($persisted === null) {
            return;
        }

        $currentBase = (bool)$persisted->get('base');
        $requestedBase = $entity->isDirty('base') ? (bool)$entity->get('base') : $currentBase;
        if (!$currentBase && !$requestedBase) {
            return;
        }

        foreach ($entity->getDirty() as $field) {
            if ($field === 'base') {
                continue;
            }

            if ($field === 'code') {
                $this->refuse($event, $entity, 'code', 'Le code d’une référence de socle ne peut pas être modifié.');
            } else {
                $this->refuse($event, $entity, $field, 'Une référence de socle ne peut pas être modifiée.');
            }

            return;
        }
    }

    /**
     * Refuse la suppression physique d'une référence de socle persistante.
     *
     * @param \Cake\Event\EventInterface<\Cake\ORM\Table> $event Événement de suppression.
     * @param \Cake\Datasource\EntityInterface $entity Référence à supprimer.
     * @return void
     */
    public function beforeDelete(EventInterface $event, EntityInterface $entity): void
    {
        $primaryKey = $this->primaryKey();
        $persisted = $this->_table->find()
            ->select(['base'])
            ->where([$primaryKey => $entity->getOriginal($primaryKey)])
            ->first();
        if ($persisted !== null && (bool)$persisted->get('base')) {
            $this->refuse($event, $entity, 'base', 'Une référence de socle ne peut pas être supprimée.');
        }
    }

    /**
     * Ajoute une erreur exploitable et interrompt l'opération ORM.
     *
     * @param \Cake\Event\EventInterface<\Cake\ORM\Table> $event Événement à interrompre.
     * @param \Cake\Datasource\EntityInterface $entity Référence refusée.
     * @param string $field Champ associé à l'erreur.
     * @param string $message Motif en français.
     * @return void
     */
    private function refuse(EventInterface $event, EntityInterface $entity, string $field, string $message): void
    {
        $entity->setError($field, ['protected' => $message]);
        $event->stopPropagation();
        $event->setResult(false);
    }

    /** Retourne la clé primaire simple des référentiels protégés. */
    private function primaryKey(): string
    {
        $primaryKey = $this->_table->getPrimaryKey();
        if (!is_string($primaryKey)) {
            throw new LogicException('La protection des références exige une clé primaire simple.');
        }

        return $primaryKey;
    }
}

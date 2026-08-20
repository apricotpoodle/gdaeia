<?php
declare(strict_types=1);

namespace App\Model\Table;

use App\Model\Entity\User;
use Cake\ORM\Query\SelectQuery;
use Cake\ORM\Table;

/**
 * @class AppTable
 * @description Socle d'infrastructure commun pour l'ensemble des classes Table de l'application.
 * Centralise de manière sécurisée et performante les Behaviors et configurations transverses.
 * @package App\Model\Table
 * @author L'Équipe de Développement
 * @class AppTable
 * @description Socle d'infrastructure commun pour l'ensemble des classes Table.
 * @package App\Model\Table
 */
class AppTable extends Table
{
    /**
     * Méthode d'initialisation globale.
     * Automatise les configurations communes sans altérer l'analyse statique (PHPStan).
     *
     * @param array<string, mixed> $config Le tableau de configuration de la Table.
     * @return void
     */
    public function initialize(array $config): void
    {
        parent::initialize($config);

        // 💡 Principe DRY : Si la table possède les colonnes temporelles standards,
        // on attache automatiquement le TimestampBehavior pour gérer 'created' et 'modified'.
        if ($this->getSchema()->hasColumn('created') && $this->getSchema()->hasColumn('modified')) {
            $this->addBehavior('Timestamp');
        }
    }

    /**
     * Custom finder générique 'visibleTo' pour les tables de nomenclature/référentiels.
     * Filtre les enregistrements non supprimés (soft delete) s'il existe une colonne 'deleted'.
     *
     * Utilisation : ->find('visibleTo', user: $currentUser)
     *
     * @param \Cake\ORM\Query\SelectQuery $query
     * @param \App\Model\Entity\User $user
     * @return \Cake\ORM\Query\SelectQuery
     */
    public function findVisibleTo(SelectQuery $query, User $user): SelectQuery
    {
        // Si la table possède une colonne 'deleted', on ne garde que les actifs
        if ($this->getSchema()->hasColumn('deleted')) {
            $query->where([$this->getAlias() . '.deleted IS' => null]);
        }

        return $query;
    }
}

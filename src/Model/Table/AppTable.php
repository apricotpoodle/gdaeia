<?php

declare(strict_types=1);

namespace App\Model\Table;

use Cake\ORM\Table;

/**
 * @class AppTable
 * @description Socle d'infrastructure commun pour l'ensemble des classes Table de l'application.
 * Centralise de manière sécurisée et performante les Behaviors et configurations transverses.
 * @package App\Model\Table
 * @author L'Équipe de Développement
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
}

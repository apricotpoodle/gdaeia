<?php

declare(strict_types=1);

namespace App\Model\Entity;

use Cake\ORM\Entity;

/**
 * Class AppEntity
 * * Classe parente abstraite pour toutes les entités de l'application.
 * Centralise les comportements globaux d'entreprise, notamment l'injection
 * automatique des permissions d'interface (UI) pour Tabulator.
 * * @package App\Model\Entity
 */
abstract class AppEntity extends Entity
{
    /**
     * Liste des propriétés virtuelles à exposer systématiquement lors de la conversion JSON.
     * * @var array<string>
     */
    protected array $_virtual = [
        '_ui_permissions',
    ];

    /**
     * Accesseur virtuel combinant les permissions des boutons d'actions et la visibilité des colonnes.
     * Déporte la logique de calcul vers des méthodes spécialisées surchargeables dans les entités enfants.
     * * @return array{actions: array<string, bool>, columns: array<string, bool>}
     */
    protected function _getUiPermissions(): array
    {
        return [
            'actions' => $this->getActionPermissions(),
            'columns' => $this->getColumnVisibility(),
        ];
    }

    /**
     * Calcule les droits sur les actions CRUD de la ligne.
     * À surcharger dans les entités enfants si une logique fine est requise.
     * * @return array<string, bool>
     */
    protected function getActionPermissions(): array
    {
        return [
            'view' => true,
            'edit' => true,
            'delete' => true,
        ];
    }

    /**
     * Calcule les droits de visibilité des colonnes pour cette entité.
     * À surcharger dans les entités enfants (ex: masquer les données sensibles).
     * * @return array<string, bool>
     */
    protected function getColumnVisibility(): array
    {
        return [];
    }
}

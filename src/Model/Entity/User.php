<?php

declare(strict_types=1);

namespace App\Model\Entity;

/**
 * Class User
 * * Spécification de l'entité User appliquant des règles chirurgicales
 * de contrôle d'accès sur ses lignes et ses colonnes sensibles.
 * * @package App\Model\Entity
 */
class User extends AppEntity
{
    /**
     * Chantiers d'accessibilité de masse par défaut.
     * * @var array<string, bool>
     */
    protected array $_accessible = [
        '*' => true,
        'id' => false,
    ];

    /**
     * Surcharge locale : Règle de gestion sur les boutons d'actions.
     * * @return array<string, bool>
     */
    protected function getActionPermissions(): array
    {
        // Exemple : On interdit la modification et suppression de l'administrateur racine (ID 1)
        return [
            'view' => true,
            'edit' => false, //($this->id !== 1),
            'delete' => ($this->id !== 1),
            'impersonate' => ($this->id !== 1),
        ];
    }

    /**
     * Surcharge locale : Contrôle d'accès à l'affichage des colonnes.
     * * @return array<string, bool>
     */
    protected function getColumnVisibility(): array
    {
        return [
            'email' => true,
            // Seule la Factory décidera de lire cette clé pour afficher/masquer
            'issuperuser' => ($this->id === 1 || $this->role_id === 1),
        ];
    }
}

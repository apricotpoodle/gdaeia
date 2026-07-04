<?php

declare(strict_types=1);

namespace App\Model\Entity;

/**
 * Class User
 * @package App\Model\Entity
 */
class User extends AppEntity
{
    protected array $_accessible = [
        '*' => true,
        'id' => false,
    ];

    /**
     * Application du scénario de test sur les boutons.
     * @return array<string, bool>
     */
    protected function getActionPermissions(): array
    {
        return [
            'view' => true,
            'edit' => ($this->id % 2 === 0), // Autorisé uniquement pour les IDs pairs
            'delete' => false,               // Désactivé pour tout le monde
        ];
    }

    /**
     * Application du scénario de test sur les colonnes.
     * @return array<string, bool>
     */
    protected function getColumnVisibility(): array
    {
        return [
            'email' => true,
            'issuperuser' => false, // On force le masquage de la colonne Super Admin
        ];
    }
}

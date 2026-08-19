<?php

declare(strict_types=1);

namespace App\Model\Entity;

use Cake\ORM\Entity;

/**
 * Class AppEntity
 * @package App\Model\Entity
 */
abstract class AppEntity extends Entity
{
    /**
     * 🆔 CARTOGRAPHIE DES RÔLES CRITIQUES (IDs de la base de données)
     * Centraliser ces IDs ici évite les nombres magiques dans toute l'application.
     */
    public const ROLE_ADMIN                     = 1;
    public const ROLE_DEMANDEUR                 = 2;
    public const ROLE_1_VALIDEUR_POLE           = 3;
    public const ROLE_2_VALIDEUR_RRH            = 4;
    public const ROLE_3_VALIDEUR_DRH            = 5;
    public const ROLE_4_VALIDEUR_CG             = 6;
    public const ROLE_5_VALIDEUR_DIR            = 7;

    // L'entité mère est désormais totalement propre et agnostique.
    // Elle servira uniquement à centraliser des méthodes utiles
    // pour toutes vos entités métiers, sans interférer avec l'API JSON.
    // /**
    //  * Spécifie les propriétés virtuelles à ajouter au payload JSON.
    //  * @var array<string>
    //  */
    // protected array $_virtual = [
    //     'grid_rights',
    // ];

    // /**
    //  * Accesseur obligatoire respectant le format CamelCase pour CakePHP.
    //  * Correspond à la propriété virtuelle 'grid_rights'.
    //  * @return array{actions: array<string, bool>, columns: array<string, bool>}
    //  */
    // protected function _getGridRights(): array
    // {
    //     return [
    //         'actions' => $this->getActionPermissions(),
    //         'columns' => $this->getColumnVisibility(),
    //     ];
    // }

    // /**
    //  * Droits par défaut sur les actions de ligne.
    //  * @return array<string, bool>
    //  */
    // protected function getActionPermissions(): array
    // {
    //     return [
    //         'view' => true,
    //         'edit' => true,
    //         'delete' => true,
    //     ];
    // }

    // /**
    //  * Visibilité par défaut des colonnes structurelles.
    //  * @return array<string, bool>
    //  */
    // protected function getColumnVisibility(): array
    // {
    //     return [];
    // }
}

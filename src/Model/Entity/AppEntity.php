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

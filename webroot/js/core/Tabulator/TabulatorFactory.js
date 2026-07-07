// ==============================================================================
// Fichier : webroot/js/core/Tabulator/TabulatorFactory.js
// Rôle : Centralisation de l'instanciation des composants grilles métiers
// ==============================================================================

import { TabulatorBuilder } from './TabulatorBuilder.js';
import { ColumnsFactory } from './ColumnsFactory.js';

export class TabulatorFactory {
    /**
     * Fabrique dédiée à la grille des Utilisateurs (USERS)
     */
    static createUsersGrid(selector) {
        return new TabulatorBuilder(selector)
            .setController('users')
            .setRemotePagination()
            .setWithActions()
            .setColumns([
                ColumnsFactory.id(),
                ColumnsFactory.text("username", "Identifiant", { frozen: true }),
                ColumnsFactory.text("email", "Adresse Email"),
                ColumnsFactory.boolean("issuperuser", "Administrateur"),
                ColumnsFactory.dateRange("created", "Date d'inscription"),
                ColumnsFactory.dateRange("modified", "Dernière modification")
            ])
            .build();
    }
}

// ==============================================================================
// Fichier : webroot/js/core/Tabulator/TabulatorFactory.js
// Rôle : Centralisation de l'instanciation des composants grilles métiers
// ==============================================================================

import { TabulatorBuilder } from './TabulatorBuilder.js';
import { ColumnsFactory } from './ColumnsFactory.js';

export class TabulatorFactory {

    /**
     * SOCLE COMMUN (DRY)
     * Définit le standard UX/UI de l'entreprise pour une grille de données classique.
     * @private
     */
    static _createBaseGrid(selector) {
        return new TabulatorBuilder(selector)
            .enableStatePersistence() // La norme de l'entreprise est imposée ici
            .setRemotePagination()
            ;
    }

    /**
     * SOCLE COMMUN (DRY)
     * Définit le standard UX/UI de l'entreprise pour une grille de données avec colonne Actions.
     * @private
     */
    static _createActionGrid(selector) {
        return this._createBaseGrid(selector)
            .setWithActions()
            ;
    }

    /**
     * Fabrique dédiée à la configuration de la grille des Utilisateurs (USERS).
     * @static
     * @param {string} selector - Le sélecteur CSS cible.
     * @returns {Object} L'instance du Builder de grille Tabulator.
     */
    static createUsersGrid(selector) {
        return this._createActionGrid(selector)
            .setAjaxSource('/api/users.json')
            .setController('users')
            .addActions(['impersonate'])
            .setColumns([
                ColumnsFactory.id({ visible: true }),
                ColumnsFactory.text("firstname", "prenom"),
                ColumnsFactory.text("lastname", "nom"),
                ColumnsFactory.text("username", "Identifiant", { frozen: true }),
                ColumnsFactory.text("email", "Adresse Email"),
                ColumnsFactory.boolean("issuperuser", "Administrateur"),
                ColumnsFactory.dateRange("created", "Date d'inscription"),
                ColumnsFactory.dateRange("modified", "Dernière modification")
            ])
            .build();
    }
}

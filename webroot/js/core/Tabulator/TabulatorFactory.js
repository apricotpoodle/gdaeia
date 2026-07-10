// ==============================================================================
// Fichier : webroot/js/core/Tabulator/TabulatorFactory.js
// Rôle : Centralisation de l'instanciation des composants grilles métiers
// ==============================================================================

import { TabulatorBuilder } from './TabulatorBuilder.js';
import { ColumnsFactory } from './ColumnsFactory.js';

/**
 * @class TabulatorFactory
 * @description Centralisation des fabriques de création de grilles Tabulator métiers.
 */
export class TabulatorFactory {

    /**
     * SOCLE COMMUN (DRY)
     * Définit le standard UX/UI de l'entreprise pour une grille de données classique.
     * Maintient impérativement le système de contrôle et la pagination en HAUT.
     * @private
     * @param {string} selector - Le sélecteur CSS de l'élément cible.
     * @returns {TabulatorBuilder} L'instance configurée du builder.
     */
    static _createBaseGrid(selector) {
        return new TabulatorBuilder(selector)
            .enableStatePersistence() // Mémorisation locale
            .setContinuousScroll(20)  // 💡 NOUVEAU STANDARD : Défilement infini par lots de 20
            // 💡 Applique une règle de base à toutes les colonnes de la table
            .setColumnDefaults({
                widthGrow: 1,
                tooltip: true // (Exemple : active les infobulles partout par défaut)
            })
            ;
    }

    /**
     * SOCLE COMMUN (DRY)
     * Définit le standard UX/UI de l'entreprise pour une grille de données avec colonne Actions.
     * @private
     * @param {string} selector - Le sélecteur CSS de l'élément cible.
     * @returns {TabulatorBuilder} L'instance configurée du builder.
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
     * @returns {Tabulator} L'instance finale de la grille Tabulator.
     */
    static createUsersGrid(selector) {
        return this._createActionGrid(selector)
            .setAjaxSource('/api/users.json')
            .setController('users')
            .addActions(['impersonate'])
            // .disablePagination() // < --- SUPPRIMÉ : On veut conserver les 20 lignes!
            // .setHeight("100%)") // 100% du parent flexbox
            // 💡 LA FENÊTRE : Il FAUT une hauteur absolue pour forcer l'ascenseur INTERNE.
            // Si Flexbox bugue, utilise un calc strict pour garantir l'apparition de l'ascenseur.
            .setHeight("calc(100vh - 180px)") // <--- AJOUT : Bloque la grille avant le bas de l'écran
            // .setHeight("calc(100vh - 220px)")
            // .setHeight("calc(100vh)")
            .setColumns([
                ColumnsFactory.id({ visible: true }),
                ColumnsFactory.text("firstname", "Prénom"),
                ColumnsFactory.text("lastname", "Nom"),
                ColumnsFactory.text("role.name", "Rôle"),
                // ColumnsFactory.text("username", "Identifiant", { frozen: true }),
                ColumnsFactory.text("email", "Adresse Email"),
                ColumnsFactory.boolean("issuperuser", "Administrateur"),
                // ColumnsFactory.dateRange("created", "Date d'inscription"),
                // ColumnsFactory.dateRange("modified", "Dernière modification")
            ])
            .build();
    }
}

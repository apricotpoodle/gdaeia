// ==============================================================================
// Fichier : webroot/js/core/Tabulator/ColumnsFactory.js
// Rôle : Fabrique sémantique de configurations de colonnes standardisées
// ==============================================================================

import { ColumnBuilder } from './ColumnBuilder.js';
import { ColumnFilters } from './ColumnFilters.js';

/**
 * @file ColumnsFactory.js
 * @description Fabrique d'infrastructure pour standardiser et compiler les colonnes Tabulator.
 * Compatible JSDoc avancée.
 */

export class ColumnsFactory {
    /**
     * Génère la colonne ID configurée avec filtres et tris numériques.
     * Masquée par défaut, activable instantanément via les overrides.
     *
     * @static
     * @param {Object} [overrides={}] - Surcharges de configuration (ex: { visible: true })
     * @param {string} [field="id"] - Nom de la clé primaire en base de données.
     * @returns {Object} Configuration brute de colonne pour l'API Tabulator.
     */
    static id(overrides = {}, field = "id") {
        return new ColumnBuilder(field, "ID")
            .setHozAlign("center")
            .setSorter("number")
            .setHeaderFilter("number")
            .setOptions(
                {
                    visible: false, // Masqué par défaut
                    width: 70,      // Largeur standard pour les ids
                    ...overrides    // Application des surcharges utilisateur
                }
            )
            .build();
    }


    /**
     * Génère une colonne de texte standard avec tri Tristate inclus.
     *
     * @static
     * @param {string} field - Champ de l'entité JSON.
     * @param {string} title - Libellé de l'en-tête.
     * @param {Object} [overrides={}] - Options complémentaires (ex: { frozen: true })
     * @returns {Object}
     */
    static text(field, title, overrides = {}) {
        return new ColumnBuilder(field, title)
            .setSorter("string")
            .setHozAlign("left")
            .setHeaderFilter("input")
            .setOptions(overrides)
            .build();
    }

    /**
     * Génère une colonne de texte standard avec tri Tristate inclus.
     *
     * @static
     * @param {string} field - Champ de l'entité JSON.
     * @param {string} title - Libellé de l'en-tête.
     * @param {Object} [overrides={}] - Options complémentaires (ex: { frozen: true })
     * @returns {Object}
     */
    static boolean(field, title, overrides = {}) {
        return new ColumnBuilder(field, title)
            .setSorter("boolean")
            .setHozAlign("center")
            .setFormatter("tickCross")
            .setHeaderFilter("list", {
                values: { "true": "Oui", "false": "Non", "": "Tous" }
            })
            .setOptions(overrides)
            .build();
    }

    /**
     * Génère une colonne de texte standard avec tri Tristate inclus.
     *
     * @static
     * @param {string} field - Champ de l'entité JSON.
     * @param {string} title - Libellé de l'en-tête.
     * @param {Object} [overrides={}] - Options complémentaires (ex: { frozen: true })
     * @returns {Object}
     */
    static dateRange(field, title, overrides = {}) {
        return new ColumnBuilder(field, title)
            .setSorter("date")
            .setHozAlign("center")
            .setLiveFilter(false)
            .setHeaderFilter(ColumnFilters.dateRangeEditor)
            // Indique à Tabulator qu'une chaîne vide "" signifie "filtre inactif"
            .setHeaderFilterEmptyCheck(function (value) {
                return value === "" || value === null || value === undefined;
            })
            .setFormatter((cell) => {
                const value = cell.getValue();
                if (!value) return "-";
                return new Date(value).toLocaleDateString('fr-FR', {
                    year: 'numeric', month: '2-digit', day: '2-digit'
                });
            })
            .setOptions(overrides)
            .build();
    }
}

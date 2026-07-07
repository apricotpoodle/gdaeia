// webroot/js/core/Tabulator/ColumnsFactory.js

import { ColumnBuilder } from './ColumnBuilder.js';
import { dateRangeFilterEditor } from './ColumnFilters.js';

export class ColumnsFactory {
    /**
     * Colonne technique ID (Masquée par défaut)
     */
    static id(field = "id", overrides = {}) {
        return new ColumnBuilder(field, "ID")
            .setHozAlign("center")
            .setOptions({ visible: false, ...overrides })
            .build();
    }

    /**
     * Colonne de texte standard avec filtre de recherche
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
     * Colonne Booléenne représentée par une Checkbox/Case à cocher (ex: isActive, isSuperuser)
     */
    static boolean(field, title, overrides = {}) {
        return new ColumnBuilder(field, title)
            .setSorter("boolean")
            .setHozAlign("center")
            .setFormatter("tickCross")
            .setHeaderFilter("select", {
                values: { "true": "Oui", "false": "Non", "": "Tous" }
            })
            .setOptions(overrides)
            .build();
    }

    /**
     * Colonne Date dotée du double datepicker (Plage de dates début/fin) pour filtrage distant
     */
    static dateRange(field, title, overrides = {}) {
        return new ColumnBuilder(field, title)
            .setSorter("date")
            .setHozAlign("center")
            .setLiveFilter(false) // On ne filtre pas à chaque saisie clavier
            .setHeaderFilter(dateRangeFilterEditor)
            .setFormatter((cell) => {
                const value = cell.getValue();
                if (!value) return "-";
                // Conversion de la date ISO de CakePHP en format localisé FR
                return new Date(value).toLocaleDateString('fr-FR', {
                    year: 'numeric',
                    month: '2-digit',
                    day: '2-digit'
                });
            })
            .setOptions(overrides)
            .build();
    }
}

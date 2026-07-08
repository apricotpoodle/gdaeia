// ==============================================================================
// Fichier : webroot/js/core/Tabulator/ColumnsFactory.js
// Rôle : Fabrique sémantique de configurations de colonnes standardisées
// ==============================================================================

import { ColumnBuilder } from './ColumnBuilder.js';
import { dateRangeFilterEditor } from './ColumnFilters.js';

export class ColumnsFactory {
    static id(field = "id", overrides = {}) {
        return new ColumnBuilder(field, "ID")
            .setHozAlign("center")
            .setOptions({ visible: false, ...overrides })
            .build();
    }

    static text(field, title, overrides = {}) {
        return new ColumnBuilder(field, title)
            .setSorter("string")
            .setHozAlign("left")
            .setHeaderFilter("input")
            .setOptions(overrides)
            .build();
    }

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

    static dateRange(field, title, overrides = {}) {
        return new ColumnBuilder(field, title)
            .setSorter("date")
            .setHozAlign("center")
            .setLiveFilter(false)
            .setHeaderFilter(dateRangeFilterEditor)
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

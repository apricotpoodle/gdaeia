// ==============================================================================
// Fichier : webroot/js/views/Menus/menus-columns.js
// Rôle : Définition externalisée des colonnes pour la grille des Menus
// ==============================================================================

import { ColumnsFactory } from '/js/core/Tabulator/ColumnsFactory.js';

export function getMenusColumns() {
    return [
        ColumnsFactory.id({ visible: true }),
        ColumnsFactory.text('name', 'Nom'),
        ColumnsFactory.text('url', 'URL'),
        ColumnsFactory.boolean('active', 'Actif')
    ];
}

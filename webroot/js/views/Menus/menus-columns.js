// ==============================================================================
// Fichier : webroot/js/views/Menus/menus-columns.js
// Rôle : Définition externalisée des colonnes pour la grille des Menus
// ==============================================================================

import { ColumnsFactory } from '/js/core/Tabulator/ColumnsFactory.js';

export function getMenusColumns() {
    return [
        ColumnsFactory.id({ visible: true }),
        ColumnsFactory.text('level', 'Niveau',{'width':50}),
        ColumnsFactory.text('name', 'Nom',{'width':500}),
        ColumnsFactory.text('url', 'URL'),
        ColumnsFactory.boolean('dividor_before', 'Diviseur'),
        ColumnsFactory.boolean('disabled', 'Grisé'),
        ColumnsFactory.boolean('active', 'Actif'),
    ];
}

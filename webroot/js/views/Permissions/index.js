import { TabulatorFactory } from '../../core/Tabulator/TabulatorFactory.js';

const roleSelect = document.querySelector('#permissions-role');
const gridSelector = '#permissions-catalog';
const gridHost = document.querySelector(gridSelector);
let grid;
let pendingChange;

roleSelect.addEventListener('change', () => {
    clearTimeout(pendingChange);
    // Tabulator initialise la grille dans une tâche différée. Regrouper les
    // changements évite de reconstruire le conteneur pour une sélection déjà passée.
    pendingChange = setTimeout(() => {
        grid?.destroy();
        grid = undefined;
        gridHost.hidden = roleSelect.value === '';

        // Une nouvelle instance repart en page 1 sans reprendre les anciens filtres.
        if (!gridHost.hidden) {
            grid = TabulatorFactory.createPermissionsGrid(gridSelector, roleSelect.value);
        }
    });
});

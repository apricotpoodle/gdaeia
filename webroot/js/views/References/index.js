import { TabulatorFactory } from '../../core/Tabulator/TabulatorFactory.js';
import { globalTabulatorObserver } from '../../core/Tabulator/TabulatorObserver.js';
import { FlashManager } from '../../core/FlashManager.js';

const tableSelector = '#references-grid';
const gridElement = document.querySelector(tableSelector);
const controller = gridElement?.dataset.controller || '';
const referencesTable = TabulatorFactory.createReferenceGrid(tableSelector, controller);

globalTabulatorObserver.subscribe(`${tableSelector}:action:create`, () => {
    window.location.href = `/${controller}/add`;
});

globalTabulatorObserver.subscribe(`${tableSelector}:action:edit`, (reference) => {
    window.location.href = reference._actionUrl;
});

globalTabulatorObserver.subscribe(`${tableSelector}:action:delete`, async (reference) => {
    if (!confirm(`Supprimer la référence « ${reference.name} » ?`)) return;

    try {
        const csrfToken = document.querySelector('meta[name="csrfToken"]')?.getAttribute('content');
        if (!csrfToken) throw new Error('Jeton CSRF manquant.');
        const response = await fetch(reference._actionUrl, {
            method: 'POST',
            headers: {
                'X-CSRF-Token': csrfToken,
                Accept: 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
            },
        });
        const payload = await response.json();
        if (!response.ok || !payload.success) throw new Error(payload.message || `Erreur serveur (${response.status})`);
        referencesTable.deleteRow(reference.id);
        FlashManager.success(payload.message);
    } catch (error) {
        FlashManager.error(error instanceof Error ? error.message : 'La suppression a échoué.');
    }
});

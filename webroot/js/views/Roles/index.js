import { TabulatorFactory } from '../../core/Tabulator/TabulatorFactory.js';
import { globalTabulatorObserver } from '../../core/Tabulator/TabulatorObserver.js';
import { FlashManager } from '../../core/FlashManager.js';

const tableSelector = '#roles-table';
const rolesTable = TabulatorFactory.createRolesGrid(tableSelector);

globalTabulatorObserver.subscribe(`${tableSelector}:action:create`, () => {
    window.location.href = '/roles/add';
});

globalTabulatorObserver.subscribe(`${tableSelector}:action:edit`, (role) => {
    window.location.href = role._actionUrl;
});

globalTabulatorObserver.subscribe(`${tableSelector}:action:delete`, async (role) => {
    if (!confirm(`Désactiver le rôle « ${role.name} » ?`)) return;

    try {
        const csrfToken = document.querySelector('meta[name="csrfToken"]')?.getAttribute('content');
        if (!csrfToken) throw new Error('Jeton CSRF manquant.');

        const response = await fetch(role._actionUrl, {
            method: 'POST',
            headers: {
                'X-CSRF-Token': csrfToken,
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
            },
        });
        const payload = await response.json();
        if (!response.ok || !payload.success) throw new Error(payload.message || `Erreur serveur (${response.status})`);

        rolesTable.deleteRow(role.id);
        FlashManager.success(payload.message);
    } catch (error) {
        FlashManager.error(`<strong>Action refusée :</strong> ${error.message}`);
    }
});

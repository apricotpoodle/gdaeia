import { TabulatorFactory } from '../../core/Tabulator/TabulatorFactory.js';
import { globalTabulatorObserver } from '../../core/Tabulator/TabulatorObserver.js';
import { FlashManager } from '../../core/FlashManager.js';

TabulatorFactory.createBlockedValidationGrid('#blocked-validations-table');

globalTabulatorObserver.subscribe('#blocked-validations-table:action:resendValidation', async (cycle) => {
    if (!confirm('Envoyer une relance aux validateurs de la demande n° ' + cycle.id + ' ?')) return;
    try {
        const csrfToken = document.querySelector('meta[name="csrfToken"]')?.getAttribute('content');
        if (!csrfToken) throw new Error('Jeton CSRF manquant.');
        const response = await fetch('/api/applicationforms/' + cycle.id + '/validation/remind.json', {
            method: 'POST',
            credentials: 'same-origin',
            headers: {
                'X-CSRF-Token': csrfToken,
                'Accept': 'application/json',
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
            },
            body: JSON.stringify({ _csrfToken: csrfToken }),
        });
        const payload = await response.json();
        if (!response.ok || !payload.success) throw new Error(payload.message || 'La relance n’a pas été envoyée.');
        FlashManager.success(payload.message);
    } catch (error) {
        FlashManager.error('<strong>Action refusée :</strong> ' + error.message);
    }
});

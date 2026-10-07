import { FlashManager } from '../../core/FlashManager.js';

const button = document.querySelector('#duplicate-applicationform');
const token = document.querySelector('meta[name="csrfToken"]')?.getAttribute('content') || '';
const applicationformId = button?.dataset.applicationformId;

button?.addEventListener('click', async () => {
    if (!applicationformId || !confirm(`Dupliquer la demande n° ${applicationformId} en brouillon ?`)) return;

    try {
        const response = await fetch(`/api/applicationforms/${applicationformId}/duplicate.json`, {
            method: 'POST',
            credentials: 'same-origin',
            headers: {
                Accept: 'application/json',
                'Content-Type': 'application/json',
                'X-CSRF-Token': token,
                'X-Requested-With': 'XMLHttpRequest',
            },
            body: JSON.stringify({ _csrfToken: token }),
        });
        const payload = await response.json();
        if (!response.ok || !payload.success) {
            throw new Error(payload.message || 'La duplication a échoué.');
        }

        FlashManager.success(payload.message);
        window.location.href = `/applicationforms/edit/${payload.details.id}`;
    } catch (error) {
        FlashManager.error(`<strong>Action refusée :</strong> ${error.message}`);
    }
});

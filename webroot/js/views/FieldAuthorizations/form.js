import { FlashManager } from '/js/core/FlashManager.js';

/**
 * Soumet le formulaire d’administration des autorisations de champs.
 * @param {string} endpoint URL API de mutation.
 */
export function initializeFieldAuthorizationForm(endpoint) {
    const form = document.getElementById('field-authorization-form');
    if (!form) return;

    form.addEventListener('submit', async (event) => {
        event.preventDefault();
        const csrfToken = document.querySelector('meta[name="csrfToken"]')?.getAttribute('content');
        const submit = form.querySelector('button[type="submit"]');
        if (!csrfToken) {
            FlashManager.error('Jeton CSRF manquant.');
            return;
        }

        submit?.setAttribute('disabled', 'disabled');
        try {
            const response = await fetch(endpoint, {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-Token': csrfToken,
                    'X-Requested-With': 'XMLHttpRequest',
                },
                body: new FormData(form),
            });
            const payload = await response.json();
            if (!response.ok || !payload.success) {
                const details = (payload.errors || []).map(error => `${error.label} : ${error.reason}`).join('<br>');
                throw new Error(details || payload.message || 'Impossible d’enregistrer la règle.');
            }

            window.location.href = '/field-authorizations';
        } catch (error) {
            const message = error instanceof Error ? error.message : 'Une erreur inattendue est survenue.';
            FlashManager.error(`<strong>Échec :</strong> ${message}`);
        } finally {
            submit?.removeAttribute('disabled');
        }
    });
}

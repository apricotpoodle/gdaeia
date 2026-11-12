import { FlashManager } from '/js/core/FlashManager.js';

/**
 * Soumet le formulaire d’administration des autorisations de champs.
 * @param {string} endpoint URL API de mutation.
 */
export function initializeFieldAuthorizationForm(endpoint) {
    const form = document.getElementById('field-authorization-form');
    if (!form) return;

    const typeSelect = form.querySelector('[name="target_type"]');
    const resourceSelect = form.querySelector('[data-target-resource]');
    const targetSelect = form.querySelector('[data-target-select]');
    const description = form.querySelector('[data-target-description]');
    let targets = [];

    const refreshResources = (selectedResource = '') => {
        const type = typeSelect?.value || '';
        const resources = [...new Set(
            targets.filter(item => item.target_type === type).map(item => item.resource),
        )];
        if (!resourceSelect) return;

        resourceSelect.replaceChildren(new Option(
            '— Sélectionner une ressource —', '', true, false,
        ));
        resources.forEach(resource => resourceSelect.add(new Option(
            resource,
            resource,
            false,
            resource === selectedResource,
        )));
    };

    const refreshTargets = () => {
        const type = typeSelect?.value || '';
        const resource = resourceSelect?.value || '';
        const selected = form.dataset.currentTarget || '';
        if (!targetSelect) return;
        targetSelect.replaceChildren(new Option(
            type && resource ? '— Sélectionner une cible —' : 'Sélectionnez d’abord le type et la ressource',
            '', true, false,
        ));
        targets.filter(item => item.target_type === type && item.resource === resource).forEach(item => {
            targetSelect.add(new Option(`${item.label} — ${item.target}`, item.target, false, item.target === selected));
        });
        const item = targets.find(candidate => candidate.target === targetSelect.value
            && candidate.target_type === type && candidate.resource === resource);
        if (description) description.textContent = item?.description || '';
        form.dataset.currentTarget = '';
    };

    typeSelect?.addEventListener('change', () => {
        refreshResources();
        refreshTargets();
    });
    resourceSelect?.addEventListener('change', refreshTargets);
    targetSelect?.addEventListener('change', () => {
        const item = targets.find(candidate => candidate.target === targetSelect.value
            && candidate.target_type === typeSelect?.value && candidate.resource === resourceSelect?.value);
        if (description) description.textContent = item?.description || '';
    });

    const schemaUrl = new URL(form.dataset.schemaUrl || '/api/field-authorizations/get-form-schema.json', window.location.origin);
    ['current_type', 'current_resource', 'current_target'].forEach((key) => {
        const value = form.dataset[{ current_type: 'currentType', current_resource: 'currentResource', current_target: 'currentTarget' }[key]];
        if (value) schemaUrl.searchParams.set(key, value);
    });
    fetch(schemaUrl, { headers: { Accept: 'application/json' } })
        .then(response => response.json())
        .then(payload => {
            targets = payload.targets || [];
            if (typeSelect && form.dataset.currentType) typeSelect.value = form.dataset.currentType;
            refreshResources(form.dataset.currentResource || '');
            refreshTargets();
        })
        .catch(() => {
            if (resourceSelect) resourceSelect.replaceChildren(new Option('Référentiel indisponible', ''));
            if (targetSelect) targetSelect.replaceChildren(new Option('Référentiel indisponible', ''));
            if (description) description.textContent = 'Le référentiel des cibles est momentanément indisponible.';
        });

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

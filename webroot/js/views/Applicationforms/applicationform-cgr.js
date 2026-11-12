/**
 * @file applicationform-cgr.js
 * @description Charge la configuration CGR du département sélectionné.
 */
import { isStructuredCgrValue } from './applicationform-cgr-utils.js';

/**
 * Initialise la synchronisation du champ CGR avec le département.
 *
 * @param {Object} elements Éléments du formulaire et dépendances du chargement.
 * @param {HTMLInputElement} elements.departmentSelect Sélecteur du département.
 * @param {HTMLElement} elements.cgrContainer Conteneur des sélecteurs de segments.
 * @param {HTMLInputElement} elements.cgrFinalInput Champ soumis sous le nom `cgr`.
 * @param {HTMLElement|null} elements.errorMessage Message d'erreur affiché en cas d'échec.
 * @param {Function} [elements.fetcher] Fonction compatible avec `fetch`.
 * @param {Document} [elements.document] Document utilisé pour créer les sélecteurs.
 * @returns {{ready: Promise<void>, refresh: Function}} Contrôle du chargement CGR.
 */
export function initializeCgrForm({
    departmentSelect,
    cgrContainer,
    cgrFinalInput,
    errorMessage,
    fetcher = (...args) => fetch(...args),
    document: documentRef = document,
}) {
    let requestNumber = 0;
    const initialValue = cgrFinalInput.value;

    const clearComponents = () => {
        cgrContainer.replaceChildren();
    };

    const updateFinalCgrValue = () => {
        const selects = cgrContainer.querySelectorAll('.cgr-segment-select');
        const values = Array.from(selects).map(select => select.value);
        cgrFinalInput.value = values.length > 0 && values.every(Boolean)
            ? values.join('-')
            : '';

        const isComplete = cgrFinalInput.value !== '';
        selects.forEach(select => {
            select.classList.remove(
                'border-success',
                'bg-success-subtle',
                'text-success-emphasis',
                'fw-semibold',
                'border-secondary-subtle',
                'bg-light',
            );
            select.classList.add(...(isComplete || !select.value
                ? ['border-secondary-subtle', 'bg-light']
                : ['border-success', 'bg-success-subtle', 'text-success-emphasis', 'fw-semibold']));
        });

        cgrFinalInput.dispatchEvent(new Event('input', { bubbles: true }));
    };

    const refresh = async (departmentId = departmentSelect.value, preserveInitialValue = false) => {
        const currentRequest = ++requestNumber;
        const valueToPreserve = preserveInitialValue ? initialValue : '';
        clearComponents();
        cgrFinalInput.value = '';
        cgrFinalInput.readOnly = true;
        cgrFinalInput.setAttribute('aria-busy', 'true');
        cgrFinalInput.dispatchEvent(new Event('input', { bubbles: true }));
        if (errorMessage) errorMessage.hidden = true;

        if (!departmentId) {
            cgrFinalInput.value = valueToPreserve;
            cgrFinalInput.readOnly = false;
            cgrFinalInput.removeAttribute('aria-busy');
            cgrFinalInput.dispatchEvent(new Event('input', { bubbles: true }));
            return;
        }

        try {
            const response = await fetcher(
                `/api/applicationforms/getCgrConfig/${departmentId}.json`,
                {
                    headers: {
                        Accept: 'application/json',
                        'X-Requested-With': 'XMLHttpRequest',
                    },
                },
            );
            if (!response.ok) {
                throw new Error(`Erreur HTTP ${response.status}`);
            }

            const data = await response.json();
            if (currentRequest !== requestNumber) return;
            if (!Array.isArray(data.schema) || !data.options || typeof data.options !== 'object') {
                throw new Error('Configuration CGR invalide');
            }

            const schema = data.schema;
            const options = data.options;
            const availableOptions = schema.map(segmentType => {
                const cleanType = String(segmentType).trim().toUpperCase();
                const matchedKey = Object.keys(options).find(
                    key => String(key).trim().toUpperCase() === cleanType,
                );

                return (matchedKey && Array.isArray(options[matchedKey]) ? options[matchedKey] : [])
                    .filter(option => option && option.code != null && String(option.code) !== '');
            });
            const hasCompleteCgr = schema.length > 0
                && availableOptions.every(segmentOptions => segmentOptions.length > 0);
            const preserveFreeValue = Boolean(
                valueToPreserve
                && !isStructuredCgrValue(valueToPreserve, schema, options),
            );

            if (preserveFreeValue) {
                cgrFinalInput.value = valueToPreserve;
                cgrFinalInput.readOnly = false;
                return;
            }

            if (!hasCompleteCgr) {
                cgrFinalInput.value = valueToPreserve;
                cgrFinalInput.readOnly = false;
                return;
            }

            if (availableOptions.every(segmentOptions => segmentOptions.length === 1)) {
                cgrFinalInput.value = availableOptions
                    .map(segmentOptions => String(segmentOptions[0].code))
                    .join('-');
                cgrFinalInput.readOnly = true;
                return;
            }

            const currentParts = isStructuredCgrValue(valueToPreserve, schema, options)
                ? valueToPreserve.split('-')
                : [];
            cgrFinalInput.readOnly = true;

            schema.forEach((segmentType, index) => {
                const select = documentRef.createElement('select');
                select.className = 'form-select form-select-sm cgr-segment-select mb-2 w-auto flex-grow-1';
                select.dataset.type = segmentType;
                select.required = true;
                select.disabled = cgrFinalInput.disabled;

                const placeholder = documentRef.createElement('option');
                placeholder.value = '';
                placeholder.textContent = `-- ${segmentType} --`;
                select.appendChild(placeholder);

                for (const optionData of availableOptions[index]) {
                    const option = documentRef.createElement('option');
                    option.value = String(optionData.code);
                    option.textContent = optionData.label ?? String(optionData.code);
                    select.appendChild(option);
                }

                const existingPart = currentParts[index];
                if (existingPart && availableOptions[index].some(
                    option => String(option.code) === existingPart,
                )) {
                    select.value = existingPart;
                } else if (availableOptions[index].length === 1) {
                    select.value = String(availableOptions[index][0].code);
                }

                select.addEventListener('change', updateFinalCgrValue);
                cgrContainer.appendChild(select);
            });

            updateFinalCgrValue();
        } catch {
            if (currentRequest !== requestNumber) return;
            cgrFinalInput.value = valueToPreserve;
            cgrFinalInput.readOnly = false;
            if (errorMessage) errorMessage.hidden = false;
        } finally {
            if (currentRequest === requestNumber) {
                cgrFinalInput.removeAttribute('aria-busy');
                cgrFinalInput.dispatchEvent(new Event('input', { bubbles: true }));
            }
        }
    };

    departmentSelect.addEventListener('change', () => {
        void refresh(departmentSelect.value);
    });

    return {
        ready: refresh(departmentSelect.value, true),
        refresh,
    };
}

if (typeof document !== 'undefined') {
    document.addEventListener('DOMContentLoaded', () => {
        const departmentSelect = document.getElementById('department-id');
        const cgrContainer = document.getElementById('cgr-components-container');
        const cgrFinalInput = document.getElementById('cgr-final-input');

        if (!departmentSelect || !cgrContainer || !cgrFinalInput) return;

        initializeCgrForm({
            departmentSelect,
            cgrContainer,
            cgrFinalInput,
            errorMessage: document.getElementById('cgr-config-error'),
        });
    });
}

/**
 * @file applicationform-treeselect.js
 * @description Orchestration du sélecteur de département pour les demandes de recrutement.
 */
import TreeselectWrapper from '../../core/TreeSelectJS/TreeselectWrapper.js';

document.addEventListener('DOMContentLoaded', async function () {
    const container = document.getElementById('department-tree-select');
    const hiddenInput = document.getElementById('department-id');
    const assistantSelect = document.querySelector('select[name="assistant_id"]');
    const managersContainer = document.getElementById('department-managers');

    if (!hiddenInput) {
        return;
    }

    if (assistantSelect) {
        let requestNumber = 0;
        const canEditAssistant = assistantSelect.dataset.canEdit === 'true';
        const assistantWrapper = document.getElementById('assistant-select-wrapper');
        const singleAssistantMessage = document.getElementById('assistant-options-single');
        const singleAssistantInput = document.getElementById('assistant-single-id');
        const emptyMessage = document.getElementById('assistant-options-empty');
        const errorMessage = document.getElementById('assistant-options-error');
        let selectedAssistantId = assistantSelect.value;

        const clearSingleAssistant = () => {
            if (assistantWrapper) assistantWrapper.hidden = false;
            if (singleAssistantMessage) {
                singleAssistantMessage.hidden = true;
                singleAssistantMessage.textContent = '';
            }
            if (singleAssistantInput) {
                singleAssistantInput.value = '';
                singleAssistantInput.disabled = true;
            }
        };

        const showSingleAssistant = (assistant) => {
            if (!assistantWrapper || !singleAssistantMessage || !singleAssistantInput) {
                return false;
            }

            const assistantId = String(assistant.id);
            assistantSelect.value = assistantId;
            assistantSelect.disabled = true;
            assistantWrapper.hidden = true;
            singleAssistantMessage.textContent = `Une seule assistante est autorisée pour ce département : ${assistant.display_name}.`;
            singleAssistantMessage.hidden = false;
            singleAssistantInput.value = assistantId;
            singleAssistantInput.disabled = !canEditAssistant;
            selectedAssistantId = assistantId;

            return true;
        };

        const updateAssistants = async (departmentId) => {
            const currentRequest = ++requestNumber;
            const previousAssistantId = assistantSelect.value || selectedAssistantId;
            clearSingleAssistant();
            assistantSelect.replaceChildren(new Option('-- Sélectionner --', ''));
            assistantSelect.disabled = true;
            if (emptyMessage) emptyMessage.hidden = true;
            if (errorMessage) errorMessage.hidden = true;

            if (!departmentId) {
                return;
            }

            const endpoint = assistantSelect.dataset.assistantsUrl;
            try {
                const url = new URL(endpoint, window.location.origin);
                url.searchParams.set('department_id', departmentId);
                const response = await fetch(url, {
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });
                if (!response.ok) throw new Error('Erreur API Assistantes');

                const payload = await response.json();
                if (currentRequest !== requestNumber) return;
                if (!payload.success || !Array.isArray(payload.data)) {
                    throw new Error('Réponse API Assistantes invalide');
                }

                const assistants = payload.data;
                const assistantIds = assistants.map((assistant) => String(assistant.id));
                for (const assistant of assistants) {
                    assistantSelect.add(new Option(assistant.display_name, assistant.id));
                }

                const previousIsEligible = assistantIds.includes(previousAssistantId);
                const defaultAssistantId = assistantSelect.dataset.defaultAssistantId;
                const defaultIsEligible = assistantIds.includes(defaultAssistantId);
                assistantSelect.value = previousIsEligible
                    ? previousAssistantId
                    : (defaultIsEligible ? defaultAssistantId : '');
                if (assistants.length === 1 && showSingleAssistant(assistants[0])) {
                    if (emptyMessage) emptyMessage.hidden = true;
                    return;
                }

                selectedAssistantId = assistantSelect.value;
                assistantSelect.disabled = !canEditAssistant;
                if (emptyMessage) emptyMessage.hidden = assistantIds.length > 0;
            } catch (error) {
                if (currentRequest !== requestNumber) return;
                assistantSelect.disabled = !canEditAssistant;
                if (errorMessage) errorMessage.hidden = false;
                console.error('Erreur lors du chargement des assistantes :', error);
            }
        };

        if (canEditAssistant) {
            assistantSelect.addEventListener('change', () => {
                selectedAssistantId = assistantSelect.value;
            });
            hiddenInput.addEventListener('change', () => updateAssistants(hiddenInput.value));
            const initialOptions = Array.from(assistantSelect.options)
                .filter((option) => option.value !== '');
            if (initialOptions.length === 1) {
                showSingleAssistant({
                    id: initialOptions[0].value,
                    display_name: initialOptions[0].textContent || initialOptions[0].text,
                });
            }
            if (hiddenInput.value) {
                updateAssistants(hiddenInput.value);
            }
        } else {
            assistantSelect.disabled = true;
            const initialOptions = Array.from(assistantSelect.options)
                .filter((option) => option.value !== '');
            if (initialOptions.length === 1) {
                showSingleAssistant({
                    id: initialOptions[0].value,
                    display_name: initialOptions[0].textContent || initialOptions[0].text,
                });
            }
        }
    }

    if (managersContainer && managersContainer.dataset.canLoad === 'true') {
        let requestNumber = 0;
        const errorMessage = document.getElementById('department-managers-error');

        const updateDepartmentManagers = async (departmentId) => {
            const currentRequest = ++requestNumber;
            managersContainer.textContent = '';
            if (errorMessage) errorMessage.hidden = true;

            if (!departmentId) {
                managersContainer.textContent = 'Aucun responsable associé';
                return;
            }

            const endpoint = managersContainer.dataset.managersUrl;
            try {
                const url = new URL(endpoint, window.location.origin);
                url.searchParams.set('department_id', departmentId);
                const response = await fetch(url, {
                    headers: {
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });
                if (!response.ok) throw new Error('Erreur API Responsables');

                const payload = await response.json();
                if (currentRequest !== requestNumber) return;
                if (!payload.success || !Array.isArray(payload.data)
                    || !payload.data.every((name) => typeof name === 'string')) {
                    throw new Error('Réponse API Responsables invalide');
                }

                managersContainer.textContent = payload.data.length > 0
                    ? payload.data.join(', ')
                    : 'Aucun responsable associé';
            } catch (error) {
                if (currentRequest !== requestNumber) return;
                if (errorMessage) errorMessage.hidden = false;
                console.error('Erreur lors du chargement des responsables :', error);
            }
        };

        hiddenInput.addEventListener('change', () => updateDepartmentManagers(hiddenInput.value));
        if (hiddenInput.value) {
            updateDepartmentManagers(hiddenInput.value);
        }
    }

    if (!container) return;

    try {
        const response = await fetch('/api/applicationforms/get-form-schema.json', {
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        });

        if (!response.ok) throw new Error('Erreur API Schéma');
        const data = await response.json();

        if (!data.departments) {
            throw new Error('Arborescence des départements indisponible.');
        }

        const rawValue = hiddenInput.value;
        const currentValue = rawValue !== '' && !Number.isNaN(Number(rawValue))
            ? Number(rawValue)
            : null;

        new TreeselectWrapper({
            parentContainer: container,
            hiddenInput,
            value: currentValue,
            options: data.departments,
            isSingleSelect: true,
            openLevel: 2,
            placeholder: 'Sélectionner un département...'
        });
    } catch (error) {
        console.error('Erreur lors de l’initialisation de TreeselectJS :', error);
    }
});

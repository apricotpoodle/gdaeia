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
        let preferredAssistantId = assistantSelect.value;
        let isLoadingAssistants = false;
        const canEditAssistant = assistantSelect.dataset.canEdit === 'true';
        const emptyMessage = document.getElementById('assistant-options-empty');
        const errorMessage = document.getElementById('assistant-options-error');

        assistantSelect.addEventListener('change', () => {
            if (!isLoadingAssistants) {
                preferredAssistantId = assistantSelect.value;
            }
        });

        const updateAssistants = async (departmentId) => {
            if (!isLoadingAssistants) {
                preferredAssistantId = assistantSelect.value;
            }
            const assistantIdToKeep = preferredAssistantId;
            const currentRequest = ++requestNumber;
            assistantSelect.replaceChildren(new Option('-- Sélectionner --', ''));
            assistantSelect.disabled = true;
            isLoadingAssistants = true;
            if (emptyMessage) emptyMessage.hidden = true;
            if (errorMessage) errorMessage.hidden = true;

            if (!departmentId) {
                preferredAssistantId = '';
                isLoadingAssistants = false;
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

                const data = await response.json();
                if (currentRequest !== requestNumber) return;

                const assistants = data.assistants ?? {};
                const assistantIds = Object.keys(assistants);
                for (const assistantId of assistantIds) {
                    assistantSelect.add(new Option(assistants[assistantId], assistantId));
                }

                const previousIsEligible = assistantIds.includes(assistantIdToKeep);
                const defaultAssistantId = assistantSelect.dataset.defaultAssistantId;
                const defaultIsEligible = assistantIds.includes(defaultAssistantId);
                preferredAssistantId = previousIsEligible
                    ? assistantIdToKeep
                    : (defaultIsEligible ? defaultAssistantId : '');
                assistantSelect.value = preferredAssistantId;
                isLoadingAssistants = false;
                assistantSelect.disabled = !canEditAssistant;
                if (emptyMessage) emptyMessage.hidden = assistantIds.length > 0;
            } catch (error) {
                if (currentRequest !== requestNumber) return;
                preferredAssistantId = '';
                isLoadingAssistants = false;
                assistantSelect.disabled = !canEditAssistant;
                if (errorMessage) errorMessage.hidden = false;
                console.error('Erreur lors du chargement des assistantes :', error);
            }
        };

        hiddenInput.addEventListener('change', () => updateAssistants(hiddenInput.value));
        if (hiddenInput.value) {
            updateAssistants(hiddenInput.value);
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

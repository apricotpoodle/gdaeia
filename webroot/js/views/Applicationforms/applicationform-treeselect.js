/**
 * @file applicationform-treeselect.js
 * @description Orchestration du sélecteur de département pour les demandes de recrutement.
 */
import TreeselectWrapper from '../../core/TreeSelectJS/TreeselectWrapper.js';

document.addEventListener('DOMContentLoaded', async function () {
    const container = document.getElementById('department-tree-select');
    const hiddenInput = document.getElementById('department-id');

    if (!container || !hiddenInput) {
        return;
    }

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

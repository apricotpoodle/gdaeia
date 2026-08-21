/**
 * Initialisation du Treeselect pour le choix du département dans la Zone 1.
 */
document.addEventListener('DOMContentLoaded', async function () {
    const container = document.getElementById('department-tree-select');
    const hiddenInput = document.getElementById('department-id');

    if (!container || !hiddenInput) return;

    try {
        // 1. Récupération du schéma via l'API
        const response = await fetch('/api/applicationforms/get-form-schema.json', {
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        });

        if (!response.ok) {
            throw new Error('Erreur lors de la récupération du schéma');
        }

        const data = await response.json();

        // 2. Initialisation de TreeselectJS avec l'arbre natif fourni par l'API
        if (window.Treeselect && data.departments) {
            const currentValue = hiddenInput.value ? String(hiddenInput.value) : null;

            const treeselect = new window.Treeselect({
                parentHtmlContainer: container,
                value: currentValue,
                options: data.departments, // Transmission directe du tableau hiérarchique de l'API
                isSingleSelect: true,
                openLevel: 2, // Ouvre automatiquement les 2 premiers niveaux de l'arbre
                placeholder: 'Sélectionner un département...'
            });

            // Synchronisation avec le champ caché CakePHP lors de la sélection
            treeselect.srcElement.addEventListener('input', (e) => {
                const selectedValue = e.detail;
                hiddenInput.value = selectedValue || '';

                // Informe le script CGR que le département a changé
                hiddenInput.dispatchEvent(new Event('change'));
            });
        }
    } catch (error) {
        console.error('Erreur lors de l’initialisation de TreeselectJS :', error);
    }
});

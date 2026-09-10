/**
 * @file applicationform-treeselect.js
 * @description Initialisation du Treeselect avec verrou synchrone et cast des types.
 */
document.addEventListener('DOMContentLoaded', async function () {
    const container = document.getElementById('department-tree-select');
    const hiddenInput = document.getElementById('department-id');

    if (!container || !hiddenInput) return;

    if (container.dataset.treeselectInit === "true") {
        return;
    }
    container.dataset.treeselectInit = "true";
    container.innerHTML = '';

    // 🔍 DEBUG: On inspecte ce que CakePHP a mis dans l'input (Mode Édition)
    console.log("🌳 [Treeselect] Valeur brute du DOM (hiddenInput.value) :", hiddenInput.value);
    console.log("🌳 [Treeselect] Type brut :", typeof hiddenInput.value);

    try {
        const response = await fetch('/api/applicationforms/get-form-schema.json', {
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        });

        if (!response.ok) throw new Error('Erreur API Schéma');
        const data = await response.json();

        if (window.Treeselect && data.departments) {

            // 💡 CORRECTION DU TYPE : On convertit la chaîne "71" en entier 71
            // pour que Treeselect reconnaisse l'ID qui correspond au JSON.
            let currentValue = hiddenInput.value ? hiddenInput.value : null;

            if (currentValue !== null && !isNaN(currentValue)) {
                currentValue = Number(currentValue);
            }

            console.log("🌳 [Treeselect] Valeur castée passée au composant (currentValue) :", currentValue);
            console.log("🌳 [Treeselect] Type casté :", typeof currentValue);

            const treeselect = new window.Treeselect({
                parentHtmlContainer: container,
                value: currentValue,
                options: data.departments,
                isSingleSelect: true,
                openLevel: 2,
                placeholder: 'Sélectionner un département...'
            });

            // Écouteur sur la sélection du Treeselect
            treeselect.srcElement.addEventListener('input', (e) => {
                const selectedValue = Array.isArray(e.detail) ? e.detail[0] : e.detail;
                hiddenInput.value = selectedValue || '';

                console.log(`[1] Treeselect: Sélection modifiée, nouvel ID = ${hiddenInput.value}`);

                // Émission de l'événement change pour réveiller le script CGR
                hiddenInput.dispatchEvent(new Event('change', { bubbles: true, cancelable: true }));
            });
        }
    } catch (error) {
        console.error('Erreur lors de l’initialisation de TreeselectJS :', error);
    }
});

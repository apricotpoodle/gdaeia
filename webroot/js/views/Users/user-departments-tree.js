/**
 * @file webroot/js/views/Users/user-departments-tree.js
 * @description Synchronise la sélection TreeselectJS avec la structure HasMany de l'ORM CakePHP.
 */

document.addEventListener('DOMContentLoaded', async () => {
    const targetContainer = document.getElementById('user-departments-tree');
    const dataScript = document.getElementById('user-departments-data');
    const hiddenInputsContainer = document.getElementById('user-departments-hidden-inputs');

    if (!targetContainer || !hiddenInputsContainer) {
        return;
    }

    let options = [];
    let initialValue = [];
    const isReadOnly = targetContainer.dataset.readonly === 'true';

    // 1. Récupération des données locales injectées par PHP
    if (dataScript && dataScript.textContent) {
        try {
            const localData = JSON.parse(dataScript.textContent);
            options = localData.options || [];
            initialValue = localData.value || [];
        } catch (e) {
            console.warn('Erreur lecture JSON local :', e);
        }
    }

    // 2. Fallback API si les options locales sont vides
    if (options.length === 0) {
        try {
            const response = await fetch('/api/users/get-form-schema.json', {
                method: 'GET',
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });

            if (response.ok) {
                const apiData = await response.json();
                options = apiData.departments || [];
            }
        } catch (error) {
            console.error('Erreur API getFormSchema :', error);
        }
    }

    /**
     * Génère les inputs cachés au format HasMany : user_departments[INDEX][department_id]
     */
    const syncHiddenInputs = (selectedValues) => {
        hiddenInputsContainer.innerHTML = '';
        const valuesArray = Array.isArray(selectedValues) ? selectedValues : [selectedValues];

        let index = 0;
        valuesArray.forEach((id) => {
            if (id !== null && id !== undefined && id !== '') {
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = `user_departments[${index}][department_id]`;
                input.value = String(id);
                hiddenInputsContainer.appendChild(input);
                index++;
            }
        });
    };

    /**
     * Tente de récupérer Treeselect, ou injecte la balise script si manquante.
     */
    const loadTreeselectClass = () => {
        return new Promise((resolve, reject) => {
            let ClassObj = window.Treeselect || (window.default ? window.default.Treeselect : null);
            if (ClassObj) {
                return resolve(ClassObj);
            }

            // Injection dynamique si la balise script manque
            const script = document.createElement('script');
            script.src = '/js/vendor/treeselect/treeselectjs.umd.js';
            script.onload = () => {
                ClassObj = window.Treeselect || (window.default ? window.default.Treeselect : null);
                if (ClassObj) {
                    resolve(ClassObj);
                } else {
                    reject(new Error('Impossible d\'instancier la classe Treeselect.'));
                }
            };
            script.onerror = () => reject(new Error('Échec du chargement du fichier treeselectjs.umd.js'));
            document.head.appendChild(script);
        });
    };

    try {
        const TreeselectClass = await loadTreeselectClass();

        const treeselect = new TreeselectClass({
            parentHtmlContainer: targetContainer,
            value: initialValue,
            options: options,
            isSingleSelect: false,
            showTags: true,
            clearable: !isReadOnly,
            searchable: true,
            placeholder: 'Sélectionner les départements...',
            disabled: isReadOnly,
            showCount: true,
            openLevel: 1,
            grouped: true,
            isGroupedValue: false,
            isIndependentNodes: false
        });

        syncHiddenInputs(initialValue);

        treeselect.srcElement.addEventListener('input', (e) => {
            syncHiddenInputs(e.detail);
        });
    } catch (err) {
        console.error('Erreur lors du montage de TreeselectJS :', err);
    }
});

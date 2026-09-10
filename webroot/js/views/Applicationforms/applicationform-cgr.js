/**
 * @file applicationform-cgr.js
 * @description Génération dynamique des sélecteurs CGR et écoute du Treeselect.
 */
document.addEventListener('DOMContentLoaded', function() {
    const departmentSelect = document.getElementById('department-id');
    const cgrContainer = document.getElementById('cgr-components-container');
    const cgrFinalInput = document.getElementById('cgr-final-input');

    // 💡 DIAGNOSTIC : Affichage explicite des éléments manquants
    if (!departmentSelect) console.warn("🚨 CGR: L'élément #department-id est introuvable sur la page !");
    if (!cgrContainer) console.warn("🚨 CGR: L'élément #cgr-components-container est introuvable !");
    if (!cgrFinalInput) console.warn("🚨 CGR: L'élément #cgr-final-input est introuvable !");

    if (!departmentSelect || !cgrContainer || !cgrFinalInput) return;

    function refreshAllSelectStyles() {
        const selects = cgrContainer.querySelectorAll('.cgr-segment-select');
        const isCgrComplete = cgrFinalInput.value && cgrFinalInput.value.trim() !== '';

        selects.forEach(select => {
            select.classList.remove('border-success', 'bg-success-subtle', 'text-success-emphasis', 'fw-semibold', 'border-secondary-subtle', 'bg-light');
            if (isCgrComplete) {
                select.classList.add('border-secondary-subtle', 'bg-light');
            } else if (select.value) {
                select.classList.add('border-success', 'bg-success-subtle', 'text-success-emphasis', 'fw-semibold');
            } else {
                select.classList.add('border-secondary-subtle', 'bg-light');
            }
        });
    }

    function fetchAndBuildCgr(departmentId, initialValue = '') {
        if (!departmentId) {
            cgrContainer.innerHTML = '';
            cgrFinalInput.value = '';
            return;
        }

        console.log(`[3] CGR: Appel réseau vers /api/applicationforms/getCgrConfig/${departmentId}.json`);

        fetch(`/api/applicationforms/getCgrConfig/${departmentId}.json`, {
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
            .then(res => {
                if (!res.ok) throw new Error(`HTTP erreur ${res.status}`);
                return res.json();
            })
            .then(data => {
                console.log("[4] CGR: Données reçues du serveur :", data);
                cgrContainer.innerHTML = '';

                if (!data.schema || data.schema.length === 0) {
                    cgrFinalInput.readOnly = false;
                    if (!initialValue) cgrFinalInput.value = '';
                    return;
                }

                cgrFinalInput.readOnly = true;
                const currentParts = initialValue ? initialValue.split('-') : [];

                data.schema.forEach((segmentType, index) => {
                    const select = document.createElement('select');
                    // select.className = 'form-select form-select-sm cgr-segment-select mb-2';
                    // NOUVEAU CODE : Ajout de "w-auto" et "flex-grow-1"
                    select.className = 'form-select form-select-sm cgr-segment-select mb-2 w-auto flex-grow-1';                    select.dataset.type = segmentType;
                    select.required = true;

                    const defaultOption = document.createElement('option');
                    defaultOption.value = '';
                    defaultOption.textContent = `-- ${segmentType} --`;
                    select.appendChild(defaultOption);

                    const cleanType = String(segmentType).trim().toUpperCase();
                    const matchedKey = Object.keys(data.options || {}).find(k => k.trim().toUpperCase() === cleanType);
                    const availableOptions = matchedKey ? data.options[matchedKey] : [];

                    if (availableOptions.length === 0) {
                        const emptyOpt = document.createElement('option');
                        emptyOpt.disabled = true;
                        emptyOpt.textContent = `(Aucun ${segmentType})`;
                        select.appendChild(emptyOpt);
                    } else {
                        let hasPreselection = false;
                        availableOptions.forEach(opt => {
                            const option = document.createElement('option');
                            option.value = opt.code;
                            option.textContent = opt.label;
                            if (currentParts[index] && currentParts[index] === opt.code) {
                                option.selected = true;
                                hasPreselection = true;
                            }
                            select.appendChild(option);
                        });

                        if (!hasPreselection && availableOptions.length === 1) {
                            select.value = availableOptions[0].code;
                        }
                    }

                    select.addEventListener('change', updateFinalCgrValue);
                    cgrContainer.appendChild(select);
                });

                updateFinalCgrValue();
            })
            .catch(err => console.error('[Erreur CGR] Le chargement a échoué :', err));
    }

    function updateFinalCgrValue() {
        const selects = cgrContainer.querySelectorAll('.cgr-segment-select');
        if (selects.length === 0) return;

        const values = Array.from(selects).map(s => s.value).filter(Boolean);
        if (values.length === selects.length) {
            cgrFinalInput.value = values.join('-');
        } else {
            cgrFinalInput.value = '';
        }
        refreshAllSelectStyles();
        cgrFinalInput.dispatchEvent(new Event('input', { bubbles: true }));
    }

    // Le listener crucial qui fait le pont entre les deux scripts
    departmentSelect.addEventListener('change', function() {
        console.log(`[2] CGR: Événement 'change' intercepté ! Lancement de l'hydratation pour le département ${this.value}`);
        fetchAndBuildCgr(this.value, '');
    });

    if (departmentSelect.value) {
        fetchAndBuildCgr(departmentSelect.value, cgrFinalInput.value);
    }
});

document.addEventListener('DOMContentLoaded', function () {
    const departmentSelect = document.getElementById('department-id');
    const cgrContainer = document.getElementById('cgr-components-container');
    const cgrFinalInput = document.getElementById('cgr-final-input');

    if (!departmentSelect || !cgrContainer || !cgrFinalInput) return;

    function fetchAndBuildCgr(departmentId, initialValue = '') {
        if (!departmentId) {
            cgrContainer.innerHTML = '';
            return;
        }

        fetch(`/api/applicationforms/getCgrConfig/${departmentId}.json`, {
            headers: { 'Accept': 'application/json' }
        })
        .then(res => res.json())
        .then(data => {
            cgrContainer.innerHTML = '';

            // Si stratégie libre ou sans schéma : saisie directe
            if (!data.schema || data.schema.length === 0) {
                cgrFinalInput.readOnly = false;
                return;
            }

            cgrFinalInput.readOnly = true;
            const currentParts = initialValue ? initialValue.split('-') : [];

            // Parcours de chaque type requis par la stratégie du département
            data.schema.forEach((segmentType, index) => {
                const select = document.createElement('select');
                select.className = 'form-select form-select-sm cgr-segment-select';
                select.dataset.type = segmentType;

                const defaultOption = document.createElement('option');
                defaultOption.value = '';
                defaultOption.textContent = `-- ${segmentType} --`;
                select.appendChild(defaultOption);

                // Recherche insensible à la casse dans les clés de data.options
                const cleanType = String(segmentType).trim().toUpperCase();
                const matchedKey = Object.keys(data.options || {}).find(k => k.trim().toUpperCase() === cleanType);
                const availableOptions = matchedKey ? data.options[matchedKey] : [];

                if (availableOptions.length === 0) {
                    const emptyOpt = document.createElement('option');
                    emptyOpt.disabled = true;
                    emptyOpt.textContent = `(Aucun ${segmentType} paramétré)`;
                    select.appendChild(emptyOpt);
                } else {
                    availableOptions.forEach(opt => {
                        const option = document.createElement('option');
                        option.value = opt.code;
                        option.textContent = opt.label;
                        
                        if (currentParts[index] === opt.code) {
                            option.selected = true;
                        }
                        select.appendChild(option);
                    });
                }

                select.addEventListener('change', updateFinalCgrValue);
                cgrContainer.appendChild(select);
            });

            updateFinalCgrValue();
        })
        .catch(err => console.error('Erreur chargement CGR:', err));
    }

    function updateFinalCgrValue() {
        const selects = cgrContainer.querySelectorAll('.cgr-segment-select');
        if (selects.length === 0) return;

        const values = Array.from(selects).map(s => s.value).filter(Boolean);

        // Si tous les sous-choix sont faits, fabrication de la chaîne finale (ex: "S01-T02")
        if (values.length === selects.length) {
            cgrFinalInput.value = values.join('-');
        } else {
            cgrFinalInput.value = ''; // Incomplet
        }
    }

    departmentSelect.addEventListener('change', function () {
        fetchAndBuildCgr(this.value);
    });

    if (departmentSelect.value) {
        fetchAndBuildCgr(departmentSelect.value, cgrFinalInput.value);
    }
});
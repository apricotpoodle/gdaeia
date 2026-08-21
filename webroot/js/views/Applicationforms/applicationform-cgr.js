/**
 * @file applicationform-cgr.js
 * @description Génération dynamique des sélecteurs CGR, auto-sélection des options uniques
 * et gestion visuelle dynamique (nettoyage des indicateurs une fois le CGR complet).
 *
 * @author Équipe de Développement
 * @version 1.2.0
 */

document.addEventListener('DOMContentLoaded', function () {
    const departmentSelect = document.getElementById('department-id') || document.getElementById('department-id-input');
    const cgrContainer = document.getElementById('cgr-components-container');
    const cgrFinalInput = document.getElementById('cgr-final-input');

    if (!departmentSelect || !cgrContainer || !cgrFinalInput) return;

    /**
     * Met à jour le style visuel de TOUS les sous-sélecteurs CGR.
     * Si le code CGR complet est assemblé, les sélecteurs restent neutres pour ne pas surcharger l'IHM.
     * S'il est incomplet, les sélecteurs déjà renseignés sont mis en avant.
     */
    function refreshAllSelectStyles() {
        const selects = cgrContainer.querySelectorAll('.cgr-segment-select');
        const isCgrComplete = cgrFinalInput.value && cgrFinalInput.value.trim() !== '';

        selects.forEach(select => {
            // Nettoyage systématique des classes de couleur
            select.classList.remove(
                'border-success', 'bg-success-subtle', 'text-success-emphasis', 'fw-semibold',
                'border-secondary-subtle', 'bg-light'
            );

            if (isCgrComplete) {
                // 💡 CODE COMPLET / AFFICHAGE INITIAL : Style neutre pour les sélecteurs
                select.classList.add('border-secondary-subtle', 'bg-light');
            } else if (select.value) {
                // 💡 SAISIE EN COURS : Mise en avant de l'élément déjà choisi
                select.classList.add('border-success', 'bg-success-subtle', 'text-success-emphasis', 'fw-semibold');
            } else {
                // 💡 SAISIE EN COURS : Élément en attente de choix
                select.classList.add('border-secondary-subtle', 'bg-light');
            }
        });
    }

    /**
     * Interroge l'API CGR pour le département donné et construit les éléments <select>.
     *
     * @param {string} departmentId Identifiant du département.
     * @param {string} [initialValue=''] Code CGR préexistant (ex: "S01-T02").
     */
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

            // Si aucune règle CGR pour ce département
            if (!data.schema || data.schema.length === 0) {
                cgrFinalInput.readOnly = false;
                return;
            }

            cgrFinalInput.readOnly = true;
            const currentParts = initialValue ? initialValue.split('-') : [];

            // Construction de chaque sous-sélecteur du schéma
            data.schema.forEach((segmentType, index) => {
                const select = document.createElement('select');
                select.className = 'form-select form-select-sm cgr-segment-select';
                select.dataset.type = segmentType;

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
                    emptyOpt.textContent = `(Aucun ${segmentType} paramétré)`;
                    select.appendChild(emptyOpt);
                } else {
                    let hasPreselection = false;

                    availableOptions.forEach(opt => {
                        const option = document.createElement('option');
                        option.value = opt.code;
                        option.textContent = opt.label;

                        // Restauration de la valeur initiale enregistrée
                        if (currentParts[index] && currentParts[index] === opt.code) {
                            option.selected = true;
                            hasPreselection = true;
                        }
                        select.appendChild(option);
                    });

                    // Auto-sélection si une seule option est disponible
                    if (!hasPreselection && availableOptions.length === 1) {
                        select.value = availableOptions[0].code;
                    }
                }

                // Écouteur de modification du sélecteur
                select.addEventListener('change', function () {
                    updateFinalCgrValue();
                });

                cgrContainer.appendChild(select);
            });

            // Assemblage de la valeur initiale et mise à jour des styles
            updateFinalCgrValue();
        })
        .catch(err => console.error('Erreur lors du chargement du schéma CGR :', err));
    }

    /**
     * Recalcule la valeur assemblée globale du Code CGR
     * et rafraîchit l'IHM de tous les sélecteurs.
     */
    function updateFinalCgrValue() {
        const selects = cgrContainer.querySelectorAll('.cgr-segment-select');
        if (selects.length === 0) return;

        const values = Array.from(selects).map(s => s.value).filter(Boolean);

        if (values.length === selects.length) {
            // Assemblage complet (ex: "S01-T02")
            cgrFinalInput.value = values.join('-');
        } else {
            // Incomplet
            cgrFinalInput.value = '';
        }

        // Mise à jour de l'apparence des sous-sélecteurs CGR
        refreshAllSelectStyles();

        // Notification d'événement vers applicationform-treeselect.js pour basculer la bannière visuelle
        cgrFinalInput.dispatchEvent(new Event('input', { bubbles: true }));
    }

    // Écouteur de changement de département
    departmentSelect.addEventListener('change', function () {
        fetchAndBuildCgr(this.value);
    });

    // Initialisation au chargement de la page
    if (departmentSelect.value) {
        fetchAndBuildCgr(departmentSelect.value, cgrFinalInput.value);
    }
});

/**
 * @file applicationform-treeselect.js
 * @description Gestionnaire TreeselectJS avec distinction fine de l'obligation du département
 * et de l'état indicatif facultatif du Code CGR (Bleu Info).
 */

document.addEventListener('DOMContentLoaded', async function () {
    const hiddenInput = document.getElementById('department-id') || document.getElementById('department-id-input');
    const cgrInput = document.getElementById('cgr-final-input');
    const container = document.getElementById('department-tree-select');

    if (!hiddenInput || !container) {
        return;
    }

    const originalDepartmentId = hiddenInput.value ? String(hiddenInput.value).trim() : '';
    const originalCgrValue = cgrInput ? cgrInput.value.trim() : '';
    let currentDepartmentId = originalDepartmentId;

    // --- MESSAGES D'AVERTISSEMENT ET D'INFORMATION ---
    let cgrWarningFeedback = document.getElementById('cgr-warning-feedback');
    if (!cgrWarningFeedback && cgrInput && cgrInput.parentNode) {
        cgrWarningFeedback = document.createElement('div');
        cgrWarningFeedback.id = 'cgr-warning-feedback';
        cgrWarningFeedback.className = 'form-text d-none mt-1 fw-bold';
        cgrInput.parentNode.appendChild(cgrWarningFeedback);
    }

    let deptWarningFeedback = document.getElementById('dept-warning-feedback');
    if (!deptWarningFeedback && container.parentNode) {
        deptWarningFeedback = document.createElement('div');
        deptWarningFeedback.id = 'dept-warning-feedback';
        deptWarningFeedback.className = 'form-text text-danger d-none mt-1 fw-bold';
        deptWarningFeedback.innerHTML = '<i class="bi bi-exclamation-circle-fill me-1"></i> La sélection d\'un département est obligatoire.';
        container.parentNode.appendChild(deptWarningFeedback);
    }

    /**
     * Valide l'état du département dans TreeselectJS (Obligatoire).
     *
     * @param {string} deptId
     */
    const validateDepartmentState = (deptId) => {
        const inputControl = container.querySelector('.treeselect-input');
        if (!deptId) {
            if (inputControl) {
                inputControl.classList.add('border-danger', 'bg-danger-subtle');
            }
            if (deptWarningFeedback) deptWarningFeedback.classList.remove('d-none');
        } else {
            if (inputControl) {
                inputControl.classList.remove('border-danger', 'bg-danger-subtle');
            }
            if (deptWarningFeedback) deptWarningFeedback.classList.add('d-none');
        }
    };

    /**
     * Gestion fine du retour visuel CGR à 3 états :
     * - 'danger' (Rouge) : Modification avec réinitialisation d'un CGR précédemment renseigné.
     * - 'info'   (Bleu)  : Département renseigné, mais CGR vide (invite facultative).
     * - 'neutral'(Normal): Département vide OU CGR complet et assemblé.
     *
     * @param {'danger'|'info'|'neutral'} state
     */
    const applyCgrVisualFeedback = (state) => {
        if (!cgrInput) return;

        cgrInput.classList.remove(
            'bg-light',
            'bg-danger-subtle', 'text-danger', 'border-danger',
            'bg-info-subtle', 'text-info-emphasis', 'border-info',
            'fw-bold'
        );

        if (state === 'danger') {
            cgrInput.classList.add('bg-danger-subtle', 'text-danger', 'border-danger', 'fw-bold');
            if (cgrWarningFeedback) {
                cgrWarningFeedback.className = 'form-text text-danger mt-1 fw-bold';
                cgrWarningFeedback.innerHTML = '<i class="bi bi-exclamation-triangle-fill me-1"></i> Attention : Le changement de département a réinitialisé le code CGR.';
            }
        } else if (state === 'info') {
            // BLEU INFO : Saisie CGR facultative
            cgrInput.classList.add('bg-info-subtle', 'text-info-emphasis', 'border-info', 'fw-bold');
            if (cgrWarningFeedback) {
                cgrWarningFeedback.className = 'form-text text-info-emphasis mt-1 fw-bold';
                cgrWarningFeedback.innerHTML = '<i class="bi bi-info-circle-fill me-1"></i> Vous pouvez facultativement sélectionner un code CGR ci-dessus.';
            }
        } else {
            // NEUTRE
            cgrInput.classList.add('bg-light');
            if (cgrWarningFeedback) {
                cgrWarningFeedback.className = 'form-text d-none mt-1 fw-bold';
            }
        }
    };

    // ÉCOUTEUR SUR LE CHAMP CGR FINAL (reçoit les événements de applicationform-cgr.js)
    if (cgrInput) {
        cgrInput.addEventListener('input', function () {
            if (!currentDepartmentId) {
                // Département vide -> CGR Neutre
                applyCgrVisualFeedback('neutral');
            } else if (this.value && this.value.trim() !== '') {
                // CGR complet -> CGR Neutre
                applyCgrVisualFeedback('neutral');
            } else if (currentDepartmentId === originalDepartmentId && !originalCgrValue) {
                // CGR initialement vide -> Bleu Info facultatif
                applyCgrVisualFeedback('info');
            } else if (currentDepartmentId !== originalDepartmentId && !this.value) {
                // Changement sans CGR reconstruit -> Rester Bleu Info ou Danger
                applyCgrVisualFeedback(cgrInput.dataset.hadPreviousValue === 'true' ? 'danger' : 'info');
            } else {
                applyCgrVisualFeedback('info');
            }
        });
    }

    try {
        const response = await fetch('/api/applicationforms/get-form-schema.json');
        if (!response.ok) {
            throw new Error('Erreur HTTP ' + response.status + ' lors du chargement du schéma');
        }
        const data = await response.json();

        const treeselect = new Treeselect({
            parentHtmlContainer: container,
            options: data.departments || [],
            value: originalDepartmentId ? originalDepartmentId : null,
            isSingleSelect: true,
            showTags: false,
            disabledBranchNode: false,
            isGroupSelectable: true,
            clearable: true,
            searchable: true,
            placeholder: 'Sélectionner un département...',
            disabled: hiddenInput.disabled,
        });

        // Contrôle visuel du département au montage initial
        validateDepartmentState(originalDepartmentId);

        // Si département renseigné mais sans CGR au départ -> Bleu Info facultatif
        if (originalDepartmentId && !originalCgrValue) {
            applyCgrVisualFeedback('info');
        }

        // RACCOURCIS CLAVIER (Alt + Flèche Bas / Espace)
        container.addEventListener('keydown', (e) => {
            const isAltArrowDown = e.altKey && e.key === 'ArrowDown';
            const isSpace = e.key === ' ' || e.code === 'Space';

            if ((isAltArrowDown || isSpace) && !treeselect.isListOpened) {
                e.preventDefault();
                e.stopPropagation();
                treeselect.toggleOpenClose();
            }
        });

        const notifyCgrScript = (deptId) => {
            hiddenInput.value = deptId;

            const changeEvent = new Event('change', { bubbles: true, cancelable: true });
            hiddenInput.dispatchEvent(changeEvent);

            if (window.jQuery) {
                window.jQuery(hiddenInput).trigger('change');
            }
        };

        // ÉCOUTEUR PRINCIPAL TREESELECTJS
        treeselect.srcElement.addEventListener('input', (e) => {
            const rawVal = e.detail;
            const selectedDepartmentId = rawVal !== null && rawVal !== undefined ? String(rawVal).trim() : '';

            if (selectedDepartmentId === currentDepartmentId) {
                return;
            }

            const hadActiveCgrValue = cgrInput && cgrInput.value.trim() !== '';
            if (cgrInput) {
                cgrInput.dataset.hadPreviousValue = hadActiveCgrValue ? 'true' : 'false';
            }

            currentDepartmentId = selectedDepartmentId;

            // 1. Validation du Département obligatoire
            validateDepartmentState(selectedDepartmentId);

            // 2. Si le Département devient vide
            if (!selectedDepartmentId) {
                if (cgrInput) cgrInput.value = '';
                applyCgrVisualFeedback('neutral');
                notifyCgrScript('');
                return;
            }

            // 3. Retour au Département d'origine
            if (selectedDepartmentId === originalDepartmentId) {
                if (cgrInput) cgrInput.value = '';
                applyCgrVisualFeedback(originalCgrValue ? 'info' : 'info');
                notifyCgrScript(selectedDepartmentId);
                return;
            }

            // 4. Nouveau Département sélectionné
            if (cgrInput) cgrInput.value = '';

            // Si un CGR actif a été effacé par ce changement -> Rouge Danger, sinon Bleu Info facultatif
            if (hadActiveCgrValue) {
                applyCgrVisualFeedback('danger');
            } else {
                applyCgrVisualFeedback('info');
            }

            notifyCgrScript(selectedDepartmentId);
        });

    } catch (error) {
        console.error('Erreur lors de l\'initialisation de TreeselectJS:', error);
    }
});

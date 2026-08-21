/**
 * @file applicationform-treeselect.js
 * @description Gestionnaire du sélecteur hiérarchique de département (TreeselectJS)
 * avec bascule automatique des états d'avertissement CGR (Rouge / Bleu / Neutre).
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

    // --- CRÉATION DE L'ÉLÉMENT D'INFORMATION / AVERTISSEMENT ---
    let warningFeedback = document.getElementById('cgr-warning-feedback');
    if (!warningFeedback && cgrInput && cgrInput.parentNode) {
        warningFeedback = document.createElement('div');
        warningFeedback.id = 'cgr-warning-feedback';
        warningFeedback.className = 'form-text d-none mt-1 fw-bold';
        cgrInput.parentNode.appendChild(warningFeedback);
    }

    /**
     * Gestion à 3 états du retour visuel CGR :
     * - 'danger' (Rouge) : Département modifié -> Nécessite reconfiguration.
     * - 'info'   (Bleu)  : Département d'origine ou réinitialisé, code CGR incomplet/vide.
     * - 'neutral'(Normal): Code CGR présent et complet.
     *
     * @param {'danger'|'info'|'neutral'} state
     */
    const applyVisualFeedback = (state) => {
        if (!cgrInput) return;

        // Nettoyage des classes de couleur
        cgrInput.classList.remove('bg-light', 'bg-danger-subtle', 'text-danger', 'border-danger', 'bg-info-subtle', 'text-info-emphasis', 'border-info', 'fw-bold');

        if (state === 'danger') {
            // ROUGE : Changement de département
            cgrInput.classList.add('bg-danger-subtle', 'text-danger', 'border-danger', 'fw-bold');
            if (warningFeedback) {
                warningFeedback.className = 'form-text text-danger mt-1 fw-bold';
                warningFeedback.innerHTML = '<i class="bi bi-exclamation-triangle-fill me-1"></i> Attention : Le changement de département a réinitialisé le code CGR.';
            }
        } else if (state === 'info') {
            // BLEU : Département sélectionné mais code CGR incomplet ou vide
            cgrInput.classList.add('bg-info-subtle', 'text-info-emphasis', 'border-info', 'fw-bold');
            if (warningFeedback) {
                warningFeedback.className = 'form-text text-info-emphasis mt-1 fw-bold';
                warningFeedback.innerHTML = '<i class="bi bi-info-circle-fill me-1"></i> Information : Le code CGR est vide. Vous pouvez en sélectionner un nouveau ou laisser vide.';
            }
        } else {
            // NEUTRE : Code CGR saisi et complet
            cgrInput.classList.add('bg-light');
            if (warningFeedback) {
                warningFeedback.className = 'form-text d-none mt-1 fw-bold';
            }
        }
    };

    // 💡 ÉCOUTEUR SUR LE CHAMP CGR FINAL :
    // Réagit dès que applicationform-cgr.js met à jour la valeur globale
    if (cgrInput) {
        cgrInput.addEventListener('input', function () {
            if (this.value && this.value.trim() !== '') {
                // Dès que le code CGR est reconstruit et complet, passage au style NEUTRE (normal)
                applyVisualFeedback('neutral');
            } else if (currentDepartmentId !== originalDepartmentId) {
                // Si le département n'est plus celui d'origine et le CGR est vide
                applyVisualFeedback('danger');
            } else if (!originalCgrValue) {
                // Si le département d'origine n'avait déjà pas de CGR
                applyVisualFeedback('neutral');
            } else {
                // Si on est sur le département d'origine mais avec CGR vidé
                applyVisualFeedback('info');
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

        // ÉCOUTEUR PRINCIPAL TREESELECT
        treeselect.srcElement.addEventListener('input', (e) => {
            const rawVal = e.detail;
            const selectedDepartmentId = rawVal !== null && rawVal !== undefined ? String(rawVal).trim() : '';

            if (selectedDepartmentId === currentDepartmentId) {
                return;
            }

            currentDepartmentId = selectedDepartmentId;

            // SCÉNARIO A : Retour au département d'origine
            if (selectedDepartmentId === originalDepartmentId) {
                if (cgrInput) {
                    if (!originalCgrValue) {
                        cgrInput.value = '';
                        applyVisualFeedback('neutral');
                    } else {
                        cgrInput.value = '';
                        applyVisualFeedback('info');
                    }
                }
                notifyCgrScript(selectedDepartmentId);
                return;
            }

            // SCÉNARIO B : Changement vers un nouveau département ou effacement
            if (cgrInput) {
                cgrInput.value = '';
            }
            applyVisualFeedback('danger'); // Passage au ROUGE
            notifyCgrScript(selectedDepartmentId);
        });

    } catch (error) {
        console.error('Erreur lors de l\'initialisation de TreeselectJS:', error);
    }
});

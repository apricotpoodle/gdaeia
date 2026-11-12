/**
 * @file applicationform-candidate.js
 * @description Gestion dynamique de la saisie du candidat (Collaborateur interne vs Candidat externe).
 *
 * @author Fabrice Bouillerot
 * @version 1.0.0
 */

document.addEventListener('DOMContentLoaded', function () {
    const selectCollaborator = document.getElementById('collaborator-id');
    const inputApplicantName = document.getElementById('applicantname');
    const inputApplicantFirstName = document.getElementById('applicant-first-name');
    const inputApplicantLastName = document.getElementById('applicant-last-name');

    if (!selectCollaborator || !inputApplicantName) return;

    const hasStructuredCandidateFields = inputApplicantName.type === 'hidden'
        && inputApplicantFirstName
        && inputApplicantLastName;

    // Récupération du conteneur du sélecteur pour pouvoir le masquer / l'afficher
    const collaboratorContainer = selectCollaborator.closest('.collaborator-select-wrapper') || selectCollaborator.parentElement;

    /**
     * Met à jour l'état et la visibilité des champs selon les données saisies/sélectionnées.
     */
    function syncCandidateFields(selectionChanged = false) {
        const selectedValue = selectCollaborator.value ? String(selectCollaborator.value).trim() : '';
        const applicantText = inputApplicantName.value ? inputApplicantName.value.trim() : '';

        if (selectedValue !== '') {
            // CAS 1 : Un collaborateur interne est sélectionné.
            const selectedOption = selectCollaborator.options[selectCollaborator.selectedIndex];
            if (selectedOption && selectedOption.text) {
                inputApplicantName.value = selectedOption.text.trim();
            }

            if (hasStructuredCandidateFields) {
                inputApplicantFirstName.value = '';
                inputApplicantLastName.value = '';
                inputApplicantFirstName.disabled = true;
                inputApplicantLastName.disabled = true;
            } else {
                inputApplicantName.readOnly = true;
                inputApplicantName.classList.add('bg-light', 'text-muted');
            }

            if (collaboratorContainer) {
                collaboratorContainer.style.display = '';
            }
        } else {
            // CAS 2 : La demande concerne un candidat externe.
            if (hasStructuredCandidateFields) {
                inputApplicantFirstName.disabled = false;
                inputApplicantLastName.disabled = false;
                if (selectionChanged) {
                    inputApplicantName.value = '';
                }
                if (collaboratorContainer) {
                    collaboratorContainer.style.display = '';
                }

                return;
            }

            inputApplicantName.readOnly = false;
            inputApplicantName.classList.remove('bg-light', 'text-muted');

            if (applicantText !== '') {
                if (collaboratorContainer) {
                    collaboratorContainer.style.display = 'none';
                }
            } else {
                if (collaboratorContainer) {
                    collaboratorContainer.style.display = '';
                }
            }
        }
    }

    // Écoute des événements de saisie et de changement de sélection.
    selectCollaborator.addEventListener('change', () => syncCandidateFields(true));
    inputApplicantName.addEventListener('input', syncCandidateFields);

    // Exécution initiale au chargement, notamment pour le mode édition.
    syncCandidateFields();
});

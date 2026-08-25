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

    if (!selectCollaborator || !inputApplicantName) return;

    // Récupération du conteneur du sélecteur pour pouvoir le masquer / l'afficher
    const collaboratorContainer = selectCollaborator.closest('.collaborator-select-wrapper') || selectCollaborator.parentElement;

    /**
     * Met à jour l'état et la visibilité des champs selon les données saisies/sélectionnées.
     */
    function syncCandidateFields() {
        const selectedValue = selectCollaborator.value ? String(selectCollaborator.value).trim() : '';
        const applicantText = inputApplicantName.value ? inputApplicantName.value.trim() : '';

        if (selectedValue !== '') {
            // 💡 CAS 1 : Un collaborateur interne est sélectionné
            const selectedOption = selectCollaborator.options[selectCollaborator.selectedIndex];
            if (selectedOption && selectedOption.text) {
                // Remplit applicantname avec le nom/libellé du collaborateur choisi
                inputApplicantName.value = selectedOption.text.trim();
            }

            // Verrouillage du champ applicantname en lecture seule
            inputApplicantName.readOnly = true;
            inputApplicantName.classList.add('bg-light', 'text-muted');

            // Garantit que le sélecteur reste visible
            if (collaboratorContainer) {
                collaboratorContainer.style.display = '';
            }
        } else {
            // 💡 CAS 2 : Pas de collaborateur interne (Collaborateur_id est NULL)
            inputApplicantName.readOnly = false;
            inputApplicantName.classList.remove('bg-light', 'text-muted');

            // Si du texte est saisi librement dans applicantname, masquer le sélecteur
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

    // Écoute des événements de saisie et de changement de sélection
    selectCollaborator.addEventListener('change', syncCandidateFields);
    inputApplicantName.addEventListener('input', syncCandidateFields);

    // Exécution initiale au chargement (crucial pour le mode edit)
    syncCandidateFields();
});

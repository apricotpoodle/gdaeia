/**
 * @file zone-contract.js
 * @description Gestion des comportements dynamiques pour la zone "Caractéristiques du contrat".
 */
document.addEventListener('DOMContentLoaded', function () {
    const contractTypeSelect = document.getElementById('contracttype-id');
    const beginAtInput = document.getElementById('begin-at');
    const beginAtWarning = document.getElementById('begin-at-warning-text');
    const endAtInput = document.getElementById('end-at');
    const asterisk = document.getElementById('end-at-required-asterisk');
    const cdiWarning = document.getElementById('cdi-warning-text');

    /**
     * Alerte visuelle douce (code couleur) lorsque la date de début est vide.
     *
     * @returns {void}
     */
    function updateBeginDateNotice() {
        if (!beginAtInput) return;

        if (!beginAtInput.value) {
            beginAtInput.classList.add('border-warning', 'bg-warning-subtle');
            if (beginAtWarning) beginAtWarning.classList.remove('d-none');
        } else {
            beginAtInput.classList.remove('border-warning', 'bg-warning-subtle');
            if (beginAtWarning) beginAtWarning.classList.add('d-none');
        }
    }

    /**
     * Pilote la cohérence CDI vs CDD/ALT/CTT sur le champ end_at.
     *
     * @returns {void}
     */
    function updateContractTypeRules() {
        if (!contractTypeSelect || !endAtInput) return;

        const selectedOption = contractTypeSelect.options[contractTypeSelect.selectedIndex];
        const selectedText = selectedOption ? selectedOption.text.toUpperCase().trim() : '';

        if (selectedText.includes('CDI')) {
            endAtInput.value = '';
            endAtInput.disabled = true;
            if (asterisk) asterisk.classList.add('d-none');
            if (cdiWarning) cdiWarning.classList.remove('d-none');
        } else if (selectedText.includes('CDD') || selectedText.includes('ALT') || selectedText.includes('CTT')) {
            endAtInput.disabled = false;
            if (asterisk) asterisk.classList.remove('d-none');
            if (cdiWarning) cdiWarning.classList.add('d-none');
        } else {
            endAtInput.disabled = false;
            if (asterisk) asterisk.classList.add('d-none');
            if (cdiWarning) cdiWarning.classList.add('d-none');
        }
    }

    if (beginAtInput) {
        beginAtInput.addEventListener('change', updateBeginDateNotice);
        beginAtInput.addEventListener('input', updateBeginDateNotice);
        updateBeginDateNotice();
    }

    if (contractTypeSelect) {
        contractTypeSelect.addEventListener('change', updateContractTypeRules);
        updateContractTypeRules();
    }
});

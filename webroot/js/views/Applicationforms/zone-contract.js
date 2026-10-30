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
    const workingDaysContainer = document.getElementById('container-working-days');
    const workingDaysInput = document.getElementById('working-days');

    /**
     * Convertit une date HTML en date locale sans décalage de fuseau.
     *
     * @param {string} value Date au format YYYY-MM-DD.
     * @returns {Date|null} Date locale ou null si la valeur est invalide.
     */
    function parseDateInput(value) {
        if (!/^\d{4}-\d{2}-\d{2}$/.test(value)) return null;

        const [year, month, day] = value.split('-').map(Number);
        const date = new Date(year, month - 1, day);

        return date.getFullYear() === year
            && date.getMonth() === month - 1
            && date.getDate() === day
            ? date
            : null;
    }

    /**
     * Calcule les jours calendaires inclusifs d'une période.
     *
     * @param {Date} begin Date de début.
     * @param {Date} end Date de fin.
     * @returns {number} Nombre de jours calendaires.
     */
    function calendarDaysInclusive(begin, end) {
        return Math.floor((end - begin) / 86400000) + 1;
    }

    /**
     * Calcule les jours ouvrés inclusifs, du lundi au vendredi.
     *
     * @param {Date} begin Date de début.
     * @param {Date} end Date de fin.
     * @returns {number} Nombre de jours ouvrés.
     */
    function workingDaysInclusive(begin, end) {
        let workingDays = 0;
        const current = new Date(begin);

        while (current <= end) {
            const weekday = current.getDay();
            if (weekday !== 0 && weekday !== 6) workingDays++;
            current.setDate(current.getDate() + 1);
        }

        return workingDays;
    }

    /**
     * Synchronise la visibilité, la limite et la valeur par défaut des jours.
     *
     * @param {boolean} resetValue Réinitialise la valeur après une modification de date.
     * @returns {void}
     */
    function updateWorkingDays(resetValue = false) {
        if (!workingDaysContainer || !workingDaysInput) return;

        const begin = parseDateInput(beginAtInput?.value || '');
        const end = parseDateInput(endAtInput?.value || '');
        if (!begin || !end || end < begin || endAtInput?.disabled) {
            workingDaysContainer.classList.add('d-none');
            workingDaysInput.removeAttribute('max');
            if (resetValue) workingDaysInput.value = '';
            return;
        }

        const maximum = calendarDaysInclusive(begin, end);
        workingDaysInput.max = String(maximum);
        workingDaysContainer.classList.remove('d-none');

        if (resetValue || workingDaysInput.value === '') {
            workingDaysInput.value = String(workingDaysInclusive(begin, end));
        } else if (Number(workingDaysInput.value) > maximum) {
            workingDaysInput.value = String(maximum);
        }
    }

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

        updateWorkingDays(true);
    }

    if (beginAtInput) {
        beginAtInput.addEventListener('change', () => {
            updateBeginDateNotice();
            updateWorkingDays(true);
        });
        beginAtInput.addEventListener('input', () => {
            updateBeginDateNotice();
            updateWorkingDays(true);
        });
        updateBeginDateNotice();
    }

    if (endAtInput) {
        endAtInput.addEventListener('change', () => updateWorkingDays(true));
        endAtInput.addEventListener('input', () => updateWorkingDays(true));
    }

    if (contractTypeSelect) {
        contractTypeSelect.addEventListener('change', updateContractTypeRules);
        updateContractTypeRules();
    }

    updateWorkingDays();
});

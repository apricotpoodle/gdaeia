const settingEndpoints = {
    'validation.default_due_hours': {
        url: '/api/workflow-settings/default-due-hours.json',
        field: 'default_due_hours',
    },
    'validation.blocked_after_business_days': {
        url: '/api/workflow-settings/blocked-after-business-days.json',
        field: 'blocked_after_business_days',
    },
    'salary.k_coefficient': {
        url: '/api/workflow-settings/k-coefficient.json',
        field: 'k_coefficient',
    },
};

const commentRequirementKeys = {
    'validation.comment_required.accept': 'accepter',
    'validation.comment_required.reject': 'refuser',
};

const editorTypes = {
    number: 'number',
    boolean: 'checkbox',
    date: 'date',
    text: 'text',
};

/**
 * Crée un éditeur de cellule adapté au type de valeur du réglage.
 * @param {'number'|'boolean'|'date'|'text'} type Type de valeur.
 * @param {{documentRef?: Document}} [options] Dépendances du DOM.
 * @returns {Function} Éditeur Tabulator.
 */
export function createSettingEditor(type, { documentRef = document } = {}) {
    const inputType = editorTypes[type] || 'text';

    return (cell, onRendered, success, cancel) => {
        const input = documentRef.createElement('input');
        let completed = false;
        input.type = inputType;
        input.className = inputType === 'checkbox'
            ? 'workflow-setting-checkbox'
            : 'form-control form-control-sm';
        input.setAttribute('aria-label', cell.getRow().getData().label);

        if (inputType === 'checkbox') {
            input.checked = Boolean(cell.getValue());
            input.addEventListener('change', () => {
                if (completed) return;
                completed = true;
                success(input.checked);
            });
        } else {
            input.value = cell.getValue() ?? '';
            input.required = true;
            const setting = cell.getRow().getData();
            if (inputType === 'number') {
                input.min = setting.min ?? '';
                input.step = setting.step ?? 'any';
            }

            const commit = () => {
                if (completed) return;
                completed = true;
                if (inputType === 'number') {
                    success(input.value === '' ? null : input.valueAsNumber);
                } else {
                    success(input.value);
                }
            };
            input.addEventListener('blur', commit);
            input.addEventListener('keydown', (event) => {
                if (event.key === 'Enter') {
                    event.preventDefault();
                    commit();
                } else if (event.key === 'Escape') {
                    event.preventDefault();
                    if (completed) return;
                    completed = true;
                    cancel();
                }
            });
        }

        onRendered(() => {
            input.focus();
            if (inputType !== 'checkbox') input.select?.();
        });

        return input;
    };
}

/**
 * Configure les colonnes des grilles locales de réglages.
 * @param {{documentRef?: Document}} [options] Dépendances du DOM.
 * @returns {Array<Object>} Colonnes Tabulator.
 */
export function createSettingColumns(options = {}) {
    return [
        {
            title: 'Paramètre',
            field: 'label',
            headerSort: false,
            formatter: 'plaintext',
            responsive: 0,
            widthGrow: 1.2,
        },
        {
            title: 'Aide / unité',
            field: 'help',
            headerSort: false,
            formatter: 'plaintext',
            responsive: 2,
            widthGrow: 1.5,
        },
        {
            title: 'Valeur',
            field: 'value',
            headerSort: false,
            responsive: 0,
            formatter: (cell) => {
                const row = cell.getRow().getData();
                const value = cell.getValue();
                return row.type === 'boolean' ? (value ? 'Oui' : 'Non') : String(value ?? '');
            },
            editor: (cell, onRendered, success, cancel) => createSettingEditor(
                cell.getRow().getData().type,
                options,
            )(cell, onRendered, success, cancel),
            editable: (cell) => Boolean(cell.getRow().getData().canEdit),
        },
    ];
}

/**
 * Construit la requête dédiée au réglage modifié sans changer son contrat API.
 * @param {Object} setting Ligne modifiée.
 * @param {*} value Valeur validée par Tabulator.
 * @param {Array<Object>} rows Lignes de la grille.
 * @returns {{url: string, method: string, headers: Object, body: string}}
 */
export function createWorkflowSettingRequest(setting, value) {
    const requirementField = commentRequirementKeys[setting.key];
    if (requirementField) {
        return {
            url: '/api/workflow-settings/comment-requirements.json',
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ [requirementField]: Boolean(value) }),
        };
    }

    const endpoint = settingEndpoints[setting.key];
    if (!endpoint) {
        throw new Error(`Aucun endpoint de sauvegarde pour le réglage « ${setting.key} ».`);
    }

    return {
        url: endpoint.url,
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ [endpoint.field]: value }),
    };
}

/**
 * Construit les lignes de réglage des champs vidés à la duplication.
 * @param {{fields: string[], options: Array<{field: string, label: string}>}} payload Réponse GET.
 * @param {boolean} canEdit Autorisation d’édition côté écran.
 * @returns {Array<Object>} Lignes Tabulator.
 */
export function createDuplicateResetRows(payload, canEdit) {
    const selectedFields = new Set(payload.fields || []);

    return (payload.options || []).map(({ field, label }) => ({
        key: field,
        field,
        label,
        value: selectedFields.has(field),
        help: duplicateResetHelp(selectedFields.has(field)),
        type: 'boolean',
        canEdit,
    }));
}

/**
 * Décrit le résultat du choix courant pour une valeur métier.
 * @param {boolean} reset Indique si la valeur sera réinitialisée.
 * @returns {string} Aide correspondant au choix.
 */
export function duplicateResetHelp(reset) {
    return reset
        ? 'Cette valeur sera effacée lors de la duplication.'
        : 'Cette valeur sera conservée lors de la duplication.';
}

/**
 * Construit les valeurs canoniques de la ligne après l’enregistrement.
 * @param {string[]} fields Champs que l’API confirme réinitialiser.
 * @param {string} field Champ de la ligne modifiée.
 * @returns {{value: boolean, help: string}} Valeur et aide mises à jour.
 */
export function createDuplicateResetRowUpdate(fields, field) {
    const reset = fields.includes(field);

    return { value: reset, help: duplicateResetHelp(reset) };
}

/**
 * Construit la requête dédiée à la sélection complète des champs à réinitialiser.
 * @param {string[]} fields Champs configurables choisis.
 * @returns {{url: string, method: string, headers: Object, body: string}}
 */
export function createDuplicateResetFieldsRequest(fields) {
    return {
        url: '/api/workflow-settings/duplicate-reset-fields.json',
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ fields }),
    };
}

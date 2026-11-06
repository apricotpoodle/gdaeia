import { FlashManager } from '../../core/FlashManager.js';
import { TabulatorFactory } from '../../core/Tabulator/TabulatorFactory.js';
import { globalTabulatorObserver } from '../../core/Tabulator/TabulatorObserver.js';
import {
    createDepartmentSelector,
    fillSelectOptions,
    renderCircuitPreview,
    ValidationCircuitRulesClient,
} from './circuit-rules.js';

/**
 * Orchestre les formulaires et les actions des grilles du domaine.
 * Les habilitations sont initialisées seulement lorsque le serveur a rendu leur section.
 * @param {Object} [dependencies={}] Adaptateurs de la page et du transport.
 * @returns {Promise<void>}
 */
export async function initializeCircuitRulesPage({
    documentRef = document,
    client = new ValidationCircuitRulesClient(),
    factory = TabulatorFactory,
    observer = globalTabulatorObserver,
    createSelector = createDepartmentSelector,
    flash = FlashManager,
    confirmAction = (message) => window.confirm(message),
} = {}) {
    const find = (selector) => documentRef.querySelector(selector);
    const form = find('#validation-circuit-rule-form');
    if (!form) return;

    const [tree, options] = await Promise.all([client.departments(), client.options()]);
    const contract = form.querySelector('[name="contracttype_id"]');
    const finalRole = form.querySelector('[name="final_role_id"]');
    fillSelectOptions(contract, options.contracttypes, 'Choisir un type de contrat');
    fillSelectOptions(finalRole, options.roles, 'Choisir un rôle final');

    const rulesSelector = '#validation-circuit-rules-grid';
    const table = factory.createValidationCircuitRulesGrid(rulesSelector);
    const fields = find('#validation-circuit-rule-fields');
    const preview = find('#validation-circuit-preview');
    const mode = find('#validation-circuit-rule-mode');
    let editingId = null;
    let previewVersion = 0;
    let busy = false;

    const invalidatePreview = () => {
        previewVersion++;
        preview.replaceChildren();
    };
    const showCount = (selector, values) => {
        find(selector).textContent = values.length === 0
            ? 'Aucun département sélectionné.'
            : `${values.length} département${values.length > 1 ? 's' : ''} sélectionné${values.length > 1 ? 's' : ''}.`;
    };
    const departments = createSelector(find('#validation-circuit-departments'), tree, (values) => {
        showCount('#validation-circuit-selected-count', values);
        invalidatePreview();
    });
    const values = () => ({
        department_ids: departments.values,
        contracttype_id: contract.value,
        final_role_id: finalRole.value,
    });
    const reset = () => {
        editingId = null;
        form.reset();
        contract.value = '';
        finalRole.value = '';
        departments.setLocked(false);
        departments.replace([]);
        showCount('#validation-circuit-selected-count', []);
        mode.textContent = 'Création de règles pour les départements sélectionnés';
        invalidatePreview();
    };
    const run = async (operation) => {
        if (busy) return;
        busy = true;
        fields.disabled = true;
        try {
            await operation();
        } catch (error) {
            flash.error(error.message);
        } finally {
            busy = false;
            fields.disabled = false;
        }
    };
    const newSelection = () => {
        if (busy) return;
        reset();
        form.scrollIntoView?.({ behavior: 'smooth', block: 'nearest' });
    };
    const edit = (rule) => {
        if (busy) return;
        reset();
        editingId = Number(rule.id);
        departments.replace([Number(rule.department_id)]);
        departments.setLocked(true);
        showCount('#validation-circuit-selected-count', departments.values);
        contract.value = String(rule.contracttype_id);
        finalRole.value = String(rule.final_role_id);
        mode.textContent = `Modification unitaire — ${rule.department?.name || 'Département'} (département conservé)`;
        form.scrollIntoView?.({ behavior: 'smooth', block: 'nearest' });
    };

    form.addEventListener('submit', (event) => {
        event.preventDefault();
        if (!form.reportValidity()) return;
        return run(async () => {
            const result = await client.saveRule(values(), editingId);
            reset();
            await table.replaceData();
            flash.success(result.message);
        });
    });
    find('#save-validation-circuit-rule')?.addEventListener('click', () => form.requestSubmit());
    find('#new-validation-circuit-rule')?.addEventListener('click', newSelection);
    find('#preview-validation-circuit-rule')?.addEventListener('click', () => {
        if (!form.reportValidity()) return;
        return run(async () => {
            invalidatePreview();
            const version = previewVersion;
            const result = await client.preview(values());
            if (version === previewVersion) renderCircuitPreview(preview, result);
        });
    });
    contract.addEventListener('change', invalidatePreview);
    finalRole.addEventListener('change', invalidatePreview);
    observer.subscribe(`${rulesSelector}:action:create`, newSelection);
    observer.subscribe(`${rulesSelector}:action:edit`, edit);
    observer.subscribe(`${rulesSelector}:action:delete`, (rule) => {
        if (!confirmAction(`Retirer la règle « ${rule.contracttype?.name || 'Contrat'} » du département « ${rule.department?.name || 'Département'} » et rétablir son circuit complet ?`)) return;
        return run(async () => {
            const result = await client.deleteRule(rule.id);
            if (editingId === Number(rule.id)) reset();
            await table.replaceData();
            flash.success(result.message);
        });
    });
    fields.disabled = false;

    const managerForm = find('#validation-circuit-manager-form');
    if (!managerForm) return;
    const managerRole = managerForm.querySelector('[name="role_id"]');
    fillSelectOptions(managerRole, options.roles, 'Choisir un rôle gestionnaire');
    const managersSelector = '#validation-circuit-managers-grid';
    const managersTable = factory.createValidationCircuitManagersGrid(managersSelector);
    const managerFields = find('#validation-circuit-manager-fields');
    const managerDepartments = createSelector(find('#validation-circuit-manager-departments'), tree, (ids) => {
        showCount('#validation-circuit-manager-selected-count', ids);
    });
    let grantsBusy = false;
    const runGrant = async (operation) => {
        if (grantsBusy) return;
        grantsBusy = true;
        managerFields.disabled = true;
        try {
            await operation();
        } catch (error) {
            flash.error(error.message);
        } finally {
            grantsBusy = false;
            managerFields.disabled = false;
        }
    };
    managerForm.addEventListener('submit', (event) => {
        event.preventDefault();
        if (!managerForm.reportValidity()) return;
        return runGrant(async () => {
            const result = await client.assignManagers(managerDepartments.values, managerRole.value);
            await managersTable.replaceData();
            flash.success(result.message);
        });
    });
    find('#assign-validation-circuit-managers')?.addEventListener('click', () => managerForm.requestSubmit());
    observer.subscribe(`${managersSelector}:action:delete`, (grant) => {
        if (!confirmAction(`Retirer l’habilitation du rôle « ${grant.role?.name || 'Rôle'} » pour « ${grant.department?.name || 'Département'} » ?`)) return;
        return runGrant(async () => {
            const result = await client.revokeManager(grant.id);
            await managersTable.replaceData();
            flash.success(result.message);
        });
    });
    managerFields.disabled = false;
}

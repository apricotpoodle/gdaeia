import { FlashManager } from '../../core/FlashManager.js';
import { TabulatorFactory } from '../../core/Tabulator/TabulatorFactory.js';

const departmentsElement = document.querySelector('#validation-sequences-departments-table');
const availableRolesElement = document.querySelector('#validation-sequences-available-roles-table');
const assignedRolesElement = document.querySelector('#validation-sequences-assigned-roles-table');

if (departmentsElement && availableRolesElement && assignedRolesElement) {
    const departmentsTable = TabulatorFactory.createValidationSequencesDepartmentsGrid();
    const availableRolesTable = TabulatorFactory.createValidationSequencesAvailableRolesGrid();
    const assignedRolesTable = TabulatorFactory.createValidationSequencesAssignedRolesGrid();
    const csrfToken = document.querySelector('meta[name="csrfToken"]')?.getAttribute('content') || '';
    let visibleRoles = [];

    const selectedDepartmentIds = () => departmentsTable.getSelectedRows()
        .map((row) => Number(row.getData().id))
        .filter((id) => Number.isInteger(id) && id > 0);

    async function readJson(response, fallbackMessage) {
        const body = await response.text();
        let payload;
        try {
            payload = JSON.parse(body);
        } catch (error) {
            throw new Error(fallbackMessage);
        }
        if (!response.ok || payload.success === false) {
            throw new Error(payload.message || fallbackMessage);
        }
        return payload;
    }

    async function refreshRoles() {
        const departmentIds = selectedDepartmentIds();
        if (departmentIds.length === 0) {
            await assignedRolesTable.replaceData([]);
            await availableRolesTable.replaceData(visibleRoles);
            return;
        }
        try {
            const query = new URLSearchParams();
            departmentIds.forEach((id) => query.append('department_ids[]', String(id)));
            const payload = await readJson(await fetch(`/api/validationsequences/assigned-roles.json?${query}`, { headers: { Accept: 'application/json' } }), 'Impossible de charger les rôles associés.');
            const assignedRoles = payload.data || [];
            const assignedIds = new Set(assignedRoles.map((role) => Number(role.id)));
            await assignedRolesTable.replaceData(assignedRoles);
            await availableRolesTable.replaceData(visibleRoles.filter((role) => !assignedIds.has(Number(role.id))));
        } catch (error) {
            FlashManager.error(error.message);
        }
    }

    async function loadRoles() {
        try {
            const payload = await readJson(await fetch('/api/validationsequences/roles.json', { headers: { Accept: 'application/json' } }), 'Impossible de charger les rôles validateurs.');
            visibleRoles = payload.data || [];
            await refreshRoles();
        } catch (error) {
            FlashManager.error(error.message);
        }
    }

    async function mutate(action, data, fallbackMessage) {
        const response = await fetch(`/api/validationsequences/${action}.json`, {
            method: 'POST',
            headers: { Accept: 'application/json', 'Content-Type': 'application/json', 'X-CSRF-Token': csrfToken, 'X-Requested-With': 'XMLHttpRequest' },
            body: JSON.stringify(data),
        });
        return readJson(response, fallbackMessage);
    }

    async function assignRole(role) {
        const departmentIds = selectedDepartmentIds();
        if (departmentIds.length === 0) {
            FlashManager.warning('Sélectionnez au moins un département.');
            return;
        }
        try {
            const payload = await mutate('assign-role', { department_ids: departmentIds, role_id: Number(role.id), sequence: 1 }, 'Impossible d’ajouter le rôle validateur.');
            await refreshRoles();
            FlashManager.success(`${payload.associations_created} association${payload.associations_created > 1 ? 's' : ''} créée${payload.associations_created > 1 ? 's' : ''}.`);
        } catch (error) {
            FlashManager.error(error.message);
        }
    }

    async function unassignRole(role) {
        const departmentIds = selectedDepartmentIds();
        try {
            const payload = await mutate('unassign-role', { department_ids: departmentIds, role_id: Number(role.id) }, 'Impossible de retirer le rôle validateur.');
            await refreshRoles();
            FlashManager.success(`${payload.associations_deleted} association${payload.associations_deleted > 1 ? 's' : ''} retirée${payload.associations_deleted > 1 ? 's' : ''}.`);
        } catch (error) {
            FlashManager.error(error.message);
        }
    }

    async function updateSequence(cell) {
        const role = cell.getRow().getData();
        const sequence = Number(cell.getValue());
        if (!Number.isInteger(sequence) || sequence < 1) {
            FlashManager.warning('Le numéro de séquence doit être un entier supérieur ou égal à 1.');
            await refreshRoles();
            return;
        }
        try {
            await mutate('update-sequence', { department_ids: selectedDepartmentIds(), role_id: Number(role.id), sequence }, 'Impossible de modifier la séquence.');
            FlashManager.success('Numéro de séquence mis à jour.');
        } catch (error) {
            FlashManager.error(error.message);
            await refreshRoles();
        }
    }

    departmentsTable.on('rowSelectionChanged', refreshRoles);
    availableRolesTable.on('rowDblClick', (event, row) => assignRole(row.getData()));
    assignedRolesTable.on('rowDblClick', (event, row) => unassignRole(row.getData()));
    assignedRolesTable.on('cellEdited', updateSequence);
    loadRoles();
}

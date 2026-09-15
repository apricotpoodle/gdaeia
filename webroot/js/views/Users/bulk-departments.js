/**
 * @file webroot/js/views/Users/bulk-departments.js
 * @description Synchronise trois grilles Tabulator pour l'association de départements aux utilisateurs.
 */

import { FlashManager } from '../../core/FlashManager.js';
import { TabulatorFactory } from '../../core/Tabulator/TabulatorFactory.js';

const departmentsTableElement = document.querySelector('#bulk-departments-table');
const availableUsersTableElement = document.querySelector('#bulk-available-users-table');
const selectedUsersTableElement = document.querySelector('#bulk-selected-users-table');

if (departmentsTableElement && availableUsersTableElement && selectedUsersTableElement) {
    const departmentsTable = TabulatorFactory.createBulkDepartmentsGrid('#bulk-departments-table');
    const availableUsersTable = TabulatorFactory.createBulkAvailableUsersGrid('#bulk-available-users-table');
    const selectedUsersTable = TabulatorFactory.createBulkSelectedUsersGrid('#bulk-selected-users-table');
    const csrfToken = document.querySelector('meta[name="csrfToken"]')?.getAttribute('content') || '';
    const selectedDepartmentIds = () => departmentsTable.getSelectedRows()
        .map((row) => Number(row.getData().id))
        .filter((id) => Number.isInteger(id) && id > 0);

    async function refreshAssignedUsers() {
        const departmentIds = selectedDepartmentIds();
        const query = new URLSearchParams();
        departmentIds.forEach((id) => query.append('department_ids[]', String(id)));
        const suffix = query.size > 0 ? `?${query.toString()}` : '';

        try {
            await Promise.all([
                availableUsersTable.setData(`/api/users/bulk-departments-users.json${suffix}`),
                selectedUsersTable.setData(`/api/users/bulk-departments-assigned-users.json${suffix}`),
            ]);
        } catch (error) {
            FlashManager.error('Impossible de charger les utilisateurs.');
        }
    }

    async function updateDepartmentAccess(action, user) {
        const departmentIds = selectedDepartmentIds();
        if (departmentIds.length === 0) {
            FlashManager.warning('Sélectionnez au moins un département.');
            return;
        }

        try {
            const response = await fetch(`/api/users/${action}.json`, {
                method: 'POST',
                headers: { Accept: 'application/json', 'Content-Type': 'application/json', 'X-CSRF-Token': csrfToken, 'X-Requested-With': 'XMLHttpRequest' },
                body: JSON.stringify({ department_ids: departmentIds, user_id: Number(user.id) }),
            });
            const payload = await readJson(response, 'Impossible de modifier les associations.');
            await refreshAssignedUsers();
            FlashManager.success(formatAssociationMessage(payload.associations_created ?? payload.associations_deleted, action === 'assign-bulk-departments' ? 'créée' : 'retirée'));
        } catch (error) {
            FlashManager.error(error.message);
        }
    }

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

    function formatAssociationMessage(count, participle) {
        const total = Number(count) || 0;
        return total === 0 ? 'Aucune association modifiée.' : `${total} association ${participle}${total > 1 ? 's' : ''}.`;
    }

    departmentsTable.on('rowSelectionChanged', refreshAssignedUsers);
    availableUsersTable.on('rowDblClick', (event, row) => updateDepartmentAccess('assign-bulk-departments', row.getData()));
    selectedUsersTable.on('rowDblClick', (event, row) => updateDepartmentAccess('unassign-bulk-departments', row.getData()));
}

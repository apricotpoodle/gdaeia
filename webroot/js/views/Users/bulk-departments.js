/**
 * @file webroot/js/views/Users/bulk-departments.js
 * @description Orchestrateur de l'association d'un périmètre Departments à plusieurs utilisateurs.
 */

import { FlashManager } from '../../core/FlashManager.js';
import { ButtonFactory } from '../../core/Tabulator/ButtonFactory.js';
import { TabulatorFactory } from '../../core/Tabulator/TabulatorFactory.js';

const form = document.getElementById('bulk-departments-form');
const addButtonContainer = document.getElementById('bulk-add-users-button');
const removeButtonContainer = document.getElementById('bulk-remove-users-button');
const addSubmitButton = document.getElementById('bulk-departments-add');
const replaceSubmitButton = document.getElementById('bulk-departments-replace');
const selectedUsers = new Map();
let availableUsersTable;
let selectedUsersTable;

const selectedDepartmentIds = () => Array.from(
    document.querySelectorAll('#bulk-department-ids input[name^="department_ids["]'),
    (input) => Number(input.value),
).filter((id) => Number.isInteger(id) && id > 0);

function updateTransferButtons() {
    const hasAvailableUser = availableUsersTable?.getRows().some((row) => (
        !selectedUsers.has(Number(row.getData().id))
    )) ?? false;

    addButtonContainer.hidden = !hasAvailableUser;
    removeButtonContainer.hidden = selectedUsers.size === 0;
}

function renderSelectedUsers() {
    if (!selectedUsersTable) {
        return;
    }
    selectedUsersTable.replaceData(Array.from(selectedUsers.values()));
    availableUsersTable?.getRows().forEach((row) => {
        const isSelected = selectedUsers.has(Number(row.getData().id));
        row.getElement().style.display = isSelected ? 'none' : '';
    });
    availableUsersTable?.redraw(true);
    addSubmitButton.disabled = selectedUsers.size === 0;
    replaceSubmitButton.disabled = selectedUsers.size === 0;
    updateTransferButtons();
}

function addUsers(rows) {
    rows.forEach((row) => {
        const user = row.getData();
        selectedUsers.set(Number(user.id), user);
        row.deselect();
    });
    renderSelectedUsers();
}

function removeUsers(rows) {
    rows.forEach((row) => selectedUsers.delete(Number(row.getData().id)));
    renderSelectedUsers();
}

async function submitAssociation(event) {
    event.preventDefault();

    const userIds = Array.from(selectedUsers.keys());
    const departmentIds = selectedDepartmentIds();
    const mode = event.submitter?.dataset.associationMode || 'add';
    if (userIds.length === 0 || (departmentIds.length === 0 && mode !== 'replace')) {
        FlashManager.error('Sélectionnez au moins un utilisateur et un département.');
        return;
    }

    if (mode === 'replace' && !window.confirm(
        'Les associations Departments existantes des utilisateurs sélectionnés seront remplacées. Continuer ?',
    )) {
        return;
    }

    addSubmitButton.disabled = true;
    replaceSubmitButton.disabled = true;
    try {
        const csrfToken = document.querySelector('meta[name="csrfToken"]')?.getAttribute('content');
        const response = await fetch('/api/users/bulk-departments.json', {
            method: 'POST',
            headers: {
                Accept: 'application/json',
                'Content-Type': 'application/json',
                'X-CSRF-Token': csrfToken,
                'X-Requested-With': 'XMLHttpRequest',
            },
            body: JSON.stringify({
                user_ids: userIds,
                department_ids: departmentIds,
                association_mode: mode,
            }),
        });
        const payload = await response.json();
        if (!response.ok || !payload.success) {
            throw new Error(payload.message || 'Impossible d’associer les départements.');
        }

        FlashManager.success(`${payload.associations_created} association(s) créée(s).`);
    } catch (error) {
        FlashManager.error(`<strong>Échec :</strong> ${error.message}`);
    } finally {
        addSubmitButton.disabled = selectedUsers.size === 0;
        replaceSubmitButton.disabled = selectedUsers.size === 0;
    }
}

if (form && addButtonContainer && removeButtonContainer && addSubmitButton && replaceSubmitButton) {
    addButtonContainer.innerHTML = ButtonFactory.getCommandButton('addToSelection');
    removeButtonContainer.innerHTML = ButtonFactory.getCommandButton('removeFromSelection');

    availableUsersTable = TabulatorFactory.createBulkAvailableUsersGrid(
        '#bulk-available-users-table',
        selectedUsers,
    );
    selectedUsersTable = TabulatorFactory.createBulkSelectedUsersGrid('#bulk-selected-users-table');

    addButtonContainer.addEventListener('click', () => addUsers(availableUsersTable.getSelectedRows()));
    removeButtonContainer.addEventListener('click', () => removeUsers(selectedUsersTable.getSelectedRows()));
    availableUsersTable.on('rowDblClick', (event, row) => addUsers([row]));
    selectedUsersTable.on('rowDblClick', (event, row) => removeUsers([row]));
    availableUsersTable.on('dataProcessed', updateTransferButtons);
    form.addEventListener('submit', submitAssociation);
    updateTransferButtons();
}

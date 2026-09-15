/**
 * @file Orchestrateur ES6 de l'attribution des options de menu aux rôles.
 * @description Synchronise trois grilles Tabulator et délègue toutes les
 * écritures à l'API sécurisée par les Policies et les finders de visibilité.
 */

import { FlashManager } from '../../core/FlashManager.js';
import { TabulatorFactory } from '../../core/Tabulator/TabulatorFactory.js';

const menusTableElement = document.querySelector('#role-access-menus-table');
const availableRolesTableElement = document.querySelector('#role-access-available-roles-table');
const selectedRolesTableElement = document.querySelector('#role-access-selected-roles-table');

if (menusTableElement && availableRolesTableElement && selectedRolesTableElement) {
    const menusTable = TabulatorFactory.createRoleAccessMenusGrid('#role-access-menus-table');
    const availableRolesTable = TabulatorFactory.createRoleAccessAvailableRolesGrid('#role-access-available-roles-table');
    const selectedRolesTable = TabulatorFactory.createRoleAccessSelectedRolesGrid('#role-access-selected-roles-table');
    const csrfToken = document.querySelector('meta[name="csrfToken"]')?.getAttribute('content') || '';
    /** @type {Array<{id: number|string, name: string}>} Rôles visibles par l'opérateur. */
    let visibleRoles = [];

    /** @returns {number[]} Identifiants des options actuellement sélectionnées. */
    const selectedMenuIds = () => menusTable.getSelectedRows()
        .map((row) => Number(row.getData().id))
        .filter((id) => Number.isInteger(id) && id > 0);

    /**
     * Décode une réponse API et transforme les erreurs HTTP en exception métier.
     *
     * @param {Response} response Réponse Fetch à décoder.
     * @param {string} fallbackMessage Message affiché sans détail API.
     * @returns {Promise<object>} Corps JSON de la réponse réussie.
     */
    async function readJson(response, fallbackMessage) {
        const payload = await response.json();
        if (!response.ok || !payload.success) {
            throw new Error(payload.message || fallbackMessage);
        }
        return payload;
    }

    /**
     * Actualise les rôles communs aux options sélectionnées et le complément disponible.
     *
     * @returns {Promise<void>}
     */
    async function refreshAssignedRoles() {
        const menuIds = selectedMenuIds();
        if (menuIds.length === 0) {
            await selectedRolesTable.replaceData([]);
            await availableRolesTable.replaceData(visibleRoles);
            return;
        }

        try {
            const query = new URLSearchParams();
            menuIds.forEach((id) => query.append('menu_ids[]', String(id)));
            const response = await fetch(`/api/menus/role-access-assigned-roles.json?${query.toString()}`, {
                headers: { Accept: 'application/json' },
            });
            const payload = await response.json();
            if (!response.ok) {
                throw new Error(payload.message || 'Impossible de charger les rôles associés.');
            }
            const assignedRoles = payload.data || [];
            const assignedRoleIds = new Set(assignedRoles.map((role) => Number(role.id)));
            await selectedRolesTable.replaceData(assignedRoles);
            await availableRolesTable.replaceData(
                visibleRoles.filter((role) => !assignedRoleIds.has(Number(role.id))),
            );
        } catch (error) {
            FlashManager.error(error.message);
        }
    }

    /**
     * Charge une fois le périmètre de rôles visible par l'opérateur.
     *
     * @returns {Promise<void>}
     */
    async function loadVisibleRoles() {
        try {
            const response = await fetch('/api/menus/role-access-roles.json', {
                headers: { Accept: 'application/json' },
            });
            const payload = await response.json();
            if (!response.ok) {
                throw new Error(payload.message || 'Impossible de charger les rôles disponibles.');
            }
            visibleRoles = payload.data || [];
            await refreshAssignedRoles();
        } catch (error) {
            FlashManager.error(error.message);
        }
    }

    /**
     * Attribue ou retire un rôle pour les options sélectionnées et leurs descendants.
     *
     * @param {'assign-role-access'|'unassign-role-access'} action Endpoint métier ciblé.
     * @param {{id: number}} role Rôle activé par double-clic.
     * @returns {Promise<void>}
     */
    async function updateRoleAccess(action, role) {
        const menuIds = selectedMenuIds();
        if (menuIds.length === 0) {
            FlashManager.warning('Sélectionnez au moins une option de menu.');
            return;
        }

        try {
            const response = await fetch(`/api/menus/${action}.json`, {
                method: 'POST',
                headers: {
                    Accept: 'application/json',
                    'Content-Type': 'application/json',
                    'X-CSRF-Token': csrfToken,
                    'X-Requested-With': 'XMLHttpRequest',
                },
                body: JSON.stringify({ menu_ids: menuIds, role_id: Number(role.id) }),
            });
            const payload = await readJson(response, 'Impossible de modifier les associations.');
            await refreshAssignedRoles();
            if (action === 'assign-role-access') {
                FlashManager.success(formatAssociationMessage(payload.associations_created, 'créée'));
            } else {
                FlashManager.success(formatAssociationMessage(payload.associations_deleted, 'retirée'));
            }
        } catch (error) {
            FlashManager.error(error.message);
        }
    }

    /**
     * Construit le libellé français singulier/pluriel du résultat d'écriture.
     *
     * @param {number} count Nombre d'associations modifiées.
     * @param {'créée'|'retirée'} participle Participe passé à afficher.
     * @returns {string} Message prêt pour FlashManager.
     */
    function formatAssociationMessage(count, participle) {
        const total = Number(count) || 0;
        if (total === 0) {
            return 'Aucune association modifiée.';
        }

        return `${total} association ${participle}${total > 1 ? 's' : ''}.`;
    }

    menusTable.on('rowSelectionChanged', refreshAssignedRoles);
    availableRolesTable.on('rowDblClick', (event, row) => updateRoleAccess('assign-role-access', row.getData()));
    selectedRolesTable.on('rowDblClick', (event, row) => updateRoleAccess('unassign-role-access', row.getData()));
    loadVisibleRoles();
}

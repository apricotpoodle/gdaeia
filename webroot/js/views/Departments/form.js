import { FlashManager } from '../../core/FlashManager.js';
import { TabulatorFactory } from '../../core/Tabulator/TabulatorFactory.js';
import { TabulatorMutationClient } from '../../core/Tabulator/TabulatorMutationClient.js';
import TreeselectWrapper from '../../core/TreeSelectJS/TreeselectWrapper.js';
import { refreshAssociationTables } from './association-refresh.js';
import { refreshDepartmentSummary } from './department-summary.js';

/** Initialise le sélecteur de département parent. */
function initialiseParentSelector() {
    const container = document.querySelector('#department-parent-tree');
    const dataElement = document.querySelector('#department-parent-options');
    const hiddenInput = document.querySelector('#department-parent-id');
    if (!container || !dataElement || !hiddenInput) {
        return;
    }

    try {
        const data = JSON.parse(dataElement.textContent || '{}');
        new TreeselectWrapper({
            parentContainer: container,
            hiddenInput,
            value: data.value || [],
            options: data.options || [],
            isSingleSelect: true,
            searchable: true,
            clearable: true,
            placeholder: 'Sélectionner un département parent...',
        });
    } catch (error) {
        console.error('Erreur lors de l’initialisation du parent de département.', error);
    }
}

/** Configure les quatre grilles et leurs doubles clics d’association. */
function initialiseAssociations() {
    const departmentId = document.querySelector('#department-id')?.value;
    if (!departmentId) {
        return;
    }

    const grids = [
        {
            association: 'cgrs',
            available: '#department-cgrs-available-table',
            associated: '#department-cgrs-associated-table',
            label: 'CGR',
        },
        {
            association: 'managers',
            available: '#department-managers-available-table',
            associated: '#department-managers-associated-table',
            label: 'responsable',
        },
    ];
    const client = new TabulatorMutationClient();

    grids.forEach(({ association, available, associated, label }) => {
        const availableElement = document.querySelector(available);
        const associatedElement = document.querySelector(associated);
        if (!availableElement || !associatedElement) {
            return;
        }

        const availableTable = TabulatorFactory.createDepartmentAssociationGrid(
            available,
            departmentId,
            association,
            false,
        );
        const associatedTable = TabulatorFactory.createDepartmentAssociationGrid(
            associated,
            departmentId,
            association,
            true,
        );

        const mutate = async (row, action) => {
            const id = Number(row.getData().id);
            if (!Number.isInteger(id) || id < 1) {
                return;
            }

            try {
                await client.send({
                    url: `/api/departments/${departmentId}/${association}/${id}/${action}.json`,
                });
                await refreshAssociationTables([availableTable, associatedTable]);
                FlashManager.success(action === 'add'
                    ? `Le ${label} a été associé.`
                    : `Le ${label} a été dissocié.`);
            } catch (error) {
                FlashManager.error(error.message);
            }
        };

        availableTable.on('rowDblClick', (_event, row) => mutate(row, 'add'));
        associatedTable.on('rowDblClick', (_event, row) => {
            const data = row.getData();
            if (data.can_dissociate === false) {
                FlashManager.warning(
                    data.association_read_only_message || 'Dissociation non autorisée.',
                );
                return;
            }

            mutate(row, 'remove');
        });
    });
}

/** Recharge les résumés de l’onglet Département à chacun de ses retours. */
function initialiseDepartmentSummaryRefresh() {
    const departmentId = document.querySelector('#department-id')?.value;
    const departmentTab = document.querySelector('#department-tab');
    if (!departmentId || !departmentTab) {
        return;
    }

    departmentTab.addEventListener('shown.bs.tab', () => {
        refreshDepartmentSummary(departmentId).catch((error) => FlashManager.error(error.message));
    });
}

initialiseParentSelector();
initialiseAssociations();
initialiseDepartmentSummaryRefresh();

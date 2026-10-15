import { FlashManager } from '../../core/FlashManager.js';
import { TabulatorFactory } from '../../core/Tabulator/TabulatorFactory.js';
import { TabulatorMutationClient } from '../../core/Tabulator/TabulatorMutationClient.js';
import { globalTabulatorObserver } from '../../core/Tabulator/TabulatorObserver.js';

const selector = '#departments-table';
const table = TabulatorFactory.createDepartmentsGrid(selector);
const client = new TabulatorMutationClient();

function appendQueryParam(query, value, key) {
    if (value === null || value === undefined || value === '') return;
    if (Array.isArray(value)) {
        value.forEach((item, index) => appendQueryParam(query, item, key + '[' + index + ']'));
        return;
    }
    if (typeof value === 'object') {
        Object.entries(value).forEach(([childKey, childValue]) => {
            appendQueryParam(query, childValue, key + '[' + childKey + ']');
        });
        return;
    }
    query.append(key, String(value));
}

function currentFilterQuery() {
    const query = new URLSearchParams();
    table.getFilters(true).forEach((filter, index) => {
        appendQueryParam(query, filter.field, 'filters[' + index + '][field]');
        appendQueryParam(query, filter.type, 'filters[' + index + '][type]');
        appendQueryParam(query, filter.value, 'filters[' + index + '][value]');
    });
    return query;
}

table.on('dataTreeRowExpanded', async (row) => {
    const data = row.getData();
    if (data._children_loaded || !data.has_children) return;
    try {
        const query = currentFilterQuery();
        query.set('parent_id', String(data.id));
        const response = await fetch('/api/departments/tree.json?' + query.toString(), { headers: { Accept: 'application/json' } });
        const payload = await response.json();
        if (!response.ok || payload.success !== true) throw new Error(payload.message || 'Impossible de charger les enfants.');
        await row.update({ children: payload.data, _children_loaded: true });
    } catch (error) {
        row.treeCollapse();
        FlashManager.error(error.message);
    }
});

globalTabulatorObserver.subscribe(`${selector}:action:edit`, (department) => {
    window.location.href = `/departments/edit/${department.id}`;
});

globalTabulatorObserver.subscribe(`${selector}:action:deactivate`, async (department) => {
    try {
        const impactResponse = await fetch(`/api/departments/${department.id}/impact.json`, { headers: { Accept: 'application/json' } });
        const impactPayload = await impactResponse.json();
        if (!impactResponse.ok || impactPayload.success !== true) throw new Error(impactPayload.message || 'Impossible de calculer les impacts.');
        const blockers = impactPayload.data.blockers || [];
        if (blockers.length > 0) {
            FlashManager.warning('La désactivation est bloquée par des dépendances actives.');
            return;
        }
        if (!window.confirm(`Désactiver le département « ${department.name} » ?`)) return;
        await client.send({ url: `/api/departments/${department.id}/deactivate.json` });
        await table.replaceData();
        FlashManager.success('Département désactivé.');
    } catch (error) {
        FlashManager.error(error.message);
    }
});

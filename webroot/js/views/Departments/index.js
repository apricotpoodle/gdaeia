import { FlashManager } from '../../core/FlashManager.js';
import { TabulatorFactory } from '../../core/Tabulator/TabulatorFactory.js';
import { TabulatorMutationClient } from '../../core/Tabulator/TabulatorMutationClient.js';
import { globalTabulatorObserver } from '../../core/Tabulator/TabulatorObserver.js';

const selector = '#departments-table';
const table = TabulatorFactory.createDepartmentsGrid(selector);
const client = new TabulatorMutationClient();
const treeStateKey = 'departments-tree-state-v1';
const treeState = loadTreeState();
let clickTimer = null;

function loadTreeState() {
    try {
        const value = JSON.parse(window.localStorage.getItem(treeStateKey) || '[]');
        return new Set(Array.isArray(value) ? value.map(String) : []);
    } catch (error) {
        return new Set();
    }
}

function saveTreeState() {
    window.localStorage.setItem(treeStateKey, JSON.stringify([...treeState]));
}

function rememberExpanded(row) {
    treeState.add(String(row.getData().id));
    saveTreeState();
}

function rememberCollapsed(row) {
    treeState.delete(String(row.getData().id));
    saveTreeState();
}

function restoreTreeState(rows) {
    rows.forEach((row) => {
        const rowId = String(row.getData().id);
        if (treeState.has(rowId)) row.treeExpand();
        restoreTreeState(row.getTreeChildren());
    });
}

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

function hasActiveFilters() {
    return currentFilterQuery().toString() !== '';
}

function expandFilteredTree(row) {
    const children = row.getTreeChildren();
    if (children.length === 0) return;
    row.treeExpand();
    children.forEach(expandFilteredTree);
}

table.on('dataLoaded', () => {
    if (hasActiveFilters()) setTimeout(() => table.getRows().forEach(expandFilteredTree), 0);
    setTimeout(() => restoreTreeState(table.getRows()), 0);
});

table.on('dataTreeRowExpanded', rememberExpanded);
table.on('dataTreeRowCollapsed', rememberCollapsed);

table.on('rowClick', (event, row) => {
    if (event.target.closest?.('.action-menu-btn, .tabulator-data-tree-control')) return;
    window.clearTimeout(clickTimer);
    clickTimer = window.setTimeout(() => row.treeToggle(), 220);
});

table.on('rowDblClick', (event, row) => {
    if (event.target.closest?.('.action-menu-btn')) return;
    window.clearTimeout(clickTimer);
    globalTabulatorObserver.publish(`${selector}:action:edit`, row.getData());
});

globalTabulatorObserver.subscribe(`${selector}:action:create`, () => {
    window.location.href = '/departments/add';
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
        const hardBlockers = blockers.filter((dependency) =>
            ['applicationforms', 'cgr_system'].includes(dependency.key));
        if (hardBlockers.length > 0) {
            FlashManager.warning(`Désactivation impossible : ${hardBlockers.map((item) => item.label).join(', ')}.`);
            return;
        }
        const cleanup = blockers
            .filter((dependency) => dependency.count > 0)
            .map((dependency) => dependency.key);
        const summary = blockers.length === 0
            ? 'Aucune dépendance active détectée.'
            : blockers.map((dependency) => `${dependency.label} : ${dependency.count}`).join('\n');
        const confirmation = [
            `Désactiver le département « ${department.name} » ?`,
            summary,
            cleanup.length > 0
                ? 'Les dépendances listées seront nettoyées. Les utilisateurs seront uniquement détachés, jamais désactivés.'
                : '',
            'Cette opération est réservée au superutilisateur et est irréversible.',
        ].filter(Boolean).join('\n\n');
        if (!window.confirm(confirmation)) return;
        const payload = await client.send({
            url: `/api/departments/${department.id}/deactivate.json`,
            body: JSON.stringify({ cleanup }),
            headers: { 'Content-Type': 'application/json' },
        });
        await table.replaceData();
        const email = payload.email || { sent: 0, failed: 0 };
        const emailMessage = email.failed > 0
            ? ` Désactivation effectuée, mais ${email.failed} courriel(s) n’ont pas été remis.`
            : ' Le compte rendu a été envoyé.';
        FlashManager.success(`Département désactivé.${emailMessage}`);
    } catch (error) {
        FlashManager.error(error.message);
    }
});

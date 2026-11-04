import assert from 'node:assert/strict';
import { test } from 'node:test';
import { TabulatorFactory } from '../../webroot/js/core/Tabulator/TabulatorFactory.js';
import { TabulatorBuilder } from '../../webroot/js/core/Tabulator/TabulatorBuilder.js?v=20261030120000';
import { refreshAssociationTables } from '../../webroot/js/views/Departments/association-refresh.js';
import { refreshDepartmentSummary, renderDepartmentSummary } from '../../webroot/js/views/Departments/department-summary.js';

const previousWindow = globalThis.window;
const previousDocument = globalThis.document;
const originalBuild = TabulatorBuilder.prototype.build;
globalThis.window = { location: { pathname: '/departments' } };
globalThis.document = { querySelector: () => null };
TabulatorBuilder.prototype.build = function () {
    return this.config;
};

function columnMap() {
    const config = TabulatorFactory.createDepartmentsGrid('#departments-table');
    return new Map(config.columns.map(column => [column.field, column]));
}

test.after(() => {
    TabulatorBuilder.prototype.build = originalBuild;
    globalThis.window = previousWindow;
    globalThis.document = previousDocument;
});

test('compacte les responsables et les CGR avec une infobulle complète', () => {
    const columns = columnMap();
    const managers = columns.get('managers');
    const cgrCodes = columns.get('cgr_codes');

    assert.equal(managers.title, 'Responsable(s)');
    assert.equal(managers.width, 100);
    assert.equal(managers.widthGrow, 0);
    assert.equal(managers.widthShrink, 0);
    assert.equal(managers.formatter({ getValue: () => ['Alice', 'Bob'] }), '2 responsable(s)');
    assert.equal(managers.tooltip(null, { getValue: () => ['Alice', 'Bob'] }), 'Alice\nBob');

    assert.equal(cgrCodes.title, 'CGR(s)');
    assert.equal(cgrCodes.width, 70);
    assert.equal(cgrCodes.widthGrow, 0);
    assert.equal(cgrCodes.widthShrink, 0);
    assert.equal(cgrCodes.formatter({ getValue: () => [
        { code: 'CGR-A', label: 'Premier' },
        { code: 'CGR-B', label: 'Second' },
    ] }), '2 CGR');
    assert.equal(cgrCodes.tooltip(null, { getValue: () => [
        { code: 'CGR-A', label: 'Premier' },
        { code: 'CGR-B', label: 'Second' },
    ] }), 'CGR-A\nCGR-B');
    assert.equal(cgrCodes.tooltip(null, { getValue: () => [
        { code: '', label: 'Sans code' },
    ] }), 'Aucun CGR');
});

test('configure les grilles distantes des associations de département', () => {
    const managers = TabulatorFactory.createDepartmentAssociationGrid(
        '#department-managers-available-table',
        2,
        'managers',
        false,
    );
    const cgrs = TabulatorFactory.createDepartmentAssociationGrid(
        '#department-cgrs-associated-table',
        2,
        'cgrs',
        true,
    );

    assert.equal(managers.ajaxURL, '/api/departments/2/managers/available.json');
    assert.equal(managers.paginationMode, 'remote');
    assert.equal(managers.filterMode, 'remote');
    assert.equal(managers.sortMode, 'remote');
    assert.equal(managers.progressiveLoad, 'scroll');
    assert.equal(managers.pagination, undefined);
    const rowElement = { title: '' };
    managers.rowFormatter({ getData: () => ({}), getElement: () => rowElement });
    assert.equal(rowElement.title, 'Double-cliquez pour transférer cette ligne.');
    assert.deepEqual(managers.columns.map((column) => column.field), ['lastname', 'firstname', 'email']);
    assert.equal(cgrs.ajaxURL, '/api/departments/2/cgrs/associated.json');
    assert.deepEqual(cgrs.columns.map((column) => column.field), ['type', 'code', 'label']);
});

test('rend un responsable associe hors perimetre non dissociable', () => {
    const managers = TabulatorFactory.createDepartmentAssociationGrid(
        '#department-managers-associated-table',
        2,
        'managers',
        true,
    );
    const rowElement = { title: '', classList: { added: [], add(value) { this.added.push(value); } } };
    managers.rowFormatter({
        getData: () => ({
            can_dissociate: false,
            association_read_only_message: 'Dissociation non autorisée.',
        }),
        getElement: () => rowElement,
    });

    assert.equal(rowElement.title, 'Dissociation non autorisée.');
    assert.deepEqual(rowElement.classList.added, ['association-row-readonly']);
});

test('vide chaque grille avant de recharger les listes d’association', async () => {
    const events = [];
    const tables = [1, 2].map((id) => ({
        clearData: async () => events.push(`clear-${id}`),
        replaceData: async () => events.push(`replace-${id}`),
    }));

    await refreshAssociationTables(tables);

    assert.deepEqual(events, ['clear-1', 'clear-2', 'replace-1', 'replace-2']);
});

test('rafraichit les resumes de l onglet Departement', async () => {
    const elements = new Map([
        ['#department-managers-summary', { textContent: '' }],
        ['#department-cgrs-summary', { textContent: '' }],
    ]);
    const documentRef = { querySelector: (selector) => elements.get(selector) || null };
    const fetchImpl = async (url, options) => {
        assert.equal(url, '/api/departments/2/view.json');
        assert.deepEqual(options.headers, { Accept: 'application/json' });

        return {
            ok: true,
            json: async () => ({
                success: true,
                data: {
                    managers: [{ display_name: 'Alice' }, { display_name: 'Bob' }],
                    cgr_codes: [{ code: 'CGR-A' }, { code: 'CGR-B' }],
                },
            }),
        };
    };

    await refreshDepartmentSummary(2, { fetchImpl, documentRef });

    assert.equal(elements.get('#department-managers-summary').textContent, 'Alice, Bob');
    assert.equal(elements.get('#department-cgrs-summary').textContent, 'CGR-A, CGR-B');
});

test('affiche les valeurs de repli lorsque les associations sont vides', () => {
    const elements = new Map([
        ['#department-managers-summary', { textContent: '' }],
        ['#department-cgrs-summary', { textContent: '' }],
    ]);
    const documentRef = { querySelector: (selector) => elements.get(selector) || null };

    renderDepartmentSummary({ managers: [], cgr_codes: [] }, documentRef);

    assert.equal(elements.get('#department-managers-summary').textContent, 'Aucun responsable');
    assert.equal(elements.get('#department-cgrs-summary').textContent, 'Aucun CGR');
});

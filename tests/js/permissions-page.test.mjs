import assert from 'node:assert/strict';
import { test } from 'node:test';

test('la grille attend un rôle et revient à la première page sans filtre au changement', async (t) => {
    const flushChange = () => new Promise((resolve) => setTimeout(resolve));
    const roleHandlers = new Map();
    const roleSelect = { value: '', addEventListener: (event, handler) => { roleHandlers.set(event, handler); } };
    const gridHost = { hidden: true };
    const elements = { '#permissions-role': roleSelect, '#permissions-catalog': gridHost };
    const requests = [];
    let activeGrid;

    // Tabulator et le navigateur sont les frontières externes de ce test Node.
    t.mock.method(globalThis, 'fetch', async (url) => {
        requests.push(new URL(url, 'https://example.test'));
        return { ok: true, json: async () => ({ success: true, data: [], last_page: 1 }) };
    });
    globalThis.document = { querySelector: (selector) => elements[selector], createElement: () => ({}) };
    globalThis.Tabulator = class {
        constructor(selector, options) {
            assert.equal(selector, '#permissions-catalog');
            assert.equal(options.paginationMode, 'remote');
            assert.equal(options.filterMode, 'remote');
            assert.equal(options.sortMode, 'remote');
            assert.equal(options.height, 'calc(100vh - 240px)');
            this.options = options;
            this.page = 1;
            this.filters = [];
            this.reload();
            activeGrid = this;
        }

        on() {}

        reload() {
            return this.options.ajaxRequestFunc(this.options.ajaxURL, { method: 'GET' }, {
                page: this.page,
                size: this.options.paginationSize,
                [this.options.dataSendParams.filter]: this.filters,
            });
        }

        destroy() { activeGrid = null; }
    };
    t.after(() => {
        delete globalThis.document;
        delete globalThis.Tabulator;
    });

    await import('../../webroot/js/views/Permissions/index.js');

    assert.equal(requests.length, 0);
    assert.equal(gridHost.hidden, true);
    const changeRole = roleHandlers.get('change');
    assert.equal(typeof changeRole, 'function', 'Le sélecteur doit écouter l’événement change.');

    roleSelect.value = '1';
    changeRole();
    await flushChange();
    assert.equal(gridHost.hidden, false);
    assert.equal(requests[0].pathname, '/api/permissions.json');
    assert.equal(requests[0].searchParams.get('role_id'), '1');
    const columns = Object.fromEntries(activeGrid.options.columns.map((column) => [column.field, column]));
    assert.equal(columns.role_permissions.formatter({ getValue: () => [] }).textContent, 'Non attribuée');
    assert.equal(columns.menus.formatter({ getValue: () => [
        { url: '/applicationforms' },
        { url: '/applicationforms/archives' },
    ] }).textContent, '/applicationforms, /applicationforms/archives');

    activeGrid.page = 3;
    activeGrid.filters = [{ field: 'code', type: 'like', value: 'users' }];
    await activeGrid.reload();
    assert.equal(requests[1].searchParams.get('page'), '3');
    assert.equal(requests[1].searchParams.get('filters[0][value]'), 'users');

    roleSelect.value = '2';
    changeRole();
    roleSelect.value = '3';
    changeRole();
    await flushChange();
    assert.deepEqual(Object.fromEntries(requests[2].searchParams), { role_id: '3', page: '1', size: '20' });

    roleSelect.value = '';
    changeRole();
    await flushChange();
    assert.equal(gridHost.hidden, true);
    assert.equal(activeGrid, null);
    assert.equal(requests.length, 3);
});

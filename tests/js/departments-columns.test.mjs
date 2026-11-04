import assert from 'node:assert/strict';
import { test } from 'node:test';
import { TabulatorFactory } from '../../webroot/js/core/Tabulator/TabulatorFactory.js';
import { TabulatorBuilder } from '../../webroot/js/core/Tabulator/TabulatorBuilder.js?v=20261030120000';

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

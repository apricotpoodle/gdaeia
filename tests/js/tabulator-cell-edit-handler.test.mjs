import assert from 'node:assert/strict';
import { test } from 'node:test';
import { TabulatorCellEditHandler } from '../../webroot/js/core/Tabulator/TabulatorCellEditHandler.js';
import { TabulatorBuilder } from '../../webroot/js/core/Tabulator/TabulatorBuilder.js';

function createCell(field = 'access_level', value = 'EDIT') {
    const calls = { update: [], restored: 0 };
    const rowData = { id: 12, access_level: 'VIEW' };
    const row = {
        getData: () => rowData,
        update: async (data) => {
            calls.update.push(data);
            Object.assign(rowData, data);
        },
    };

    return {
        calls,
        getField: () => field,
        getValue: () => value,
        getRow: () => row,
        restoreOldValue: () => { calls.restored += 1; },
    };
}

test('met à jour uniquement la ligne avec la représentation retournée par l’API', async () => {
    const editedCell = createCell();
    const successes = [];
    const handler = new TabulatorCellEditHandler({
        editableFields: ['access_level'],
        requestFactory: currentCell => ({
            url: `/api/field-authorizations/edit/${currentCell.getRow().getData().id}.json`,
        }),
        client: {
            send: async () => ({
                success: true,
                message: 'OK',
                data: { access_level: 'EDIT', role: { name: 'Administrateur' } },
            }),
        },
        onSuccess: payload => successes.push(payload.message),
    });

    await handler.handle(editedCell);

    assert.deepEqual(editedCell.calls.update, [{
        access_level: 'EDIT',
        role: { name: 'Administrateur' },
    }]);
    assert.deepEqual(successes, ['OK']);
    assert.equal(editedCell.calls.restored, 0);
});

test('restaure la valeur précédente en cas d’erreur API', async () => {
    const editedCell = createCell();
    const errors = [];
    const handler = new TabulatorCellEditHandler({
        editableFields: ['access_level'],
        requestFactory: () => ({ url: '/api/field-authorizations/edit/12.json' }),
        client: { send: async () => { throw new Error('Modification refusée.'); } },
        onError: error => errors.push(error.message),
    });

    await handler.handle(editedCell);

    assert.equal(editedCell.calls.restored, 1);
    assert.deepEqual(errors, ['Modification refusée.']);
    assert.deepEqual(editedCell.calls.update, []);
});

test('ignore un champ qui ne fait pas partie de la stratégie', async () => {
    const editedCell = createCell('resource', 'Users');
    let called = false;
    const handler = new TabulatorCellEditHandler({
        editableFields: ['access_level'],
        requestFactory: () => ({ url: '/never-called' }),
        client: { send: async () => { called = true; } },
    });

    await handler.handle(editedCell);

    assert.equal(called, false);
    assert.equal(editedCell.calls.restored, 0);
});

test('branche le handler sur l’événement externe cellEdited de Tabulator', () => {
    const builder = new TabulatorBuilder('#grid');
    const editedCell = {};
    const handledCells = [];
    const handler = { handle: async (cell) => handledCells.push(cell) };
    const subscriptions = [];
    const table = {
        on: (eventName, callback) => subscriptions.push({ eventName, callback }),
    };

    builder.setCellEditHandler(handler);
    builder.attachCellEditHandler(table);

    assert.equal(subscriptions.length, 1);
    assert.equal(subscriptions[0].eventName, 'cellEdited');
    subscriptions[0].callback(editedCell);
    assert.deepEqual(handledCells, [editedCell]);
});

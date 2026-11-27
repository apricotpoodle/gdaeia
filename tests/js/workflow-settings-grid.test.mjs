import assert from 'node:assert/strict';
import { test } from 'node:test';
import { TabulatorCellEditHandler } from '../../webroot/js/core/Tabulator/TabulatorCellEditHandler.js';
import {
    createSettingColumns,
    createSettingEditor,
    createWorkflowSettingRequest,
} from '../../webroot/js/views/WorkflowSettings/settings-grid.js';

function createInputDocument() {
    return {
        lastCreated: null,
        createElement() {
            this.lastCreated = {
                type: '', value: '', checked: false, min: '', step: '', required: false,
                listeners: {},
                get valueAsNumber() { return this.value === '' ? Number.NaN : Number(this.value); },
                addEventListener(name, callback) { this.listeners[name] = callback; },
                setAttribute() {},
                classList: { add() {} },
                focus() {},
                select() {},
            };
            return this.lastCreated;
        },
    };
}

function makeCell(setting, value) {
    return {
        getValue: () => value,
        getRow: () => ({ getData: () => setting }),
    };
}

test('choisit un contrôle de saisie adapté aux quatre types de valeur', () => {
    const documentRef = createInputDocument();
    const expectedTypes = [
        ['number', 'number'],
        ['boolean', 'checkbox'],
        ['date', 'date'],
        ['text', 'text'],
    ];

    for (const [type, expectedInputType] of expectedTypes) {
        const editor = createSettingEditor(type, { documentRef });
        editor(
            makeCell({ value: type === 'boolean' ? true : '2026-01-15' }, type === 'boolean' ? true : '2026-01-15'),
            () => {},
            () => {},
            () => {},
        );
        const editorInput = documentRef.lastCreated;
        assert.equal(editorInput?.type, expectedInputType);
    }
});

test('valide un booléen immédiatement après son basculement', () => {
    const documentRef = createInputDocument();
    let accepted;
    const editor = createSettingEditor('boolean', { documentRef });
    const input = editor(
        makeCell({ value: false }, false),
        () => {},
        value => { accepted = value; },
        () => {},
    );

    input.checked = true;
    input.listeners.change();

    assert.equal(accepted, true);
});

test('valide un nombre quand l’éditeur est confirmé ou quitté', () => {
    const documentRef = createInputDocument();
    let accepted;
    const editor = createSettingEditor('number', { documentRef });
    const input = editor(
        makeCell({ label: 'Délai' }, 48),
        () => {},
        value => { accepted = value; },
        () => {},
    );

    input.value = '36';
    input.listeners.blur();

    assert.equal(accepted, 36);
});

test('ne valide pas deux fois une saisie confirmée puis quittée', () => {
    const documentRef = createInputDocument();
    let accepted = 0;
    const input = createSettingEditor('text', { documentRef })(
        makeCell({ label: 'Aide' }, 'Texte'),
        () => {},
        () => { accepted += 1; },
        () => {},
    );

    input.value = 'Nouveau texte';
    input.listeners.keydown({ key: 'Enter', preventDefault() {} });
    input.listeners.blur();

    assert.equal(accepted, 1);
});

test('annule une saisie avec Échap sans appeler la sauvegarde', () => {
    const documentRef = createInputDocument();
    let accepted = 0;
    let cancelled = 0;
    const input = createSettingEditor('date', { documentRef })(
        makeCell({ label: 'Date' }, '2026-01-15'),
        () => {},
        () => { accepted += 1; },
        () => { cancelled += 1; },
    );

    input.listeners.keydown({ key: 'Escape', preventDefault() {} });
    input.listeners.blur();

    assert.equal(cancelled, 1);
    assert.equal(accepted, 0);
});

test('enregistre la cellule avec son endpoint et son champ existants', () => {
    const setting = { key: 'validation.default_due_hours', value: 48 };
    const request = createWorkflowSettingRequest(setting, 36, [setting]);

    assert.equal(request.url, '/api/workflow-settings/default-due-hours.json');
    assert.equal(request.method, 'POST');
    assert.equal(request.headers['Content-Type'], 'application/json');
    assert.deepEqual(JSON.parse(request.body), { default_due_hours: 36 });
});

test('garde l’autre obligation de commentaire lors de la sauvegarde d’une cellule', () => {
    const accept = { key: 'validation.comment_required.accept', value: true };
    const request = createWorkflowSettingRequest(accept, false);

    assert.equal(request.url, '/api/workflow-settings/comment-requirements.json');
    assert.deepEqual(JSON.parse(request.body), { accepter: false });
});

test('envoie le coefficient vers son endpoint dédié', () => {
    const setting = { key: 'salary.k_coefficient', value: 17.46 };
    const request = createWorkflowSettingRequest(setting, 18.25, [setting]);

    assert.equal(request.url, '/api/workflow-settings/k-coefficient.json');
    assert.deepEqual(JSON.parse(request.body), { k_coefficient: 18.25 });
});

test('refuse l’édition locale d’une ligne interdite', () => {
    const valueColumn = createSettingColumns({ documentRef: createInputDocument() })
        .find(column => column.field === 'value');

    assert.equal(valueColumn.editable({ getRow: () => ({ getData: () => ({ canEdit: false }) }) }), false);
    assert.equal(valueColumn.editable({ getRow: () => ({ getData: () => ({ canEdit: true }) }) }), true);
});

test('conserve le libellé et la valeur avant l’aide sur petit écran', () => {
    const columns = createSettingColumns({ documentRef: createInputDocument() });
    const responsivePriorities = Object.fromEntries(columns.map(column => [column.field, column.responsive]));

    assert.deepEqual(responsivePriorities, { label: 0, help: 2, value: 0 });
});

test('restaure la cellule si la sauvegarde inline est refusée par le serveur', async () => {
    const setting = { key: 'validation.blocked_after_business_days', value: 3 };
    const cell = makeCell(setting, 5);
    const requests = [];
    let restored = 0;
    const handler = new TabulatorCellEditHandler({
        requestFactory: editedCell => createWorkflowSettingRequest(
            editedCell.getRow().getData(),
            editedCell.getValue(),
        ),
        client: {
            send: async request => {
                requests.push(request);
                throw new Error('Le délai doit être un entier positif.');
            },
        },
        onError: () => {},
    });
    cell.restoreOldValue = () => { restored += 1; };

    await handler.handle(cell);

    assert.equal(requests[0].url, '/api/workflow-settings/blocked-after-business-days.json');
    assert.deepEqual(JSON.parse(requests[0].body), { blocked_after_business_days: 5 });
    assert.equal(restored, 1);
});

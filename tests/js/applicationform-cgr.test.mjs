import assert from 'node:assert/strict';
import { test } from 'node:test';
import { isStructuredCgrValue } from '../../webroot/js/views/Applicationforms/applicationform-cgr-utils.js';

let cgrModule;
test('expose une initialisation CGR testable', async () => {
    globalThis.document = { addEventListener() {} };
    cgrModule = await import('../../webroot/js/views/Applicationforms/applicationform-cgr.js');
    delete globalThis.document;
    assert.equal(typeof cgrModule.initializeCgrForm, 'function');
});

class FakeClassList {
    values = new Set();

    add(...values) {
        values.forEach(value => this.values.add(value));
    }

    remove(...values) {
        values.forEach(value => this.values.delete(value));
    }
}

class FakeElement {
    children = [];
    listeners = new Map();
    classList = new FakeClassList();
    dataset = {};
    value = '';
    textContent = '';
    hidden = false;
    readOnly = false;
    disabled = false;
    className = '';

    constructor(tagName = 'div') {
        this.tagName = tagName.toUpperCase();
    }

    set innerHTML(value) {
        this.children = [];
        this._innerHTML = value;
    }

    get innerHTML() {
        return this._innerHTML || '';
    }

    appendChild(child) {
        this.children.push(child);
        return child;
    }

    replaceChildren(...children) {
        this.children = children;
    }

    querySelectorAll(selector) {
        const className = selector.slice(1);
        return this.children.filter(child => child.className.split(/\s+/).includes(className));
    }

    addEventListener(type, callback) {
        const callbacks = this.listeners.get(type) || [];
        callbacks.push(callback);
        this.listeners.set(type, callbacks);
    }

    setAttribute(name, value) {
        this[name] = value;
    }

    removeAttribute(name) {
        delete this[name];
    }

    dispatchEvent() {
        return true;
    }

    dispatch(type) {
        (this.listeners.get(type) || []).forEach(callback => callback(new Event(type)));
    }
}

function createHarness({ departmentId = '1', value = '', disabled = false, canLoadConfig = true, fetcher }) {
    const departmentSelect = new FakeElement('input');
    departmentSelect.value = departmentId;
    const cgrContainer = new FakeElement();
    const cgrFinalInput = new FakeElement('input');
    cgrFinalInput.value = value;
    cgrFinalInput.disabled = disabled;
    const errorMessage = new FakeElement('div');
    errorMessage.hidden = true;
    errorMessage.textContent = 'La configuration du CGR n’a pas pu être chargée.';

    const controller = cgrModule.initializeCgrForm({
        departmentSelect,
        cgrContainer,
        cgrFinalInput,
        errorMessage,
        canLoadConfig,
        fetcher,
        document: { createElement: tagName => new FakeElement(tagName) },
    });

    return { controller, departmentSelect, cgrContainer, cgrFinalInput, errorMessage };
}

function response(schema, options) {
    return { ok: true, json: async () => ({ schema, options }) };
}

function deferred() {
    let resolve;
    const promise = new Promise((resolvePromise) => {
        resolve = resolvePromise;
    });

    return { promise, resolve };
}

test('charge le CGR unique AAN02 du demandeur sans afficher d’erreur', async () => {
    const calls = [];
    const harness = createHarness({
        departmentId: '2',
        fetcher: async (url) => {
            calls.push(url);
            return response(['ANALYTIQUE'], {
                ANALYTIQUE: [{ code: 'AAN02', label: 'AAN02 - Développement Éditorial' }],
            });
        },
    });

    await harness.controller.ready;

    assert.deepEqual(calls, ['/api/applicationforms/getCgrConfig/2.json']);
    assert.equal(harness.cgrFinalInput.value, 'AAN02');
    assert.equal(harness.cgrFinalInput.readOnly, true);
    assert.equal(harness.cgrContainer.children.length, 0);
    assert.equal(harness.errorMessage.hidden, true);
});

test('conserve le CGR existant sans erreur si le chargement est interdit', async () => {
    const calls = [];
    const harness = createHarness({
        departmentId: '2',
        value: 'AAN02',
        canLoadConfig: false,
        fetcher: async (url) => {
            calls.push(url);
            return { ok: false, status: 403 };
        },
    });

    await harness.controller.ready;

    assert.deepEqual(calls, []);
    assert.equal(harness.cgrFinalInput.value, 'AAN02');
    assert.equal(harness.cgrFinalInput.readOnly, true);
    assert.equal(harness.errorMessage.hidden, true);
});

test('vide la valeur pendant le chargement et recharge chaque département', async () => {
    const calls = [];
    const harness = createHarness({
        value: 'A1-N2',
        fetcher: async (url) => {
            calls.push(url);
            return response(['AXE'], { AXE: [{ code: 'A2', label: 'A2' }] });
        },
    });

    await harness.controller.ready;
    harness.departmentSelect.value = '2';
    harness.departmentSelect.dispatch('change');

    assert.equal(harness.cgrFinalInput.value, '');
    await new Promise(setImmediate);

    assert.deepEqual(calls, [
        '/api/applicationforms/getCgrConfig/1.json',
        '/api/applicationforms/getCgrConfig/2.json',
    ]);
    assert.equal(harness.cgrFinalInput.value, 'A2');
});

test('ignore une réponse CGR arrivée après celle du département courant', async () => {
    const olderRequest = deferred();
    const newerRequest = deferred();
    const harness = createHarness({
        departmentId: '',
        fetcher: (url) => url.endsWith('/1.json') ? olderRequest.promise : newerRequest.promise,
    });

    const olderLoad = harness.controller.refresh('1');
    const newerLoad = harness.controller.refresh('2');
    newerRequest.resolve(response(['AXE'], { AXE: [{ code: 'A2', label: 'A2' }] }));
    await newerLoad;
    olderRequest.resolve(response(['AXE'], { AXE: [{ code: 'A1', label: 'A1' }] }));
    await olderLoad;

    assert.equal(harness.cgrFinalInput.value, 'A2');
});

test('affiche les sélecteurs uniquement pour plusieurs CGR complets', async () => {
    const harness = createHarness({
        fetcher: async () => response(['AXE', 'NIVEAU'], {
            AXE: [{ code: 'A1', label: 'A1' }, { code: 'A2', label: 'A2' }],
            NIVEAU: [{ code: 'N1', label: 'N1' }, { code: 'N2', label: 'N2' }],
        }),
    });

    await harness.controller.ready;

    const [axe, niveau] = harness.cgrContainer.querySelectorAll('.cgr-segment-select');
    assert.equal(harness.cgrContainer.children.length, 2);
    axe.value = 'A2';
    axe.dispatch('change');
    niveau.value = 'N1';
    niveau.dispatch('change');
    assert.equal(harness.cgrFinalInput.value, 'A2-N1');
});

test('utilise un champ libre sans CGR complet ou quand un CGR libre existe déjà', async () => {
    const payloads = [
        { schema: [], options: {} },
        { schema: ['AXE', 'NIVEAU'], options: { AXE: [{ code: 'A1' }], NIVEAU: [] } },
        { schema: ['AXE'], options: { AXE: [{ code: 'A1' }, { code: 'A2' }] }, value: 'CGR libre' },
    ];

    for (const payload of payloads) {
        const harness = createHarness({
            value: payload.value || '',
            fetcher: async () => response(payload.schema, payload.options),
        });
        await harness.controller.ready;

        assert.equal(harness.cgrContainer.children.length, 0);
        assert.equal(harness.cgrFinalInput.readOnly, false);
        assert.equal(harness.cgrFinalInput.value, payload.value || '');
    }
});

test('affiche une erreur française si la configuration CGR échoue', async () => {
    const harness = createHarness({ fetcher: async () => { throw new Error('offline'); } });

    await harness.controller.ready;

    assert.equal(harness.errorMessage.hidden, false);
    assert.match(harness.errorMessage.textContent, /configuration du CGR n’a pas pu être chargée/);
    assert.equal(harness.cgrFinalInput.readOnly, false);
});

test('conserve les droits de lecture seule sur les sélecteurs CGR', async () => {
    const harness = createHarness({
        disabled: true,
        fetcher: async () => response(['AXE'], {
            AXE: [{ code: 'A1', label: 'A1' }, { code: 'A2', label: 'A2' }],
        }),
    });

    await harness.controller.ready;

    assert.equal(harness.cgrFinalInput.disabled, true);
    assert.equal(harness.cgrContainer.children.length, 1);
    assert.equal(harness.cgrContainer.querySelectorAll('.cgr-segment-select')[0].disabled, true);
});

test('reconnaît un CGR correspondant à tous les segments disponibles', () => {
    assert.equal(isStructuredCgrValue('A1-N2', ['AXE', 'NIVEAU'], {
        AXE: [{ code: 'A1' }],
        NIVEAU: [{ code: 'N2' }],
    }), true);
});

test('considère un CGR libre comme non structuré', () => {
    assert.equal(isStructuredCgrValue('VANILLE', ['AXE'], {
        AXE: [{ code: 'A1' }],
    }), false);
});

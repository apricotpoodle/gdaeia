import assert from 'node:assert/strict';
import test from 'node:test';

async function initializeForm({ departmentId = '', canLoad = true, responses = [] } = {}) {
    let onReady;
    const hiddenInputListeners = [];
    const hiddenInput = {
        value: departmentId,
        addEventListener(type, listener) {
            if (type === 'change') hiddenInputListeners.push(listener);
        },
        dispatchChange(value) {
            this.value = value;
            return Promise.all(hiddenInputListeners.map((listener) => listener()));
        },
    };
    const managersContainer = {
        dataset: {
            managersUrl: '/api/applicationforms/managers-for-department.json',
            canLoad: String(canLoad),
        },
        textContent: 'Responsable initial',
    };
    const errorMessage = { hidden: true };
    const document = {
        addEventListener: (_type, listener) => { onReady = listener; },
        getElementById: (id) => ({
            'department-id': hiddenInput,
            'department-tree-select': null,
            'department-managers': managersContainer,
            'department-managers-error': errorMessage,
        })[id] ?? null,
        querySelector: () => null,
    };
    const previous = {
        document: globalThis.document,
        window: globalThis.window,
        fetch: globalThis.fetch,
        consoleError: console.error,
    };
    globalThis.document = document;
    globalThis.window = { location: { origin: 'https://example.test' } };
    let fetchIndex = 0;
    const fetchCalls = [];
    globalThis.fetch = (...args) => {
        fetchCalls.push(args);
        return responses[fetchIndex++](...args);
    };

    const moduleUrl = new URL('../../webroot/js/views/Applicationforms/applicationform-treeselect.js', import.meta.url);
    await import(`${moduleUrl.href}?test=${Math.random()}`);
    await onReady();

    return {
        errorMessage,
        fetchCalls,
        hiddenInput,
        managersContainer,
        restore() {
            for (const [key, value] of Object.entries({
                document: previous.document,
                window: previous.window,
                fetch: previous.fetch,
            })) {
                if (value === undefined) delete globalThis[key];
                else globalThis[key] = value;
            }
            console.error = previous.consoleError;
        },
    };
}

function response(data) {
    return Promise.resolve({ ok: true, json: async () => ({ success: true, data }) });
}

async function flushPromises() {
    await Promise.resolve();
    await Promise.resolve();
}

test('charge les responsables du département sélectionné au chargement', async (t) => {
    const form = await initializeForm({
        departmentId: '1',
        responses: [() => response(['Responsable A', 'Responsable B'])],
    });
    t.after(form.restore);

    await flushPromises();

    assert.equal(form.managersContainer.textContent, 'Responsable A, Responsable B');
    assert.equal(new URL(form.fetchCalls[0][0]).searchParams.get('department_id'), '1');
});

test('actualise les responsables après un changement de département', async (t) => {
    const form = await initializeForm({
        departmentId: '1',
        responses: [
            () => response(['Responsable A']),
            () => response(['Responsable B']),
        ],
    });
    t.after(form.restore);
    await flushPromises();

    await form.hiddenInput.dispatchChange('2');

    assert.equal(form.managersContainer.textContent, 'Responsable B');
    assert.equal(new URL(form.fetchCalls[1][0]).searchParams.get('department_id'), '2');
});

test('efface les responsables précédents pendant le chargement', async (t) => {
    let resolveManagers;
    const form = await initializeForm({
        departmentId: '1',
        responses: [() => new Promise((resolve) => { resolveManagers = resolve; })],
    });
    t.after(form.restore);
    await flushPromises();

    assert.equal(form.managersContainer.textContent, '');
    resolveManagers({ ok: true, json: async () => ({ success: true, data: ['Responsable A'] }) });
    await flushPromises();
    assert.equal(form.managersContainer.textContent, 'Responsable A');
});

test('ignore la réponse tardive des responsables d’un ancien département', async (t) => {
    let resolveOld;
    const form = await initializeForm({
        responses: [
            () => new Promise((resolve) => { resolveOld = resolve; }),
            () => response(['Responsable récent']),
        ],
    });
    t.after(form.restore);

    const oldResponse = form.hiddenInput.dispatchChange('1');
    await form.hiddenInput.dispatchChange('2');
    resolveOld({ ok: true, json: async () => ({ success: true, data: ['Responsable obsolète'] }) });
    await oldResponse;

    assert.equal(form.managersContainer.textContent, 'Responsable récent');
});

test('affiche l’absence de responsable lorsque la liste est vide', async (t) => {
    const form = await initializeForm({
        departmentId: '1',
        responses: [() => response([])],
    });
    t.after(form.restore);
    await flushPromises();

    assert.equal(form.managersContainer.textContent, 'Aucun responsable associé');
});

test('affiche une erreur si la liste des responsables ne peut pas être chargée', async (t) => {
    const previousConsoleError = console.error;
    console.error = () => {};
    t.after(() => { console.error = previousConsoleError; });
    const form = await initializeForm({
        departmentId: '1',
        responses: [() => Promise.reject(new Error('Échec réseau'))],
    });
    t.after(form.restore);
    await flushPromises();

    assert.equal(form.managersContainer.textContent, '');
    assert.equal(form.errorMessage.hidden, false);
});

test('ne charge pas les responsables lorsque le département est en lecture seule', async (t) => {
    const form = await initializeForm({ departmentId: '1', canLoad: false });
    t.after(form.restore);

    assert.equal(form.fetchCalls.length, 0);
    assert.equal(form.managersContainer.textContent, 'Responsable initial');
});

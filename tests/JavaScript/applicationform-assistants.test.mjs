import assert from 'node:assert/strict';
import { test } from 'node:test';

function makeSelect({ value = '', dataset = {} } = {}) {
    let selectedValue = value;
    const options = [{ value: '' }];
    const listeners = {};

    return {
        dataset,
        disabled: true,
        addEventListener(type, listener) {
            listeners[type] = listener;
        },
        dispatchChange(value) {
            this.value = value;
            return listeners.change?.();
        },
        replaceChildren(...nextOptions) {
            options.splice(0, options.length, ...nextOptions);
            selectedValue = '';
        },
        add(option) {
            options.push(option);
        },
        get options() {
            return options;
        },
        get value() {
            return selectedValue;
        },
        set value(nextValue) {
            selectedValue = options.some((option) => String(option.value) === String(nextValue))
                ? String(nextValue)
                : '';
        },
    };
}

async function initializeForm({
    departmentId = '',
    assistantId = '',
    canEdit = true,
    canEditManagers = false,
    responses = [],
} = {}) {
    let onReady;
    const hiddenInputListeners = {};
    const hiddenInput = {
        value: departmentId,
        addEventListener(type, listener) {
            hiddenInputListeners[type] ??= [];
            hiddenInputListeners[type].push(listener);
        },
        dispatchChange(value) {
            this.value = value;
            return Promise.all((hiddenInputListeners.change ?? []).map((listener) => listener()));
        },
    };
    const assistantSelect = makeSelect({
        value: assistantId,
        dataset: {
            assistantsUrl: '/api/applicationforms/assistants-for-department.json',
            defaultAssistantId: '10',
            canEdit: String(canEdit),
        },
    });
    const emptyMessage = { hidden: true };
    const errorMessage = { hidden: true };
    const managersContainer = {
        dataset: {
            managersUrl: '/api/applicationforms/managers-for-department.json',
            canLoad: String(canEditManagers),
        },
        textContent: 'Responsable initial',
    };
    const managersError = { hidden: true };
    const document = {
        addEventListener: (_type, listener) => { onReady = listener; },
        getElementById: (id) => ({
            'department-id': hiddenInput,
            'department-tree-select': null,
            'assistant-options-empty': emptyMessage,
            'assistant-options-error': errorMessage,
            'department-managers': managersContainer,
            'department-managers-error': managersError,
        })[id] ?? null,
        querySelector: (selector) => selector === 'select[name="assistant_id"]' ? assistantSelect : null,
    };
    const previous = {
        document: globalThis.document,
        window: globalThis.window,
        Option: globalThis.Option,
        fetch: globalThis.fetch,
    };
    globalThis.document = document;
    globalThis.window = { location: { origin: 'https://example.test' } };
    globalThis.Option = class {
        constructor(text, value) {
            this.text = text;
            this.value = String(value);
        }
    };
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
        assistantSelect,
        emptyMessage,
        errorMessage,
        managersContainer,
        managersError,
        fetchCalls,
        hiddenInput,
        restore() {
            for (const [key, value] of Object.entries(previous)) {
                if (value === undefined) delete globalThis[key];
                else globalThis[key] = value;
            }
        },
    };
}

function response(data) {
    return Promise.resolve({ ok: true, json: async () => data });
}

test('désactive le sélecteur tant qu’aucun département n’est choisi', async (t) => {
    const form = await initializeForm();
    t.after(form.restore);

    assert.equal(form.assistantSelect.disabled, true);
    assert.equal(form.assistantSelect.options.length, 1);
});

test('ne charge pas les assistantes lorsque le champ est en lecture seule', async (t) => {
    const form = await initializeForm({
        departmentId: '1',
        canEdit: false,
        responses: [() => { throw new Error('Aucune requête attendue'); }],
    });
    t.after(form.restore);

    assert.equal(form.assistantSelect.disabled, true);
    assert.equal(form.errorMessage.hidden, true);
});

test('charge les assistantes et présélectionne l’opérateur seulement s’il est éligible', async (t) => {
    const form = await initializeForm({
        departmentId: '2',
        responses: [() => response({
            success: true,
            data: [
                { id: 10, display_name: 'Assistante 10' },
                { id: 11, display_name: 'Assistante 11' },
            ],
        })],
    });
    t.after(form.restore);

    assert.equal(form.assistantSelect.disabled, false);
    assert.equal(form.assistantSelect.value, '10');
    assert.deepEqual(form.assistantSelect.options.map((option) => option.value), ['', '10', '11']);
});

test('ignore la réponse tardive d’un département précédent', async (t) => {
    let resolveOld;
    const form = await initializeForm({
        responses: [
            () => new Promise((resolve) => { resolveOld = resolve; }),
            () => response({ success: true, data: [{ id: 20, display_name: 'Assistante 20' }] }),
        ],
    });
    t.after(form.restore);

    const oldResponse = form.hiddenInput.dispatchChange('1');
    const newResponse = form.hiddenInput.dispatchChange('2');
    await newResponse;
    resolveOld({
        ok: true,
        json: async () => ({ success: true, data: [{ id: 10, display_name: 'Assistante 10' }] }),
    });
    await oldResponse;

    assert.deepEqual(form.assistantSelect.options.map((option) => option.value), ['', '20']);
    assert.equal(form.assistantSelect.value, '');
});

test('conserve la sélection encore éligible après un changement de département', async (t) => {
    const form = await initializeForm({
        departmentId: '1',
        assistantId: '11',
        responses: [
            () => response({ success: true, data: [{ id: 11, display_name: 'Assistante 11' }] }),
            () => response({ success: true, data: [{ id: 10, display_name: 'Assistante 10' }, { id: 11, display_name: 'Assistante 11' }] }),
        ],
    });
    t.after(form.restore);

    assert.equal(form.assistantSelect.value, '11');
    await form.hiddenInput.dispatchChange('2');

    assert.equal(form.assistantSelect.value, '11');
});

test('conserve la sélection si une réponse précédente arrive après un changement rapide', async (t) => {
    let resolvePreviousDepartment;
    const form = await initializeForm({
        departmentId: '1',
        assistantId: '11',
        responses: [
            () => new Promise((resolve) => { resolvePreviousDepartment = resolve; }),
            () => response({
                success: true,
                data: [{ id: 10, display_name: 'Assistante 10' }, { id: 11, display_name: 'Assistante 11' }],
            }),
        ],
    });
    t.after(form.restore);

    await form.hiddenInput.dispatchChange('2');
    assert.equal(form.assistantSelect.value, '11');

    resolvePreviousDepartment({
        ok: true,
        json: async () => ({ success: true, data: [{ id: 11, display_name: 'Assistante 11' }] }),
    });
    await Promise.resolve();
});

test('affiche un message lorsque le département n’a aucune assistante éligible', async (t) => {
    const form = await initializeForm({
        departmentId: '3',
        responses: [() => response({ success: true, data: [] })],
    });
    t.after(form.restore);

    assert.equal(form.emptyMessage.hidden, false);
    assert.equal(form.assistantSelect.value, '');
});

test('charge les responsables du département sélectionné au chargement', async (t) => {
    const form = await initializeForm({
        departmentId: '1',
        canEdit: false,
        canEditManagers: true,
        responses: [() => response({ success: true, data: ['Responsable A', 'Responsable B'] })],
    });
    t.after(form.restore);

    await Promise.resolve();

    assert.equal(form.managersContainer.textContent, 'Responsable A, Responsable B');
    assert.equal(form.fetchCalls.length, 1);
});

test('actualise les responsables après un changement de département', async (t) => {
    const form = await initializeForm({
        departmentId: '1',
        canEdit: false,
        canEditManagers: true,
        responses: [
            () => response({ success: true, data: ['Responsable A'] }),
            () => response({ success: true, data: ['Responsable B'] }),
        ],
    });
    t.after(form.restore);

    await Promise.resolve();
    await form.hiddenInput.dispatchChange('2');

    assert.equal(form.managersContainer.textContent, 'Responsable B');
    assert.equal(new URL(form.fetchCalls[1][0]).searchParams.get('department_id'), '2');
});

test('efface les responsables précédents pendant le chargement', async (t) => {
    let resolveManagers;
    const form = await initializeForm({
        departmentId: '1',
        canEdit: false,
        canEditManagers: true,
        responses: [() => new Promise((resolve) => { resolveManagers = resolve; })],
    });
    t.after(form.restore);

    await Promise.resolve();

    assert.equal(form.managersContainer.textContent, '');
    resolveManagers({ ok: true, json: async () => ({ success: true, data: ['Responsable A'] }) });
    await Promise.resolve();
    await Promise.resolve();
    assert.equal(form.managersContainer.textContent, 'Responsable A');
});

test('ignore la réponse tardive des responsables d’un ancien département', async (t) => {
    let resolveOld;
    const form = await initializeForm({
        canEdit: false,
        canEditManagers: true,
        responses: [
            () => new Promise((resolve) => { resolveOld = resolve; }),
            () => response({ success: true, data: ['Responsable récent'] }),
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
        canEdit: false,
        canEditManagers: true,
        responses: [() => response({ success: true, data: [] })],
    });
    t.after(form.restore);

    await Promise.resolve();

    assert.equal(form.managersContainer.textContent, 'Aucun responsable associé');
});

test('affiche une erreur si la liste des responsables ne peut pas être chargée', async (t) => {
    const previousConsoleError = console.error;
    console.error = () => {};
    t.after(() => { console.error = previousConsoleError; });
    const form = await initializeForm({
        departmentId: '1',
        canEdit: false,
        canEditManagers: true,
        responses: [() => Promise.reject(new Error('Échec réseau'))],
    });
    t.after(form.restore);

    await Promise.resolve();
    await Promise.resolve();

    assert.equal(form.managersContainer.textContent, '');
    assert.equal(form.managersError.hidden, false);
});

test('ne charge pas les responsables lorsque le département est en lecture seule', async (t) => {
    const form = await initializeForm({
        departmentId: '1',
        canEdit: false,
        canEditManagers: false,
    });
    t.after(form.restore);

    assert.equal(form.fetchCalls.length, 0);
    assert.equal(form.managersContainer.textContent, 'Responsable initial');
});

import assert from 'node:assert/strict';
import test from 'node:test';

class FakeOption {
    constructor(text, value) {
        this.text = text;
        this.value = String(value);
    }
}

class FakeSelect {
    constructor() {
        this.dataset = {
            assistantsUrl: '/api/applicationforms/assistants-for-department.json',
            canEdit: 'true',
            defaultAssistantId: '2',
        };
        this.options = [];
        this.value = '';
        this.disabled = true;
        this.listeners = {};
    }

    add(option) {
        this.options.push(option);
    }

    addEventListener(eventName, callback) {
        this.listeners[eventName] = callback;
    }

    replaceChildren(option) {
        this.options = [option];
        this.value = option.value;
    }

    dispatch(eventName) {
        return this.listeners[eventName]?.();
    }
}

const hiddenInput = {
    value: '',
    listeners: {},
    addEventListener(eventName, callback) {
        this.listeners[eventName] = callback;
    },
    dispatch(eventName) {
        return this.listeners[eventName]?.();
    },
};
const assistantSelect = new FakeSelect();
const emptyMessage = { hidden: true };
const errorMessage = { hidden: true };
const readyDocument = {
    addEventListener(eventName, callback) {
        if (eventName === 'DOMContentLoaded') this.onReady = callback;
    },
    getElementById(id) {
        return {
            'department-id': hiddenInput,
            'assistant-options-empty': emptyMessage,
            'assistant-options-error': errorMessage,
        }[id] ?? null;
    },
    querySelector(selector) {
        return selector === 'select[name="assistant_id"]' ? assistantSelect : null;
    },
};

globalThis.document = readyDocument;
globalThis.window = { location: { origin: 'http://localhost' } };
globalThis.Option = FakeOption;

await import('../../webroot/js/views/Applicationforms/applicationform-treeselect.js');

const response = (assistants) => ({
    ok: true,
    json: async () => ({ assistants }),
});

test('le select suit le département, conserve une assistante éligible et ignore une réponse tardive', async () => {
    await readyDocument.onReady();
    assert.equal(assistantSelect.disabled, true);

    globalThis.fetch = async (input) => {
        const departmentId = new URL(input).searchParams.get('department_id');
        if (departmentId === '10' || departmentId === '20') {
            return response({ '2': 'Opératrice', '3': 'Assistante locale' });
        }
        if (departmentId === '21') {
            return response({ '2': 'Opératrice' });
        }
        if (departmentId === '23' || departmentId === '70') {
            return response({ '3': 'Assistante locale' });
        }
        if (departmentId === '80') return response({});

        return new Promise((resolve) => {
            globalThis.resolveOlderDepartment = resolve;
        });
    };

    hiddenInput.value = '10';
    await hiddenInput.dispatch('change');
    assert.equal(assistantSelect.value, '2');

    assistantSelect.value = '3';
    assistantSelect.dispatch('change');
    hiddenInput.value = '20';
    await hiddenInput.dispatch('change');
    assert.equal(assistantSelect.value, '3');

    hiddenInput.value = '21';
    await hiddenInput.dispatch('change');
    assert.equal(assistantSelect.value, '2');

    hiddenInput.value = '23';
    await hiddenInput.dispatch('change');
    assistantSelect.value = '3';
    assistantSelect.dispatch('change');

    hiddenInput.value = '60';
    const olderRequest = hiddenInput.dispatch('change');
    hiddenInput.value = '70';
    await hiddenInput.dispatch('change');
    assert.equal(assistantSelect.value, '3');

    globalThis.resolveOlderDepartment(response({ '8': 'Assistante obsolète' }));
    await olderRequest;
    assert.deepEqual(assistantSelect.options.map((option) => option.value), ['', '3']);

    hiddenInput.value = '80';
    await hiddenInput.dispatch('change');
    assert.equal(assistantSelect.value, '');
    assert.equal(emptyMessage.hidden, false);
    assert.equal(assistantSelect.disabled, false);
});

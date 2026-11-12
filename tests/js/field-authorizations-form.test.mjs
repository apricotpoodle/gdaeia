import assert from 'node:assert/strict';
import { readFileSync } from 'node:fs';
import { test } from 'node:test';

const source = readFileSync(new URL('../../webroot/js/views/FieldAuthorizations/form.js', import.meta.url), 'utf8')
    .replace("import { FlashManager } from '/js/core/FlashManager.js';", 'const FlashManager = { error() {} };');

class Option {
    constructor(text, value, defaultSelected = false, selected = false) {
        this.text = text;
        this.value = value;
        this.defaultSelected = defaultSelected;
        this.selected = selected;
    }
}

class Select {
    constructor() {
        this.value = '';
        this.children = [];
        this.listeners = new Map();
    }

    add(option) {
        this.children.push(option);
        if (option.selected) this.value = option.value;
    }

    replaceChildren(...children) {
        this.children = children;
        this.value = children.find(child => child.selected)?.value || '';
    }

    addEventListener(event, listener) {
        this.listeners.set(event, listener);
    }

    dispatch(event) {
        this.listeners.get(event)?.({ target: this });
    }
}

class Form {
    constructor() {
        this.dataset = {};
        this.listeners = new Map();
        this.type = new Select();
        this.resource = new Select();
        this.target = new Select();
        this.description = { textContent: '' };
    }

    querySelector(selector) {
        return {
            '[name="target_type"]': this.type,
            '[data-target-resource]': this.resource,
            '[data-target-select]': this.target,
            '[data-target-description]': this.description,
        }[selector] || null;
    }

    addEventListener(event, listener) {
        this.listeners.set(event, listener);
    }
}

test('affiche les ressources et les cibles après le choix du type', async () => {
    const form = new Form();
    const previous = {
        document: globalThis.document,
        window: globalThis.window,
        fetch: globalThis.fetch,
        Option: globalThis.Option,
    };

    globalThis.document = { getElementById: id => id === 'field-authorization-form' ? form : null };
    globalThis.window = { location: { origin: 'http://localhost' } };
    globalThis.Option = Option;
    globalThis.fetch = async () => ({
        json: async () => ({
            targets: [
                { target_type: 'field', resource: 'Users', target: 'email', label: 'Courriel' },
                { target_type: 'field', resource: 'Roles', target: 'name', label: 'Nom' },
                { target_type: 'section', resource: 'Applicationforms', target: 'job_section', label: 'Zone métier' },
            ],
        }),
    });

    try {
        const moduleUrl = `data:text/javascript;base64,${Buffer.from(source).toString('base64')}`;
        const { initializeFieldAuthorizationForm } = await import(moduleUrl);
        initializeFieldAuthorizationForm('/api/field-authorizations/add.json');
        await new Promise(resolve => setImmediate(resolve));

        form.type.value = 'field';
        form.type.dispatch('change');

        assert.deepEqual(form.resource.children.map(option => option.value), ['', 'Users', 'Roles']);

        form.resource.value = 'Users';
        form.resource.dispatch('change');

        assert.deepEqual(form.target.children.map(option => option.value), ['', 'email']);
    } finally {
        globalThis.document = previous.document;
        globalThis.window = previous.window;
        globalThis.fetch = previous.fetch;
        globalThis.Option = previous.Option;
    }
});

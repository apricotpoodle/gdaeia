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

async function initializeForm(form, resolveTargets) {
    const previous = {
        document: globalThis.document,
        window: globalThis.window,
        fetch: globalThis.fetch,
        Option: globalThis.Option,
    };
    const requests = [];
    const restore = () => {
        globalThis.document = previous.document;
        globalThis.window = previous.window;
        globalThis.fetch = previous.fetch;
        globalThis.Option = previous.Option;
    };

    try {
        globalThis.document = { getElementById: id => id === 'field-authorization-form' ? form : null };
        globalThis.window = { location: { origin: 'http://localhost' } };
        globalThis.Option = Option;
        globalThis.fetch = async url => {
            const requestUrl = new URL(url);
            requests.push(requestUrl);
            const targets = typeof resolveTargets === 'function'
                ? resolveTargets(requestUrl)
                : resolveTargets;
            return { json: async () => ({ targets }) };
        };

        const moduleUrl = `data:text/javascript;base64,${Buffer.from(source).toString('base64')}`;
        const { initializeFieldAuthorizationForm } = await import(moduleUrl);
        initializeFieldAuthorizationForm('/api/field-authorizations/add.json');
        await new Promise(resolve => setImmediate(resolve));
        return { requests, restore };
    } catch (error) {
        restore();
        throw error;
    }
}

test('filtre les ressources par type et les cibles par ressource', async () => {
    const form = new Form();
    const targets = [
        { target_type: 'field', resource: 'Users', target: 'email', label: 'Courriel' },
        { target_type: 'field', resource: 'Roles', target: 'name', label: 'Nom' },
        { target_type: 'section', resource: 'Applicationforms', target: 'job_section', label: 'Zone métier' },
        { target_type: 'section', resource: 'Roles', target: 'role_section', label: 'Zone des rôles' },
    ];
    const { restore } = await initializeForm(form, targets);

    try {
        form.type.value = 'field';
        form.type.dispatch('change');

        assert.deepEqual(form.resource.children.map(option => option.value), ['', 'Users', 'Roles']);

        form.resource.value = 'Users';
        form.resource.dispatch('change');

        assert.deepEqual(form.target.children.map(option => option.value), ['', 'email']);

        form.type.value = 'section';
        form.type.dispatch('change');

        assert.deepEqual(form.resource.children.map(option => option.value), ['', 'Applicationforms', 'Roles']);
        form.resource.value = 'Roles';
        form.resource.dispatch('change');
        assert.deepEqual(form.target.children.map(option => option.value), ['', 'role_section']);
    } finally {
        restore();
    }
});

test('efface la cible devenue incompatible après le changement de ressource', async () => {
    const form = new Form();
    const { restore } = await initializeForm(form, [
        { target_type: 'field', resource: 'Users', target: 'email', label: 'Courriel', description: 'Adresse utilisateur' },
        { target_type: 'field', resource: 'Roles', target: 'name', label: 'Nom', description: 'Nom du rôle' },
    ]);

    try {
        form.type.value = 'field';
        form.type.dispatch('change');
        form.resource.value = 'Users';
        form.resource.dispatch('change');
        form.target.value = 'email';
        form.target.dispatch('change');
        assert.equal(form.description.textContent, 'Adresse utilisateur');

        form.resource.value = 'Roles';
        form.resource.dispatch('change');

        assert.deepEqual(form.target.children.map(option => option.value), ['', 'name']);
        assert.equal(form.target.value, '');
        assert.equal(form.description.textContent, '');
    } finally {
        restore();
    }
});

test('préserve une cible historique sélectionnée en édition sans la proposer en ajout', async () => {
    const historicalTarget = {
        target_type: 'field', resource: 'Users', target: 'legacy_email', label: 'Courriel historique',
        description: 'Cible conservée pour cette règle existante',
    };
    const activeTarget = {
        target_type: 'field', resource: 'Users', target: 'email', label: 'Courriel',
        description: 'Adresse utilisateur',
    };
    const form = new Form();
    form.dataset.currentType = 'field';
    form.dataset.currentResource = 'Users';
    form.dataset.currentTarget = 'legacy_email';
    const { requests: editRequests, restore: restoreEdit } = await initializeForm(form, requestUrl => [
        activeTarget,
        ...(requestUrl.searchParams.has('current_target') ? [historicalTarget] : []),
    ]);

    try {
        assert.equal(editRequests[0].searchParams.get('current_type'), 'field');
        assert.equal(editRequests[0].searchParams.get('current_resource'), 'Users');
        assert.equal(editRequests[0].searchParams.get('current_target'), 'legacy_email');
        assert.deepEqual(form.target.children.map(option => option.value), ['', 'email', 'legacy_email']);
        assert.equal(form.target.value, 'legacy_email');
        assert.equal(form.description.textContent, 'Cible conservée pour cette règle existante');
    } finally {
        restoreEdit();
    }

    const addForm = new Form();
    const { restore: restoreAdd } = await initializeForm(addForm, [activeTarget]);

    try {
        assert.deepEqual(addForm.target.children.map(option => option.value), ['']);
        addForm.type.value = 'field';
        addForm.type.dispatch('change');
        addForm.resource.value = 'Users';
        addForm.resource.dispatch('change');
        assert.deepEqual(addForm.target.children.map(option => option.value), ['', 'email']);
    } finally {
        restoreAdd();
    }
});

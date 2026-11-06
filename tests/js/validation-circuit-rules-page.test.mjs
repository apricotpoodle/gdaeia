import assert from 'node:assert/strict';
import { test } from 'node:test';
import { initializeCircuitRulesPage } from '../../webroot/js/views/ValidationCircuitRules/circuit-rules-page.js';
import { createDepartmentSelector, ValidationCircuitRulesClient } from '../../webroot/js/views/ValidationCircuitRules/circuit-rules.js';

function node() {
    return {
        value: '', disabled: true, textContent: '', children: [], listeners: {},
        addEventListener(name, callback) { this.listeners[name] = callback; },
        replaceChildren(...children) { this.children = children; },
        append(...children) { this.children.push(...children); },
        ownerDocument: { createElement: () => node() },
        reset() {}, reportValidity: () => true, scrollIntoView() {},
    };
}

function setup(canManageGrants) {
    const elements = new Map();
    const get = (selector) => {
        if (!elements.has(selector)) elements.set(selector, node());
        return elements.get(selector);
    };
    const ruleForm = get('#validation-circuit-rule-form');
    const contract = node();
    const finalRole = node();
    ruleForm.querySelector = (selector) => selector.includes('contracttype') ? contract : finalRole;
    const managerRole = node();
    get('#validation-circuit-manager-form').querySelector = () => managerRole;
    const requests = [];
    let saveFails = false;
    const client = new ValidationCircuitRulesClient({
        csrfTokenProvider: () => 'csrf',
        fetchImpl: async (url, options) => {
            requests.push({ url, options });
            let data = [];
            if (url.endsWith('departments-tree.json')) data = [{ id: 1, name: 'Direction', children: [{ id: 2, name: 'Service' }] }];
            if (url.endsWith('options.json')) data = { contracttypes: [{ id: 6, name: 'Facture' }], roles: [{ id: 7, name: 'SGR' }, { id: 9, name: 'Gestionnaire' }] };
            const failed = saveFails && url.endsWith('add.json');
            return { ok: !failed, status: failed ? 422 : 200, json: async () => ({ success: !failed, data, errors: [], message: failed ? 'Règle existante.' : 'Enregistré.' }) };
        },
    });
    const widgets = [];
    class Wrapper {
        constructor(config) { this.config = config; this.nativeInstance = {}; widgets.push(this); }
        set value(value) { this.current = value; }
        setDisabled(disabled) { this.disabled = disabled; }
    }
    const events = new Map();
    const reloads = [];
    const created = [];
    const flashErrors = [];
    const dependencies = {
        documentRef: { querySelector: (selector) => {
            if (!canManageGrants && selector === '#validation-circuit-manager-form') return null;
            return get(selector);
        } },
        client,
        createSelector: (container, tree, changed) => createDepartmentSelector(container, tree, changed, Wrapper),
        factory: {
            createValidationCircuitRulesGrid: () => { created.push('rules'); return { replaceData: async () => reloads.push('rules') }; },
            createValidationCircuitManagersGrid: () => { created.push('managers'); return { replaceData: async () => reloads.push('managers') }; },
        },
        observer: { subscribe: (name, callback) => events.set(name, callback) },
        flash: { success() {}, error: (message) => flashErrors.push(message) },
        confirmAction: () => true,
    };
    return { dependencies, get, contract, finalRole, managerRole, requests, widgets, events, reloads, created, flashErrors, failSave: () => { saveFails = true; } };
}

test('le parcours local sélectionne, ajoute, édite et retire une règle sans charger les habilitations', async () => {
    const page = setup(false);
    await initializeCircuitRulesPage(page.dependencies);
    assert.deepEqual(page.created, ['rules']);
    assert.equal(page.widgets.length, 1);
    assert.equal(page.get('#validation-circuit-rule-fields').disabled, false);
    page.widgets[0].config.onChange([1]);
    page.contract.value = '6';
    page.finalRole.value = '7';
    await page.get('#validation-circuit-rule-form').listeners.submit({ preventDefault() {} });
    assert.deepEqual(JSON.parse(page.requests.at(-1).options.body), { department_ids: [1, 2], contracttype_id: 6, final_role_id: 7 });
    assert.deepEqual(page.reloads, ['rules']);

    page.events.get('#validation-circuit-rules-grid:action:edit')({ id: 12, department_id: 1, contracttype_id: 6, final_role_id: 7, department: { name: 'Direction' } });
    assert.equal(page.get('#validation-circuit-departments').inert, true);
    await page.get('#validation-circuit-rule-form').listeners.submit({ preventDefault() {} });
    assert.equal(page.requests.at(-1).url, '/api/validation-circuit-rules/12.json');
    assert.deepEqual(JSON.parse(page.requests.at(-1).options.body), { department_id: 1, contracttype_id: 6, final_role_id: 7 });
    await page.events.get('#validation-circuit-rules-grid:action:delete')({ id: 12, department: { name: 'Direction' }, contracttype: { name: 'Facture' } });
    assert.equal(page.requests.at(-1).url, '/api/validation-circuit-rules/12/delete.json');
    assert.deepEqual(page.flashErrors, []);
});

test('un refus du lot conserve la sélection et les champs pour permettre sa correction', async () => {
    const page = setup(false);
    await initializeCircuitRulesPage(page.dependencies);
    page.widgets[0].config.onChange([1]);
    page.contract.value = '6';
    page.finalRole.value = '7';
    page.failSave();
    await page.get('#validation-circuit-rule-form').listeners.submit({ preventDefault() {} });
    assert.deepEqual(page.widgets[0].current, [1, 2]);
    assert.equal(page.contract.value, '6');
    assert.deepEqual(page.reloads, []);
    assert.deepEqual(page.flashErrors, ['Règle existante.']);
    assert.equal(page.get('#validation-circuit-rule-fields').disabled, false);
});

test('le parcours super-administrateur attribue puis révoque une habilitation indépendante', async () => {
    const page = setup(true);
    await initializeCircuitRulesPage(page.dependencies);
    assert.deepEqual(page.created, ['rules', 'managers']);
    page.widgets[1].config.onChange([1]);
    page.managerRole.value = '9';
    await page.get('#validation-circuit-manager-form').listeners.submit({ preventDefault() {} });
    assert.deepEqual(JSON.parse(page.requests.at(-1).options.body), { department_ids: [1, 2], role_id: 9 });
    assert.deepEqual(page.reloads, ['managers']);
    await page.events.get('#validation-circuit-managers-grid:action:delete')({ id: 15, department: { name: 'Direction' }, role: { name: 'Gestionnaire' } });
    assert.equal(page.requests.at(-1).url, '/api/validation-circuit-rules/managers/15/revoke.json');
    assert.equal(page.requests.at(-1).options.headers['X-CSRF-Token'], 'csrf');
});

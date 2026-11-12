import assert from 'node:assert/strict';
import { test } from 'node:test';
import { TabulatorFactory } from '../../webroot/js/core/Tabulator/TabulatorFactory.js';
import { TabulatorBuilder } from '../../webroot/js/core/Tabulator/TabulatorBuilder.js?v=20261030120000';
import { isManageableUser } from '../../webroot/js/views/Users/user-readonly.js';
import { confirmAndTriggerUserImpersonation } from '../../webroot/js/views/Users/user-impersonation.js';

const previousDocument = globalThis.document;
const previousWindow = globalThis.window;
const originalBuild = TabulatorBuilder.prototype.build;
globalThis.window = { location: { pathname: '/users' } };
globalThis.document = { querySelector: () => null };
TabulatorBuilder.prototype.build = function () {
    return this.config;
};

test.after(() => {
    TabulatorBuilder.prototype.build = originalBuild;
    globalThis.window = previousWindow;
    globalThis.document = previousDocument;
});

test('marque une ligne utilisateur visible mais non gérable', () => {
    const config = TabulatorFactory.createUsersGrid('#users-table');
    const rowElement = { title: '', classList: { added: [], add(value) { this.added.push(value); } } };

    config.rowFormatter({
        getData: () => ({
            is_manageable: false,
            read_only_message: 'Utilisateur visible mais non gérable : consultation uniquement.',
        }),
        getElement: () => rowElement,
    });

    assert.equal(rowElement.title, 'Utilisateur visible mais non gérable : consultation uniquement.');
    assert.deepEqual(rowElement.classList.added, ['user-row-readonly']);
});

test('bloque le double clic d’association pour une ligne non gérable', () => {
    assert.equal(isManageableUser({ is_manageable: false }), false);
    assert.equal(isManageableUser({ is_manageable: true }), true);
});

test('confirme l’usurpation avant de déclencher le bouton de la ligne', () => {
    const messages = [];
    let clicks = 0;
    const result = confirmAndTriggerUserImpersonation(
        {
            display_name: 'Ada Lovelace',
            grid_rights: { actions: { impersonate: true } },
        },
        () => ({ click: () => { clicks += 1; } }),
        message => { messages.push(message); return true; },
    );

    assert.equal(result, true);
    assert.equal(clicks, 1);
    assert.deepEqual(messages, ['Voulez-vous vraiment vous connecter sous l’identité de Ada Lovelace ?']);
});

test('n’usurpe pas la ligne lorsque la confirmation est refusée', () => {
    let actionButtonResolved = false;
    let clicks = 0;
    const result = confirmAndTriggerUserImpersonation(
        {
            display_name: 'Ada Lovelace',
            grid_rights: { actions: { impersonate: true } },
        },
        () => { actionButtonResolved = true; return { click: () => { clicks += 1; } }; },
        () => false,
    );

    assert.equal(result, false);
    assert.equal(actionButtonResolved, false);
    assert.equal(clicks, 0);
});

test('ignore une ligne sans droit d’usurpation', () => {
    let confirmationRequested = false;
    let actionButtonResolved = false;
    const result = confirmAndTriggerUserImpersonation(
        { display_name: 'Ada Lovelace', grid_rights: { actions: { impersonate: false } } },
        () => { actionButtonResolved = true; return { click() {} }; },
        () => { confirmationRequested = true; return true; },
    );

    assert.equal(result, false);
    assert.equal(confirmationRequested, false);
    assert.equal(actionButtonResolved, false);
});

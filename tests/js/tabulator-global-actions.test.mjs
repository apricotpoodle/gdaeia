import assert from 'node:assert/strict';
import { test } from 'node:test';
import { resolveGlobalAction, TabulatorBuilder } from '../../webroot/js/core/Tabulator/TabulatorBuilder.js';

function targetFor(selector, action = null) {
    return {
        closest: requestedSelector => requestedSelector === selector ? {
            dataset: action === null ? {} : { action },
            classList: { contains: className => className === selector.slice(1) },
        } : null,
    };
}

test('reconnaît l’action créer depuis un élément interne du menu global', () => {
    const target = targetFor('.action-create', 'create');

    assert.equal(resolveGlobalAction(target), 'create');
});

test('reconnaît la réinitialisation depuis son bouton global', () => {
    const target = targetFor('.action-reset', 'reset');

    assert.equal(resolveGlobalAction(target), 'reset');
});

test('ignore une action de ligne qui n’appartient pas au menu global', () => {
    const target = targetFor('[data-action]', 'delete');

    assert.equal(resolveGlobalAction(target), null);
});

test('la pagination distante désactive le chargement progressif', () => {
    const builder = new TabulatorBuilder('#comments-grid');
    builder.config.progressiveLoad = 'scroll';
    builder.config.progressiveLoadScrollMargin = 150;

    builder.setRemotePagination();

    assert.equal(builder.config.pagination, true);
    assert.equal(builder.config.paginationMode, 'remote');
    assert.equal(builder.config.progressiveLoad, undefined);
    assert.equal(builder.config.progressiveLoadScrollMargin, undefined);
});

test('délègue les actions globales au conteneur de grille en phase de capture', () => {
    const listeners = [];
    const hostElement = {
        addEventListener: (...args) => listeners.push(args),
        contains: () => true,
    };
    const previousDocument = globalThis.document;
    globalThis.document = {
        addEventListener: () => {},
    };

    try {
        const builder = new TabulatorBuilder('#comments-grid').setWithActions(['edit']);
        builder._installGlobalActionDelegation({ element: hostElement }, hostElement);

        assert.equal(listeners.length, 1);
        assert.equal(listeners[0][0], 'click');
        assert.equal(listeners[0][2], true);
    } finally {
        globalThis.document = previousDocument;
    }
});

import assert from 'node:assert/strict';
import { test } from 'node:test';
import { resolveGlobalAction, TabulatorBuilder } from '../../webroot/js/core/Tabulator/TabulatorBuilder.js';
import { globalTabulatorObserver } from '../../webroot/js/core/Tabulator/TabulatorObserver.js';

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

test('configure le message de chargement Tabulator par défaut en français', () => {
    const builder = new TabulatorBuilder('#comments-grid');

    assert.equal(builder.config.dataLoaderLoading, 'Chargement en cours...');
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

test('vide les lignes précédentes avant de recharger un filtre distant progressif', () => {
    const previousDocument = globalThis.document;
    globalThis.document = { querySelector: () => null };
    const builder = new TabulatorBuilder('#applicationforms-grid').setContinuousScroll();
    let cleared = false;

    builder.config.dataFiltering.call({
        clearData: () => {
            cleared = true;
        },
    }, []);

    assert.equal(cleared, true);
    assert.equal(typeof builder.config.headerFilterChanged, 'function');
    builder.config.headerFilterChanged.call({
        clearData: () => {
            cleared = true;
        },
    }, []);
    assert.equal(cleared, true);
    globalThis.document = previousDocument;
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

test('publie les données de ligne sur le canal de double clic Tabulator', () => {
    const selector = '#row-double-click-grid';
    const originalDocument = globalThis.document;
    const OriginalTabulator = globalThis.Tabulator;
    const handlers = new Map();
    const user = { id: 17, display_name: 'Ada Lovelace' };

    globalThis.document = { querySelector: () => null };
    globalThis.Tabulator = class {
        constructor() {
            this.element = {};
        }

        on(eventName, callback) {
            handlers.set(eventName, callback);
        }
    };

    try {
        const published = [];
        globalTabulatorObserver.subscribe(`${selector}:rowDblClick`, data => published.push(data));

        const table = new TabulatorBuilder(selector)
            .setAjaxSource('/api/users.json')
            .build();
        const rowDblClick = handlers.get('rowDblClick');
        assert.equal(typeof rowDblClick, 'function');
        rowDblClick({}, { getData: () => user });

        assert.equal(table instanceof globalThis.Tabulator, true);
        assert.deepEqual(published, [user]);
    } finally {
        globalThis.document = originalDocument;
        globalThis.Tabulator = OriginalTabulator;
    }
});

import assert from 'node:assert/strict';
import { test } from 'node:test';
import {
    DepartmentSelection,
    ValidationCircuitRulesClient,
    renderCircuitPreview,
    fillSelectOptions,
    createDepartmentSelector,
} from '../../webroot/js/views/ValidationCircuitRules/circuit-rules.js';
import { TabulatorFactory } from '../../webroot/js/core/Tabulator/TabulatorFactory.js';
import { TabulatorBuilder } from '../../webroot/js/core/Tabulator/TabulatorBuilder.js?v=20261030120000';

const tree = [{ id: 1, name: 'Rédaction', children: [
    { id: 2, name: 'Service A', children: [{ id: 3, name: 'Équipe' }] },
    { id: 4, name: 'Service B' },
] }, { id: 8, name: 'Autre société' }];

test('sélectionne seulement le parent et les descendants présents dans le périmètre fourni', () => {
    const selection = new DepartmentSelection(tree);
    assert.deepEqual(selection.change([1, 99]), [1, 2, 3, 4]);
    assert.deepEqual(selection.change([1, 4]), [1, 4]);
    assert.deepEqual(selection.change([4]), []);
});

test('ne rajoute pas un enfant exclu quand une autre branche est sélectionnée', () => {
    const selection = new DepartmentSelection(tree);
    selection.change([1]);
    selection.change([1, 4]);
    assert.deepEqual(selection.change([1, 4, 8]), [1, 4, 8]);
});

test('une édition unitaire sélectionne exactement son département sans ses descendants', () => {
    const selection = new DepartmentSelection(tree);
    assert.deepEqual(selection.replace([1, 99]), [1]);
    assert.deepEqual(selection.change([]), []);
});

test('le composant arborescent répercute les exclusions et verrouille le département édité', () => {
    let config;
    let widget;
    class Wrapper {
        constructor(options) { config = options; widget = this; this.nativeInstance = {}; }
        set value(value) { this.current = value; }
        setDisabled() { throw new Error('Un remontage détruirait les écouteurs du composant.'); }
    }
    const changes = [];
    const container = {};
    const selector = createDepartmentSelector(container, tree, (values) => changes.push(values), Wrapper);
    config.onChange([1]);
    assert.deepEqual(selector.values, [1, 2, 3, 4]);
    assert.deepEqual(widget.current, [1, 2, 3, 4]);
    config.onChange([1, 4]);
    assert.deepEqual(selector.values, [1, 4]);
    selector.replace([1]);
    selector.setLocked(true);
    config.onChange([1, 8]);
    assert.deepEqual(selector.values, [1]);
    assert.equal(container.inert, true);
    selector.setLocked(false);
    assert.equal(container.inert, false);
    config.onChange([8]);
    assert.deepEqual(selector.values, [8]);
    assert.deepEqual(changes, [[1, 2, 3, 4], [1, 4], [8]]);
});

function clientWithPayload(payload = { success: true, message: 'Opération réussie.', errors: [], data: [] }, ok = true) {
    const requests = [];
    const client = new ValidationCircuitRulesClient({
        csrfTokenProvider: () => 'jeton-csrf',
        fetchImpl: async (url, options) => {
            requests.push({ url, options });
            return { ok, status: ok ? 200 : 422, json: async () => payload };
        },
    });
    return { client, requests };
}

test('ajoute un lot explicite et transmet le jeton CSRF sans saisir les identifiants', async () => {
    const { client, requests } = clientWithPayload();
    await client.saveRule({ department_ids: [1, 4], contracttype_id: 6, final_role_id: 7 });
    assert.equal(requests[0].url, '/api/validation-circuit-rules/add.json');
    assert.equal(requests[0].options.method, 'POST');
    assert.equal(requests[0].options.headers['X-CSRF-Token'], 'jeton-csrf');
    assert.deepEqual(JSON.parse(requests[0].options.body), { department_ids: [1, 4], contracttype_id: 6, final_role_id: 7 });
});

test('édite uniquement la règle et le département courants', async () => {
    const { client, requests } = clientWithPayload();
    await client.saveRule({ department_ids: [4], contracttype_id: 6, final_role_id: 7 }, 12);
    assert.equal(requests[0].url, '/api/validation-circuit-rules/12.json');
    assert.deepEqual(JSON.parse(requests[0].options.body), { department_id: 4, contracttype_id: 6, final_role_id: 7 });
    await assert.rejects(client.saveRule({ department_ids: [1, 4], contracttype_id: 6, final_role_id: 7 }, 12));
    assert.equal(requests.length, 1);
});

test('refuse les formulaires incomplets avant toute requête', async () => {
    const { client, requests } = clientWithPayload();
    await assert.rejects(client.saveRule({ department_ids: [], contracttype_id: 6, final_role_id: 7 }));
    await assert.rejects(client.preview({ department_ids: [1], contracttype_id: '', final_role_id: 7 }));
    await assert.rejects(client.assignManagers([], 7));
    assert.equal(requests.length, 0);
});

test('retire une règle et révoque une habilitation par des mutations unitaires protégées', async () => {
    const { client, requests } = clientWithPayload();
    await client.deleteRule(12);
    await client.revokeManager(15);
    assert.deepEqual(requests.map(({ url }) => url), [
        '/api/validation-circuit-rules/12/delete.json',
        '/api/validation-circuit-rules/managers/15/revoke.json',
    ]);
    assert.ok(requests.every(({ options }) => options.method === 'POST' && options.headers['X-CSRF-Token'] === 'jeton-csrf'));
});

test('attribue une habilitation avec un rôle distinct du rôle final', async () => {
    const { client, requests } = clientWithPayload();
    await client.assignManagers([1, 4], 9);
    assert.equal(requests[0].url, '/api/validation-circuit-rules/managers/assign.json');
    assert.deepEqual(JSON.parse(requests[0].options.body), { department_ids: [1, 4], role_id: 9 });
});

test('prévisualise le parcours explicite et conserve les problèmes par département', async () => {
    const data = [{ department_id: 4, department_name: 'Service B', sequences: [], issues: ['Validateur absent.'] }];
    const { client, requests } = clientWithPayload({ success: true, message: '', errors: [], data });
    assert.deepEqual(await client.preview({ department_ids: [4], contracttype_id: 6, final_role_id: 7 }), data);
    assert.equal(requests[0].url, '/api/validation-circuit-rules/preview.json');
    assert.equal(requests[0].options.headers['X-CSRF-Token'], 'jeton-csrf');
});

test('remonte une erreur métier et ne transforme pas un refus 422 en succès', async () => {
    const { client } = clientWithPayload({ success: false, message: 'Le lot contient une règle existante.', errors: [], data: [] }, false);
    await assert.rejects(client.saveRule({ department_ids: [1], contracttype_id: 6, final_role_id: 7 }), /règle existante/);
});

test('charge les options et l’arbre depuis les API du domaine', async () => {
    const { client, requests } = clientWithPayload({ data: tree });
    assert.deepEqual(await client.departments(), tree);
    await client.options();
    assert.deepEqual(requests.map(({ url }) => url), [
        '/api/validation-circuit-rules/departments-tree.json',
        '/api/validation-circuit-rules/options.json',
    ]);
});

function element(tagName = 'div') {
    return {
        tagName, textContent: '', children: [], className: '', value: '',
        append(...children) { this.children.push(...children); },
        replaceChildren(...children) { this.children = children; },
        ownerDocument: { createElement: (tag) => element(tag) },
        set innerHTML(_) { throw new Error('Le contenu métier ne doit pas être injecté en HTML.'); },
    };
}

test('affiche les rôles, séquences et problèmes comme texte, y compris les libellés HTML', () => {
    const container = element();
    renderCircuitPreview(container, [{
        department_id: 4, department_name: '<img src=x onerror=alert(1)>',
        sequences: [{ role_id: 7, role_name: '<b>SGR</b>', sequence: 2 }],
        issues: ['<script>alerte</script>'],
    }]);
    const text = (node) => [node.textContent, ...node.children.map(text)].join(' ');
    assert.match(text(container), /<img src=x onerror=alert\(1\)>/);
    assert.match(text(container), /2.*<b>SGR<\/b>/);
    assert.match(text(container), /<script>alerte<\/script>/);
});

test('les listes de choix exposent les libellés échappés et les valeurs des options API', () => {
    const select = element('select');
    fillSelectOptions(select, [{ id: 7, name: '<b>SGR</b>' }], 'Choisir un rôle');
    assert.equal(select.children[0].value, '');
    assert.equal(select.children[1].value, '7');
    assert.equal(select.children[1].textContent, '<b>SGR</b>');
});

test('configure les deux grilles avec pagination, tri et filtres distants et texte sûr', () => {
    const previousDocument = globalThis.document;
    const previousWindow = globalThis.window;
    const originalBuild = TabulatorBuilder.prototype.build;
    globalThis.document = { querySelector: () => null };
    globalThis.window = { location: { pathname: '/validation-circuit-rules' } };
    TabulatorBuilder.prototype.build = function () { return this.config; };
    try {
        const rules = TabulatorFactory.createValidationCircuitRulesGrid();
        const managers = TabulatorFactory.createValidationCircuitManagersGrid();
        for (const config of [rules, managers]) {
            assert.equal(config.pagination, true);
            assert.equal(config.paginationMode, 'remote');
            assert.equal(config.sortMode, 'remote');
            assert.equal(config.filterMode, 'remote');
            assert.equal(config.progressiveLoad, undefined);
            assert.ok(config.columns.filter((column) => column.field !== 'configuration_issues')
                .every((column) => column.formatter === 'plaintext'));
        }
        assert.equal(rules.ajaxURL, '/api/validation-circuit-rules.json');
        assert.deepEqual(rules.columns.map(({ field }) => field), ['department.name', 'contracttype.name', 'final_role.name', 'configuration_issues']);
        const diagnostics = rules.columns.find(({ field }) => field === 'configuration_issues');
        assert.equal(diagnostics.headerSort, false);
        assert.equal(diagnostics.headerFilter, false);
        const displayed = diagnostics.formatter({
            getValue: () => ['Rôle final inactif.', '<img src=x onerror=alert(1)>'],
            getElement: () => element(),
        });
        assert.equal(displayed.textContent, 'Rôle final inactif. <img src=x onerror=alert(1)>');
        assert.equal(diagnostics.formatter({ getValue: () => [], getElement: () => element() }).textContent, 'Circuit utilisable.');
        assert.equal(managers.ajaxURL, '/api/validation-circuit-rules/managers.json');
        assert.deepEqual(managers.columns.map(({ field }) => field), ['department.name', 'role.name']);
    } finally {
        globalThis.document = previousDocument;
        globalThis.window = previousWindow;
        TabulatorBuilder.prototype.build = originalBuild;
    }
});

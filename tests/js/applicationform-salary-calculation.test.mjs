import assert from 'node:assert/strict';
import { readFile } from 'node:fs/promises';
import test from 'node:test';

let pageId = 0;

function makeElement({ value = '', classNames = [] } = {}) {
    const listeners = new Map();
    const classes = new Set(classNames);

    return {
        value,
        textContent: '',
        classList: {
            add: (...names) => names.forEach(name => classes.add(name)),
            remove: (...names) => names.forEach(name => classes.delete(name)),
            toggle(name, force) {
                if (force === undefined ? !classes.has(name) : force) classes.add(name);
                else classes.delete(name);
            },
            contains: name => classes.has(name),
        },
        addEventListener(type, listener) {
            const callbacks = listeners.get(type) || [];
            callbacks.push(listener);
            listeners.set(type, callbacks);
        },
        async dispatch(type) {
            const callbacks = listeners.get(type) || [];
            await Promise.all(callbacks.map(callback => callback({ type })));
        },
        async dispatchEvent(event) {
            await this.dispatch(event.type);
            return true;
        },
        removeAttribute(name) {
            delete this[name];
        },
    };
}

function deferred() {
    let resolve;
    const promise = new Promise(resolvePromise => {
        resolve = resolvePromise;
    });

    return { promise, resolve };
}

async function initializeCalculator({ fetcher, periodValue = '7', amountValue = '100', daysValue = '' }) {
    const form = {};
    const period = makeElement({ value: periodValue });
    const amount = makeElement({ value: amountValue });
    const workingDays = makeElement({ value: daysValue });
    const salaryBase = makeElement();
    const salaryMessage = makeElement({ classNames: ['d-none'] });
    const elements = {
        'applicationform-main-form': form,
        'period-id': period,
        grossremuneration: amount,
        'working-days': workingDays,
        'salary-base': salaryBase,
        'salary-base-message': salaryMessage,
    };
    const previousDocument = globalThis.document;
    const previousFetch = globalThis.fetch;
    const previousEvent = globalThis.Event;
    globalThis.Event = Event;
    globalThis.document = {
        getElementById: id => elements[id] ?? null,
        querySelector: () => ({ getAttribute: () => 'csrf-token' }),
    };
    globalThis.fetch = fetcher;

    await importScript('../../webroot/js/views/Applicationforms/salary-calculation.js');

    return {
        form,
        period,
        amount,
        workingDays,
        salaryBase,
        salaryMessage,
        restore() {
            if (previousDocument === undefined) delete globalThis.document;
            else globalThis.document = previousDocument;
            if (previousFetch === undefined) delete globalThis.fetch;
            else globalThis.fetch = previousFetch;
            if (previousEvent === undefined) delete globalThis.Event;
            else globalThis.Event = previousEvent;
        },
    };
}

async function importScript(relativePath) {
    const source = await readFile(new URL(relativePath, import.meta.url), 'utf8');
    const scriptUrl = `data:text/javascript;base64,${Buffer.from(source).toString('base64')}#page-${++pageId}`;
    await import(scriptUrl);
}

function successfulResponse(salaryBase) {
    return {
        ok: true,
        json: async () => ({ success: true, message: 'Salaire de base calculé.', salary_base: salaryBase }),
    };
}

test('calcule les unités mensuelles sans nombre de jours', async t => {
    for (const scenario of [
        { periodValue: 'k-month', amountValue: '10', salaryBase: '174.6000' },
        { periodValue: 'eur-month', amountValue: '2000', salaryBase: '2000.0000' },
    ]) {
        const requests = [];
        const calculator = await initializeCalculator({
            periodValue: scenario.periodValue,
            amountValue: scenario.amountValue,
            fetcher: async (_url, options) => {
                requests.push(JSON.parse(options.body));
                return successfulResponse(scenario.salaryBase);
            },
        });
        t.after(calculator.restore);

        await calculator.period.dispatch('change');

        assert.equal(requests.length, 1);
        assert.equal(requests[0].working_days, null);
        assert.equal(calculator.salaryBase.value, scenario.salaryBase);
    }
});

test('recalcule le salaire journalier après le calcul automatique des jours ouvrés', async t => {
    const requests = [];
    const calculator = await initializeCalculator({
        periodValue: 'eur-day',
        fetcher: async (_url, options) => {
            const body = JSON.parse(options.body);
            requests.push(body);
            return successfulResponse(`${Number(body.working_days) * Number(body.grossremuneration)}.0000`);
        },
    });
    t.after(calculator.restore);

    const beginAt = makeElement();
    const endAt = makeElement();
    const workingDaysContainer = makeElement({ classNames: ['d-none'] });
    let onReady;
    const elements = {
        'contracttype-id': null,
        'begin-at': beginAt,
        'begin-at-warning-text': null,
        'end-at': endAt,
        'end-at-required-asterisk': null,
        'cdi-warning-text': null,
        'container-working-days': workingDaysContainer,
        'working-days': calculator.workingDays,
    };
    globalThis.document.getElementById = id => elements[id] ?? null;
    globalThis.document.addEventListener = (_event, callback) => { onReady = callback; };

    await importScript('../../webroot/js/views/Applicationforms/zone-contract.js');
    onReady();
    beginAt.value = '2026-11-02';
    endAt.value = '2026-11-04';
    endAt.dispatch('input');
    await new Promise(setImmediate);

    assert.equal(calculator.workingDays.value, '3');
    assert.equal(requests.length, 1);
    assert.equal(requests[0].working_days, '3');
    assert.equal(calculator.salaryBase.value, '300.0000');
});

test('recalcule le salaire journalier après un ajustement manuel des jours', async t => {
    const requests = [];
    const calculator = await initializeCalculator({
        periodValue: 'eur-day',
        daysValue: '3',
        fetcher: async (_url, options) => {
            const body = JSON.parse(options.body);
            requests.push(body);
            return successfulResponse(`${Number(body.working_days) * Number(body.grossremuneration)}.0000`);
        },
    });
    t.after(calculator.restore);

    await calculator.workingDays.dispatch('input');
    calculator.workingDays.value = '2';
    await calculator.workingDays.dispatch('input');

    assert.deepEqual(requests.map(request => request.working_days), ['3', '2']);
    assert.equal(calculator.salaryBase.value, '200.0000');
});

test('affiche le message de validation de l’API lorsque les jours sont requis', async t => {
    const calculator = await initializeCalculator({
        periodValue: 'eur-day',
        daysValue: '',
        fetcher: async () => ({
            ok: false,
            json: async () => ({
                success: false,
                message: 'Le nombre de jours travaillés est obligatoire.',
                errors: [],
            }),
        }),
    });
    t.after(calculator.restore);

    await calculator.amount.dispatch('input');

    assert.equal(calculator.salaryBase.value, '');
    assert.equal(calculator.salaryMessage.textContent, 'Le nombre de jours travaillés est obligatoire.');
    assert.equal(calculator.salaryMessage.classList.contains('d-none'), false);
});

test('efface résultat et message quand le montant ou la périodicité est vidé', async t => {
    for (const clearedField of ['amount', 'period']) {
        const calculator = await initializeCalculator({
            fetcher: async () => successfulResponse('300.0000'),
        });
        t.after(calculator.restore);
        await calculator.amount.dispatch('input');
        calculator.salaryMessage.textContent = 'Erreur précédente';
        calculator.salaryMessage.classList.remove('d-none');

        calculator[clearedField].value = '';
        await calculator[clearedField].dispatch('input');

        assert.equal(calculator.salaryBase.value, '');
        assert.equal(calculator.salaryMessage.textContent, '');
        assert.equal(calculator.salaryMessage.classList.contains('d-none'), true);
    }
});

test('ignore la réponse périmée après avoir vidé le montant', async t => {
    const pendingResponse = deferred();
    const calculator = await initializeCalculator({
        fetcher: async () => pendingResponse.promise,
    });
    t.after(calculator.restore);

    const staleCalculation = calculator.amount.dispatch('input');
    calculator.amount.value = '';
    await calculator.amount.dispatch('input');
    pendingResponse.resolve(successfulResponse('300.0000'));
    await staleCalculation;

    assert.equal(calculator.salaryBase.value, '');
});

test('ignore une ancienne réponse si un calcul plus récent a déjà abouti', async t => {
    const oldResponse = deferred();
    const requests = [];
    const calculator = await initializeCalculator({
        periodValue: 'eur-day',
        daysValue: '3',
        fetcher: async (_url, options) => {
            const body = JSON.parse(options.body);
            requests.push(body);
            return requests.length === 1 ? oldResponse.promise : successfulResponse('200.0000');
        },
    });
    t.after(calculator.restore);

    const oldCalculation = calculator.workingDays.dispatch('input');
    calculator.workingDays.value = '2';
    await calculator.workingDays.dispatch('input');
    oldResponse.resolve(successfulResponse('300.0000'));
    await oldCalculation;

    assert.equal(calculator.salaryBase.value, '200.0000');
});

import assert from 'node:assert/strict';
import test from 'node:test';

function makeInput({ value = '', type = 'text', disabled = false } = {}) {
    const listeners = {};

    return {
        value,
        type,
        disabled,
        readOnly: false,
        classList: { add() {}, remove() {} },
        addEventListener(event, listener) {
            listeners[event] = listener;
        },
        dispatch(event) {
            listeners[event]?.();
        },
    };
}

async function initializeForm({
    applicantName = makeInput({ type: 'hidden' }),
    firstName = makeInput(),
    lastName = makeInput(),
} = {}) {
    let onReady;
    const wrapper = { style: { display: '' } };
    const options = [
        { value: '', text: '-- Sélectionner --' },
        { value: '12', text: 'Ada Lovelace' },
    ];
    const select = {
        value: '',
        options,
        get selectedIndex() {
            return options.findIndex((option) => option.value === this.value);
        },
        closest: () => wrapper,
        addEventListener(event, listener) {
            this.changeListener = listener;
        },
        dispatchChange(value) {
            this.value = value;
            this.changeListener?.();
        },
    };
    const previousDocument = globalThis.document;
    globalThis.document = {
        addEventListener: (_event, listener) => { onReady = listener; },
        getElementById: (id) => ({
            'collaborator-id': select,
            applicantname: applicantName,
            'applicant-first-name': firstName,
            'applicant-last-name': lastName,
        })[id] ?? null,
    };

    const scriptUrl = new URL('../../webroot/js/views/Applicationforms/applicationform-candidate.js', import.meta.url);
    await import(`${scriptUrl.href}?test=${Math.random()}`);
    onReady();

    return {
        applicantName,
        firstName,
        lastName,
        select,
        wrapper,
        restore() {
            if (previousDocument === undefined) delete globalThis.document;
            else globalThis.document = previousDocument;
        },
    };
}

test('un collaborateur sélectionné remplace puis réactive les champs externes', async (t) => {
    const form = await initializeForm({
        firstName: makeInput({ value: 'Ancien' }),
        lastName: makeInput({ value: 'Candidat' }),
    });
    t.after(form.restore);

    form.select.dispatchChange('12');

    assert.equal(form.applicantName.value, 'Ada Lovelace');
    assert.equal(form.firstName.value, '');
    assert.equal(form.lastName.value, '');
    assert.equal(form.firstName.disabled, true);
    assert.equal(form.lastName.disabled, true);

    form.select.dispatchChange('');

    assert.equal(form.applicantName.value, '');
    assert.equal(form.firstName.disabled, false);
    assert.equal(form.lastName.disabled, false);
});

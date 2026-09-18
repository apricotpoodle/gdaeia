import { FlashManager } from '../../core/FlashManager.js';
import { TabulatorFactory } from '../../core/Tabulator/TabulatorFactory.js';
import { globalTabulatorObserver } from '../../core/Tabulator/TabulatorObserver.js';

const tableSelector = '#validation-comment-templates-grid';
const table = TabulatorFactory.createWorkflowCommentTemplatesGrid(tableSelector);
const csrfToken = document.querySelector('meta[name="csrfToken"]')?.getAttribute('content') || '';
const form = document.querySelector('#validation-comment-template-form');

async function readJson(response, fallbackMessage) {
    const payload = await response.json();
    if (!response.ok || payload.success === false) {
        throw new Error(payload.message || fallbackMessage);
    }
    return payload;
}

function resetForm() {
    form.reset();
    form.querySelector('[name="id"]').value = '';
    form.querySelector('[name="active"]').checked = true;
    form.classList.add('d-none');
}

function editTemplate(template) {
    form.querySelector('[name="id"]').value = template.id;
    form.querySelector('[name="decision"]').value = template.decision;
    form.querySelector('[name="label"]').value = template.label;
    form.querySelector('[name="content"]').value = template.content;
    form.querySelector('[name="position"]').value = template.position;
    form.querySelector('[name="active"]').checked = Boolean(template.active);
    form.classList.remove('d-none');
}

async function saveDefaultDueHours() {
    const input = document.querySelector('#validation-default-due-hours');
    const defaultDueHours = Number(input.value);
    if (!Number.isInteger(defaultDueHours) || defaultDueHours < 1) {
        FlashManager.warning('Le délai global doit être un entier positif.');
        return;
    }
    await readJson(await fetch('/api/workflow-settings/default-due-hours.json', {
        method: 'POST',
        headers: { Accept: 'application/json', 'Content-Type': 'application/json', 'X-CSRF-Token': csrfToken },
        body: JSON.stringify({ default_due_hours: defaultDueHours }),
    }), 'Impossible d’enregistrer le délai global.');
    FlashManager.success('Délai global enregistré.');
}

async function loadDefaultDueHours() {
    const payload = await readJson(await fetch('/api/workflow-settings/default-due-hours.json', {
        headers: { Accept: 'application/json' },
    }), 'Impossible de charger le délai global.');
    document.querySelector('#validation-default-due-hours').value = payload.data.default_due_hours;
}

globalTabulatorObserver.subscribe(`${tableSelector}:action:create`, () => {
    resetForm();
    form.classList.remove('d-none');
});
globalTabulatorObserver.subscribe(`${tableSelector}:action:edit`, editTemplate);
globalTabulatorObserver.subscribe(`${tableSelector}:action:delete`, async (template) => {
    if (!confirm(`Supprimer le commentaire « ${template.label} » ?`)) return;
    try {
        await readJson(await fetch(`/api/workflow-settings/comment-templates/${template.id}/delete.json`, {
            method: 'POST',
            headers: { Accept: 'application/json', 'X-CSRF-Token': csrfToken },
        }), 'Impossible de supprimer le commentaire prédéfini.');
        await table.replaceData();
        FlashManager.success('Commentaire prédéfini supprimé.');
    } catch (error) {
        FlashManager.error(error.message);
    }
});

form.addEventListener('submit', async (event) => {
    event.preventDefault();
    const data = Object.fromEntries(new FormData(form));
    data.active = form.querySelector('[name="active"]').checked;
    const id = data.id;
    const url = id === ''
        ? '/api/workflow-settings/comment-templates/create.json'
        : `/api/workflow-settings/comment-templates/${id}.json`;
    try {
        await readJson(await fetch(url, {
            method: 'POST',
            headers: { Accept: 'application/json', 'Content-Type': 'application/json', 'X-CSRF-Token': csrfToken },
            body: JSON.stringify(data),
        }), 'Impossible d’enregistrer le commentaire prédéfini.');
        resetForm();
        await table.replaceData();
        FlashManager.success('Commentaire prédéfini enregistré.');
    } catch (error) {
        FlashManager.error(error.message);
    }
});

document.querySelector('#save-validation-comment-template')?.addEventListener('click', () => form.requestSubmit());
document.querySelector('#save-validation-default-due-hours')?.addEventListener('click', () => {
    saveDefaultDueHours().catch((error) => FlashManager.error(error.message));
});
document.querySelector('#cancel-validation-comment-template')?.addEventListener('click', resetForm);
loadDefaultDueHours().catch((error) => FlashManager.error(error.message));

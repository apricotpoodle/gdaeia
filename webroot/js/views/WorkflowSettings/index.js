import { FlashManager } from '../../core/FlashManager.js';
import { TabulatorFactory } from '../../core/Tabulator/TabulatorFactory.js';
import { TabulatorCellEditHandler } from '../../core/Tabulator/TabulatorCellEditHandler.js';
import { TabulatorMutationClient } from '../../core/Tabulator/TabulatorMutationClient.js';
import { globalTabulatorObserver } from '../../core/Tabulator/TabulatorObserver.js';
import {
    createSettingColumns,
    createWorkflowSettingRequest,
} from './settings-grid.js';

const tableSelector = '#validation-comment-templates-grid';
const validationSettingsSelector = '#workflow-validation-settings-grid';
const daeSettingsSelector = '#workflow-applicationforms-settings-grid';
const table = TabulatorFactory.createWorkflowCommentTemplatesGrid(tableSelector);
const csrfToken = document.querySelector('meta[name="csrfToken"]')?.getAttribute('content') || '';
const form = document.querySelector('#validation-comment-template-form');
const tablesByPanel = { '#workflow-settings-comments-panel': table };

async function readJson(response, fallbackMessage) {
    const payload = await response.json().catch(() => null);
    if (!response.ok || payload?.success === false) {
        throw new Error(payload?.message || fallbackMessage);
    }

    return payload || {};
}

function resetForm() {
    if (!form) {
        throw new Error('Formulaire des commentaires prédéfinis introuvable.');
    }
    form.reset();
    form.querySelector('[name="id"]').value = '';
    form.querySelector('[name="active"]').checked = true;
    form.classList.add('d-none');
}

function showCreateForm() {
    resetForm();
    form.classList.remove('d-none');
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

async function loadJson(url, fallbackMessage) {
    return readJson(await fetch(url, {
        headers: { Accept: 'application/json' },
    }), fallbackMessage);
}

function createSettingsGrid(selector, rows) {
    const responseFieldByKey = {
        'validation.default_due_hours': 'default_due_hours',
        'validation.blocked_after_business_days': 'blocked_after_business_days',
        'salary.k_coefficient': 'k_coefficient',
    };
    const handler = new TabulatorCellEditHandler({
        editableFields: ['value'],
        requestFactory: (cell) => createWorkflowSettingRequest(
            cell.getRow().getData(),
            cell.getValue(),
        ),
        responseMapper: (payload, cell) => {
            const setting = cell.getRow().getData();
            if (setting.key === 'validation.comment_required.accept') {
                return { value: payload.comment_requirements?.accepter };
            }
            if (setting.key === 'validation.comment_required.reject') {
                return { value: payload.comment_requirements?.refuser };
            }
            const responseField = responseFieldByKey[setting.key];

            return responseField ? { value: payload[responseField] } : {};
        },
        client: new TabulatorMutationClient(),
        onSuccess: (_payload, cell) => {
            FlashManager.success(`Paramètre « ${cell.getRow().getData().label} » enregistré.`);
        },
        onError: (error) => FlashManager.error(error.message),
    });

    return TabulatorFactory.createBaseGrid(selector)
        .setLocalData(rows)
        .setColumnDefaults({ headerSort: false, tooltip: true })
        .setColumns(createSettingColumns())
        .setCellEditHandler(handler)
        .addOptions({
            persistence: false,
            height: '100%',
            layout: 'fitColumns',
            responsiveLayout: 'collapse',
            placeholder: "<div class='p-3 text-muted'>Aucun paramètre à afficher.</div>",
        })
        .build();
}

async function loadSettingsGrids() {
    const [duePayload, blockedPayload, requirementsPayload, coefficientPayload] = await Promise.all([
        loadJson('/api/workflow-settings/default-due-hours.json', 'Impossible de charger le délai global.'),
        loadJson('/api/workflow-settings/blocked-after-business-days.json', 'Impossible de charger le délai de détection.'),
        loadJson('/api/workflow-settings/comment-requirements.json', 'Impossible de charger les obligations de commentaire.'),
        loadJson('/api/workflow-settings/k-coefficient.json', 'Impossible de charger le coefficient k.'),
    ]);
    const validationGridElement = document.querySelector(validationSettingsSelector);
    const canManageCoefficient = document.querySelector(daeSettingsSelector)?.dataset.canManage === 'true';
    const validationRows = [
        {
            key: 'validation.default_due_hours',
            label: 'Délai global de validation',
            help: 'Heures par défaut avant l’échéance.',
            value: duePayload.data.default_due_hours,
            type: 'number',
            min: 1,
            step: 1,
            canEdit: validationGridElement?.dataset.canEditDefaultDueHours === 'true',
        },
        {
            key: 'validation.blocked_after_business_days',
            label: 'Détection des cycles bloqués',
            help: 'Jours ouvrés avant la détection du blocage.',
            value: blockedPayload.data.blocked_after_business_days,
            type: 'number',
            min: 1,
            step: 1,
            canEdit: validationGridElement?.dataset.canEditBlockedAfterBusinessDays === 'true',
        },
        {
            key: 'validation.comment_required.accept',
            label: 'Commentaire obligatoire pour une acceptation',
            help: 'Lors d’un vote favorable.',
            value: Boolean(requirementsPayload.comment_requirements.accepter),
            type: 'boolean',
            canEdit: validationGridElement?.dataset.canEditCommentAccept === 'true',
        },
        {
            key: 'validation.comment_required.reject',
            label: 'Commentaire obligatoire pour un refus',
            help: 'Lors d’un vote défavorable.',
            value: Boolean(requirementsPayload.comment_requirements.refuser),
            type: 'boolean',
            canEdit: validationGridElement?.dataset.canEditCommentReject === 'true',
        },
    ];
    const daeRows = [{
        key: 'salary.k_coefficient',
        label: 'Coefficient de conversion k/mois',
        help: 'Valeur positive pour convertir les milliers mensuels.',
        value: coefficientPayload.k_coefficient,
        type: 'number',
        min: 0.0001,
        step: 0.0001,
        canEdit: canManageCoefficient,
    }];

    return {
        validation: createSettingsGrid(validationSettingsSelector, validationRows),
        dae: createSettingsGrid(daeSettingsSelector, daeRows),
    };
}

function attachTabRedraw() {
    document.querySelectorAll('#workflow-settings-tabs [data-bs-toggle="tab"]').forEach((tab) => {
        tab.addEventListener('shown.bs.tab', () => {
            tablesByPanel[tab.dataset.bsTarget]?.redraw(true);
        });
    });
}

globalTabulatorObserver.subscribe(`${tableSelector}:action:create`, () => {
    try {
        showCreateForm();
    } catch (error) {
        FlashManager.error(error.message);
    }
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
document.querySelector('#cancel-validation-comment-template')?.addEventListener('click', resetForm);

loadSettingsGrids()
    .then((settingsTables) => {
        tablesByPanel['#workflow-settings-validation-panel'] = settingsTables.validation;
        tablesByPanel['#workflow-settings-applicationforms-panel'] = settingsTables.dae;
    })
    .catch((error) => FlashManager.error(error.message));
attachTabRedraw();

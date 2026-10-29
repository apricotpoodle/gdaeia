/**
 * @file applicationform-columns.js
 * @description Définition des colonnes pour Applicationforms via ColumnsFactory.
 */

import { ColumnsFactory } from '../../core/Tabulator/ColumnsFactory.js';

const PERIOD_LABELS = {
    K_PER_MONTH: 'k/mois',
    EUR_PER_MONTH: '€/mois',
    EUR_PER_DAY: '€/jour',
    EUR_PER_HOUR: '€/heure',
};

function formatAmount(cell) {
    const value = cell.getValue();
    if (value === null || value === undefined || value === '') return '—';

    return new Intl.NumberFormat('fr-FR', {
        minimumFractionDigits: 2,
        maximumFractionDigits: 4,
    }).format(Number(value));
}

function formatSalaryBase(cell) {
    let value = cell.getValue();
    if (value === null || value === undefined || value === '') {
        const data = cell.getRow().getData();
        const period = data.period || data.Period || {};
        const periodCode = String(period.code || data.period_code || '').toUpperCase();
        const periodName = String(period.name || '').toLowerCase();
        const isDaily = periodCode === 'EUR_PER_DAY'
            || periodCode.includes('DAY')
            || periodName.includes('jour');
        const amount = Number(String(data.grossremuneration ?? '').replace(',', '.'));
        const workingDays = Number(data.working_days);
        if (isDaily && Number.isFinite(amount) && Number.isFinite(workingDays)) {
            value = amount * workingDays;
        }
    }
    if (value === null || value === undefined || value === '') return '—';

    return new Intl.NumberFormat('fr-FR', {
        style: 'currency',
        currency: 'EUR',
    }).format(Number(value));
}

function formatPeriod(cell) {
    const value = cell.getValue();
    return PERIOD_LABELS[value] || value || '—';
}

function dateTimestamp(value) {
    if (!value) return null;

    const text = String(value);
    const normalized = /^\d{4}-\d{2}-\d{2}$/.test(text)
        ? `${text}T00:00:00Z`
        : text;
    const timestamp = Date.parse(normalized);

    return Number.isFinite(timestamp) ? timestamp : null;
}

function calendarDaysInclusive(beginAt, endAt) {
    const begin = dateTimestamp(beginAt);
    const end = dateTimestamp(endAt);
    if (begin === null || end === null || end < begin) return null;

    return Math.floor((end - begin) / 86400000) + 1;
}

function workingDaysInclusive(beginAt, endAt) {
    const begin = dateTimestamp(beginAt);
    const end = dateTimestamp(endAt);
    if (begin === null || end === null || end < begin) return null;

    let workingDays = 0;
    for (let day = begin; day <= end; day += 86400000) {
        const weekday = new Date(day).getUTCDay();
        if (weekday >= 1 && weekday <= 5) workingDays += 1;
    }

    return workingDays;
}

function formatWorkingDaysRatio(cell) {
    const data = cell.getRow().getData();
    const calendarDays = calendarDaysInclusive(data.begin_at, data.end_at);
    if (calendarDays === null) return '—';

    const enteredWorkingDays = data.working_days;
    const workingDays = enteredWorkingDays === null
        || enteredWorkingDays === undefined
        || enteredWorkingDays === ''
        ? workingDaysInclusive(data.begin_at, data.end_at)
        : Number(enteredWorkingDays);
    if (!Number.isFinite(workingDays)) return '—';

    return `${workingDays} / ${calendarDays}`;
}

export function getApplicationformColumns(metadata = {}) {
    const label = (field, fallback) => metadata[field]?.label || fallback;
    return [
        ColumnsFactory.id({ visible: true, width: 50 }),

        // L'état d'avancement du workflow est placé immédiatement après l'ID.
        {
            title: 'Validation', field: 'validation_status', width: 115,
            sorter: 'number',
            headerSortTristate: true,
            headerFilter: 'list',
            headerFilterFunc: '=',
            headerFilterParams: {
                values: {
                    '': 'Toutes',
                    '1': 'Non lancée',
                    '2': 'En cours',
                    '4': 'Acceptée',
                    '5': 'Refusée',
                    '6': 'Annulée',
                },
            },
            formatter: (cell) => {
                const data = cell.getRow().getData();
                const statuses = data.applicationformstatuses || [];
                const validationStatus = Number(cell.getValue());
                const status = statuses.find(
                    (item) => Number(item.validationstatus_id) === validationStatus,
                ) || statuses.find((item) => item.en_cours)
                    || statuses[statuses.length - 1]
                    || statuses[0];
                const percentage = Number(status?.valid_percentage || 0);
                const isAccepted = Boolean(status?.accepted) && percentage >= 100;
                const hasProgress = percentage < 100;
                const statusLabel = status?.rejected
                    ? 'Refusée'
                    : hasProgress ? `${percentage} %` : isAccepted ? 'Acceptée' : `${percentage} %`;
                const color = status?.rejected
                    ? 'danger'
                    : hasProgress ? (percentage > 0 ? 'primary' : 'secondary')
                        : isAccepted ? 'success' : 'secondary';
                return `<span class="badge bg-${color}">${statusLabel}</span>`;
            },
        },

        ColumnsFactory.text('department.code', label('department_id', 'Département'), { width: 120 }),
        ColumnsFactory.text('final_argument', 'Argumentaire général', {
            width: 50,
            widthGrow: 0,
            widthShrink: 0,
            tooltip: (_event, cell) => {
                const id = cell.getRow().getData().id ?? '—';
                const argument = cell.getValue() || '';

                return argument ? `ID: ${id}\n${argument}` : `ID: ${id}`;
            },
        }),
        ColumnsFactory.text('budgetfeature.code', 'Motif budgétaire', { width: 125 }),
        ColumnsFactory.text('hiringreason.code', 'Motif de recrutement', { width: 145 }),
        ColumnsFactory.text('candidate_name', label('candidate_name', 'Nom'), { width: 150 }),
        ColumnsFactory.text('contracttype.code', 'Type de contrat', { width: 115 }),
        ColumnsFactory.text('cgr', label('cgr', 'CGR'), { width: 100, widthGrow: 0, widthShrink: 0 }),
        ColumnsFactory.dateRange('begin_at', label('begin_at', 'Début')),
        ColumnsFactory.dateRange('end_at', label('end_at', 'Fin')),
        ColumnsFactory.text('grossremuneration', 'Rémunération envisagée', {
            width: 145,
            hozAlign: 'right',
            sorter: 'number',
            headerFilter: 'number',
            formatter: formatAmount,
        }),
        ColumnsFactory.text('period.code', 'Unité', {
            width: 90,
            widthGrow: 0,
            widthShrink: 0,
            formatter: formatPeriod,
        }),
        ColumnsFactory.text('salary_base', 'Rémunération de base', {
            width: 135,
            hozAlign: 'right',
            sorter: 'number',
            headerFilter: 'number',
            formatter: formatSalaryBase,
        }),
        ColumnsFactory.text('working_days_ratio', 'O/C', {
            width: 78,
            widthGrow: 0,
            widthShrink: 0,
            formatter: formatWorkingDaysRatio,
            headerFilter: false,
            headerTooltip: 'Ratio jours ouvrés / jours calendaires',
        }),
    ];
}

import assert from 'node:assert/strict';
import { test } from 'node:test';
import { getApplicationformColumns } from '../../webroot/js/views/Applicationforms/applicationform-columns.js';

function columnMap() {
    return new Map(getApplicationformColumns().map(column => [column.field, column]));
}

test('ordonne les colonnes de la grille des demandes selon le parcours métier', () => {
    const fields = getApplicationformColumns().map(column => column.field);

    assert.deepEqual(fields, [
        'id',
        'validation_status',
        'department.code',
        'final_argument',
        'budgetfeature.code',
        'hiringreason.code',
        'candidate_name',
        'contracttype.code',
        'cgr',
        'begin_at',
        'end_at',
        'grossremuneration',
        'period.code',
        'salary_base',
        'working_days_ratio',
    ]);
});

test('limite l’argumentaire et conserve son contenu dans l’infobulle', () => {
    const argumentColumn = columnMap().get('final_argument');
    const argument = 'Argumentaire final suffisamment long pour être réduit dans la cellule.';
    const tooltipCell = {
        getValue: () => argument,
        getRow: () => ({ getData: () => ({ id: 42 }) }),
    };
    const emptyTooltipCell = {
        getValue: () => '',
        getRow: () => ({ getData: () => ({ id: 42 }) }),
    };

    assert.equal(argumentColumn.width, 50);
    assert.equal(argumentColumn.widthGrow, 0);
    assert.equal(argumentColumn.widthShrink, 0);
    assert.equal(argumentColumn.tooltip(new Event('mousemove'), tooltipCell), `ID: 42\n${argument}`);
    assert.equal(argumentColumn.tooltip(new Event('mousemove'), emptyTooltipCell), 'ID: 42');
});

test('présente les codes de périodicité avec les libellés métier', () => {
    const unitColumn = columnMap().get('period.code');

    assert.equal(unitColumn.formatter({ getValue: () => 'K_PER_MONTH' }), 'k/mois');
    assert.equal(unitColumn.formatter({ getValue: () => 'EUR_PER_MONTH' }), '€/mois');
    assert.equal(unitColumn.formatter({ getValue: () => 'EUR_PER_DAY' }), '€/jour');
});

test('présente le ratio de jours sous forme de fraction inclusive', () => {
    const ratioColumn = columnMap().get('working_days_ratio');
    const cell = {
        getRow: () => ({
            getData: () => ({
                working_days: 5,
                begin_at: '2026-10-01',
                end_at: '2026-10-10',
            }),
        }),
    };

    assert.equal(ratioColumn.formatter(cell), '5 / 10');
    assert.equal(ratioColumn.formatter({
        getRow: () => ({
            getData: () => ({
                begin_at: '2026-10-01',
                end_at: '2026-10-10',
            }),
        }),
    }), '7 / 10');
    assert.equal(ratioColumn.formatter({
        getRow: () => ({ getData: () => ({}) }),
    }), '—');
    assert.equal(ratioColumn.title, 'O/C');
    assert.equal(ratioColumn.width, 78);
    assert.equal(ratioColumn.headerTooltip, 'Ratio jours ouvrés / jours calendaires');
});

test('calcule la rémunération de base journalière absente dans la grille', () => {
    const salaryColumn = columnMap().get('salary_base');
    const cell = {
        getValue: () => null,
        getRow: () => ({
            getData: () => ({
                grossremuneration: '2.00',
                working_days: 2,
                period: { code: 'EUR_PER_DAY' },
            }),
        }),
    };

    assert.match(salaryColumn.formatter(cell), /4,00/);
    assert.match(salaryColumn.formatter({
        getValue: () => '',
        getRow: () => ({
            getData: () => ({
                grossremuneration: '2,00',
                working_days: '2',
                period: { name: '€/jour' },
            }),
        }),
    }), /4,00/);
});

test('affiche le statut utilisé par le tri distant', () => {
    const validationColumn = columnMap().get('validation_status');
    const cell = {
        getValue: () => 4,
        getRow: () => ({
            getData: () => ({
                validation_status: 4,
                applicationformstatuses: [
                    { validationstatus_id: 2, en_cours: true, valid_percentage: 60 },
                    { validationstatus_id: 4, accepted: true, valid_percentage: 100 },
                ],
            }),
        }),
    };

    assert.match(validationColumn.formatter(cell), /Acceptée/);
});

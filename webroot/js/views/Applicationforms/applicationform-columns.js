/**
 * @file applicationform-columns.js
 * @description Définition des colonnes pour Applicationforms via ColumnsFactory.
 */

import { ColumnsFactory } from '../../core/Tabulator/ColumnsFactory.js';

export function getApplicationformColumns(metadata = {}) {
    const label = (field, fallback) => metadata[field]?.label || fallback;
    return [
        // 1. ID
        ColumnsFactory.id({ visible: true, width: 50 }),

        // 2. Département (code court)
        ColumnsFactory.text("department.code", label("department_id", "Département"), { width: 120 }),

        // 3. Personne / Collaborateur
        ColumnsFactory.text("candidate_name", label("candidate_name", "Personne / Collaborateur"), { width: 150 }),

        // 4. Type de contrat
        ColumnsFactory.text("contracttype.code", label("contracttype_id", "Contrat"), { width: 110 }),

        // 5. CGR
        ColumnsFactory.text("cgr", label("cgr", "CGR"), {
            width: undefined, // Supprime la largeur fixe arbitraire
            widthFit: "fitData",
            widthGrow: 0,
            widthShrink: 0
             }),

        // 6. Date de début
        ColumnsFactory.dateRange("begin_at", label("begin_at", "Début")),

        // 7. Date de fin
        ColumnsFactory.dateRange("end_at", label("end_at", "Fin")),

        // 8. Rémunération Brute
        ColumnsFactory.currency("grossremuneration", label("grossremuneration", "Rémunération"), { width: 100 }),

        // 9. Périodicité
        ColumnsFactory.text("period.name", label("period_id", "Période"), { width: 100 }),

        // 10. Une voix exprimée compte pour un rôle configuré, jamais pour un utilisateur.
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
                const statuses = cell.getRow().getData().applicationformstatuses || [];
                const status = statuses.find((item) => item.en_cours)
                    || statuses[statuses.length - 1]
                    || statuses[0];
                const percentage = Number(status?.valid_percentage || 0);
                const isAccepted = Boolean(status?.accepted) && percentage >= 100;
                const hasProgress = percentage < 100;
                const label = status?.rejected ? 'Refusée' : hasProgress ? `${percentage} %` : isAccepted ? 'Acceptée' : `${percentage} %`;
                const color = status?.rejected ? 'danger' : hasProgress ? (percentage > 0 ? 'primary' : 'secondary') : isAccepted ? 'success' : 'secondary';
                return `<span class="badge bg-${color}">${label}</span>`;
            },
        }
    ];
}

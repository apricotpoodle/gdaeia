/**
 * @file applicationform-columns.js
 * @description Définition des colonnes pour Applicationforms via ColumnsFactory.
 */

import { ColumnsFactory } from '../../core/Tabulator/ColumnsFactory.js';

export function getApplicationformColumns() {
    return [
        // 1. ID
        ColumnsFactory.id({ visible: true, width: 50 }),

        // 2. Département (code court)
        ColumnsFactory.text("department.code", "Département", { width: 120 }),

        // 3. Personne / Collaborateur
        ColumnsFactory.text("candidate_name", "Personne / Collaborateur", { width: 150 }),

        // 4. Type de contrat
        ColumnsFactory.text("contracttype.code", "Contrat", { width: 110 }),

        // 5. CGR
        ColumnsFactory.text("cgr", "CGR", {
            width: undefined, // Supprime la largeur fixe arbitraire
            widthFit: "fitData",
            widthGrow: 0,
            widthShrink: 0
             }),

        // 6. Date de début
        ColumnsFactory.dateRange("begin_at", "Début"),

        // 7. Date de fin
        ColumnsFactory.dateRange("end_at", "Fin"),

        // 8. Rémunération Brute
        ColumnsFactory.currency("grossremuneration", "Rémunération", { width: 100 }),

        // 9. Périodicité
        ColumnsFactory.text("period.name", "Période", { width: 100 }),

        // 10. Une voix exprimée compte pour un rôle configuré, jamais pour un utilisateur.
        {
            title: 'Validation', field: 'applicationformstatuses', width: 115, headerSort: false,
            formatter: (cell) => {
                const status = cell.getValue()?.[0];
                const percentage = Number(status?.valid_percentage || 0);
                const label = status?.rejected ? 'Refusée' : status?.accepted ? 'Acceptée' : `${percentage} %`;
                const color = status?.rejected ? 'danger' : status?.accepted ? 'success' : percentage > 0 ? 'primary' : 'secondary';
                return `<span class="badge bg-${color}">${label}</span>`;
            },
        }
    ];
}

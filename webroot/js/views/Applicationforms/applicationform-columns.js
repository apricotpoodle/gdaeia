/**
 * @file applicationform-columns.js
 * @description Définition des colonnes pour Applicationforms via ColumnsFactory.
 */

import { ColumnsFactory } from '../../core/Tabulator/ColumnsFactory.js';

export function getApplicationformColumns() {
    return [
        // 1. ID
        ColumnsFactory.id({ visible: true, width: 70 }),

        // 2. Département (code court)
        ColumnsFactory.text("department.code", "Département", { width: 120 }),

        // 3. Personne / Collaborateur
        ColumnsFactory.text("applicantname", "Personne / Collaborateur", {
            formatter: (cell) => {
                const data = cell.getRow().getData();
                if (data.applicantname) {
                    return data.applicantname;
                }
                if (data.user && data.user.email) {
                    return data.user.email;
                }
                return "-";
            },
            width: 150
        }),

        // 4. Type de contrat
        ColumnsFactory.text("contracttype.code", "Contrat", { width: 110 }),

        // 5. CGR
        ColumnsFactory.text("cgr", "CGR", { width: 130 }),

        // 6. Date de début
        ColumnsFactory.dateRange("begin_at", "Début", { width: 200 }),

        // 7. Date de fin
        ColumnsFactory.dateRange("end_at", "Fin", { width: 200 }),

        // 8. Rémunération Brute
        ColumnsFactory.currency("grossremuneration", "Rémunération", { width: 140 }),

        // 9. Périodicité
        ColumnsFactory.text("period.name", "Période", { width: 100 })
    ];
}

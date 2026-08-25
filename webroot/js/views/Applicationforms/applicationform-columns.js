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
        ColumnsFactory.text("candidate_name", "Personne / Collaborateur", {width: 150}),

        // 4. Type de contrat
        ColumnsFactory.text("contracttype.code", "Contrat", { width: 110 }),

        // 5. CGR
        ColumnsFactory.text("cgr", "CGR", { width: 130 }),

        // 6. Date de début
        ColumnsFactory.dateRange("begin_at", "Début"),

        // 7. Date de fin
        ColumnsFactory.dateRange("end_at", "Fin"),

        // 8. Rémunération Brute
        ColumnsFactory.currency("grossremuneration", "Rémunération", { width: 100 }),

        // 9. Périodicité
        ColumnsFactory.text("period.name", "Période", { width: 100 })
    ];
}

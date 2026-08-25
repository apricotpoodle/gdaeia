// ==============================================================================
// Fichier : webroot/js/core/Tabulator/ColumnFilters.js
// Rôle : Éditeurs et filtres personnalisés pour les en-têtes Tabulator
// ==============================================================================

/**
 * @file ColumnFilters.js
 * @description Filtres d'en-tête personnalisés pour les grilles Tabulator.
 */
export class ColumnFilters {

    /**
     * Éditeur de plage de dates adaptatif avec police ultra-compacte et support du reset.
     *
     * @static
     * @param {Object} cell - Composant cellule de Tabulator.
     * @param {Function} onRendered - Callback exécuté après le rendu du DOM.
     * @param {Function} success - Callback déclenchant le filtre ({ start, end }).
     * @param {Function} cancel - Callback d'annulation.
     * @param {Object} editorParams - Paramètres d'édition optionnels.
     * @returns {HTMLElement}
     */
    static dateRangeEditor(cell, onRendered, success, cancel, editorParams) {
        const container = document.createElement("div");
        container.className = "d-flex gap-1 w-100 p-1 align-items-center justify-content-between";

        // Champ Date Début
        const inputStart = document.createElement("input");
        inputStart.type = "date";
        inputStart.className = "form-control form-control-sm p-0 text-center border-secondary-subtle";
        inputStart.style.fontSize = "0.60rem";
        inputStart.style.height = "22px";
        inputStart.title = "Date de début (laisser vide pour réinitialiser)";

        // Champ Date Fin
        const inputEnd = document.createElement("input");
        inputEnd.type = "date";
        inputEnd.className = "form-control form-control-sm p-0 text-center border-secondary-subtle";
        inputEnd.style.fontSize = "0.60rem";
        inputEnd.style.height = "22px";
        inputEnd.title = "Date de fin (laisser vide pour réinitialiser)";

        container.appendChild(inputStart);
        container.appendChild(inputEnd);

        // Adaptation dynamique selon la largeur de la colonne (ResizeObserver)
        const updateLayout = (width) => {
            const breakpoint = editorParams?.responsiveBreakpoint || 120;
            if (width < breakpoint) {
                // Mode Étroit : Empilement vertical
                container.classList.remove("flex-row");
                container.classList.add("flex-column");
                inputStart.style.width = "100%";
                inputEnd.style.width = "100%";
            } else {
                // Mode Large : Côte à côte
                container.classList.remove("flex-column");
                container.classList.add("flex-row");
                inputStart.style.width = "48%";
                inputEnd.style.width = "48%";
            }
        };

        const resizeObserver = new ResizeObserver((entries) => {
            for (let entry of entries) {
                updateLayout(entry.contentRect.width);
            }
        });

        onRendered(() => {
            resizeObserver.observe(container);
            updateLayout(container.clientWidth);
        });

        // Propagation de la valeur et gestion du reset
        const onChange = () => {
            const startVal = inputStart.value || null;
            const endVal = inputEnd.value || null;

            if (!startVal && !endVal) {
                success("");
            } else {
                success({ start: startVal, end: endVal });
            }
        };

        inputStart.addEventListener("change", onChange);
        inputEnd.addEventListener("change", onChange);

        inputStart.addEventListener("keydown", (e) => { if (e.key === "Escape") cancel(); });
        inputEnd.addEventListener("keydown", (e) => { if (e.key === "Escape") cancel(); });

        return container;
    }
}

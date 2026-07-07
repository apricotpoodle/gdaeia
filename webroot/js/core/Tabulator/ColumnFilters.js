// webroot/js/core/Tabulator/ColumnFilters.js

/**
 * Éditeur personnalisé pour filtrer une plage de dates (Début / Fin)
 * Conçu pour fonctionner nativement avec le mode remote de Tabulator
 */
export const dateRangeFilterEditor = function (cell, onRendered, success, cancel, editorParams) {
    const container = document.createElement("div");
    container.style.display = "flex";
    container.style.gap = "4px";
    container.style.width = "100%";

    const startInput = document.createElement("input");
    startInput.type = "date";
    startInput.style.width = "50%";
    startInput.placeholder = "Début";

    const endInput = document.createElement("input");
    endInput.type = "date";
    endInput.style.width = "50%";
    endInput.placeholder = "Fin";

    container.appendChild(startInput);
    container.appendChild(endInput);

    function triggerFilter() {
        success({
            start: startInput.value, // Format HTML5 : AAAA-MM-JJ
            end: endInput.value
        });
    }

    startInput.addEventListener("change", triggerFilter);
    endInput.addEventListener("change", triggerFilter);

    // Évite le déclenchement intempestif des tris de colonne au clic (ADR 0024)
    container.addEventListener("click", (e) => e.stopPropagation());

    return container;
};

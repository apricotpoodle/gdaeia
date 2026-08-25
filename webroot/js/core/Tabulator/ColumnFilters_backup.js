// ==========================================
// Fichier : webroot/js/core/Tabulator/ColumnFilters.js
// ==========================================

export class ColumnFilters {
    /**
     * Éditeur de filtre personnalisé pour les plages de dates (Date Range).
     * Inclut : Boutons RAZ, Titres personnalisables, et Prévention des dates incompatibles.
     */
    static dateRangeEditor(cell, onRendered, success, cancel, editorParams) {
        const container = document.createElement("div");
        container.className = "d-flex flex-column p-1";
        container.style.minWidth = "180px";

        const titleStart = editorParams.titleStart || "Date de début de période";
        const titleEnd = editorParams.titleEnd || "Date de fin de période";

        // --- Champ Début ---
        const startGroup = document.createElement("div");
        startGroup.className = "input-group input-group-sm mb-1";

        const startInput = document.createElement("input");
        startInput.type = "date";
        startInput.className = "form-control shadow-none";
        startInput.title = titleStart;

        const startClearBtn = document.createElement("button");
        startClearBtn.className = "btn btn-outline-secondary";
        startClearBtn.type = "button";
        startClearBtn.innerHTML = "&times;";
        startClearBtn.title = "Effacer la " + titleStart.toLowerCase();

        startGroup.appendChild(startInput);
        startGroup.appendChild(startClearBtn);

        // --- Champ Fin ---
        const endGroup = document.createElement("div");
        endGroup.className = "input-group input-group-sm";

        const endInput = document.createElement("input");
        endInput.type = "date";
        endInput.className = "form-control shadow-none";
        endInput.title = titleEnd;

        const endClearBtn = document.createElement("button");
        endClearBtn.className = "btn btn-outline-secondary";
        endClearBtn.type = "button";
        endClearBtn.innerHTML = "&times;";
        endClearBtn.title = "Effacer la " + titleEnd.toLowerCase();

        endGroup.appendChild(endInput);
        endGroup.appendChild(endClearBtn);

        container.appendChild(startGroup);
        container.appendChild(endGroup);

        // Initialisation des valeurs existantes
        const currentVal = cell.getValue();
        if (currentVal && typeof currentVal === "object") {
            startInput.value = currentVal.start || "";
            endInput.value = currentVal.end || "";
        }

        // Moteur de validation et de mise à jour
        function updateFilter() {
            let start = startInput.value;
            let end = endInput.value;

            // 1. GESTION DES DATES INCOMPATIBLES (Auto-correction)
            if (start && end && start > end) {
                endInput.value = start;
                end = start;

                // Feedback visuel prolongé (2 secondes)
                endInput.classList.add("is-invalid");
                setTimeout(() => {
                    endInput.classList.remove("is-invalid");
                }, 2000);
            }

            // 2. RESTRICTION DES CALENDRIERS (Attributs HTML stricts)
            // Utiliser setAttribute force le calendrier natif à se redessiner
            if (start) {
                endInput.setAttribute("min", start);
            } else {
                endInput.removeAttribute("min");
            }

            if (end) {
                startInput.setAttribute("max", end);
            } else {
                startInput.removeAttribute("max");
            }

            // 3. SOUMISSION
            if (!start && !end) {
                success("");
            } else {
                success({ start: start, end: end });
            }
        }

        // Initialisation immédiate des contraintes si le filtre était déjà rempli
        updateFilter();

        // Écouteurs d'événements : 'input' réagit instantanément même à la frappe clavier
        startInput.addEventListener("input", updateFilter);
        startInput.addEventListener("change", updateFilter);

        endInput.addEventListener("input", updateFilter);
        endInput.addEventListener("change", updateFilter);

        startClearBtn.addEventListener("click", (e) => {
            e.stopPropagation();
            startInput.value = "";
            updateFilter();
        });

        endClearBtn.addEventListener("click", (e) => {
            e.stopPropagation();
            endInput.value = "";
            updateFilter();
        });

        container.addEventListener("click", (e) => e.stopPropagation());

        return container;
    }
}

class TabulatorFactory {

    // ==========================================
    // GABARITS DE CONFIGURATION STRUCTUTELS (Privés)
    // ==========================================

    /**
     * GABARIT "BASE" : Initialise un Builder avec la configuration technique socle.
     * @private
     * @param {string} selector
     * @returns {TabulatorBuilder}
     */
    static #getBaseTable(selector) {
        return new TabulatorBuilder(selector)
            .setRemotePagination(20)
            .setColumnDefaults({
                headerSort: true,
                headerFilter: "input",
                headerFilterPlaceholder: "Filtrer...",
                headerFilterLiveFilter: true
            })

            .enableStatePersistence()

            // CONFIGURATION ULTRA-SIMPLE DU COMPORTEMENT SOUHAITÉ :
            // Pour tester le Scénario A (Anonymisation cellule par cellule) avec placeholder personnalisé :
            .setSecurityStrategy('CELL_MASK', '[Confidentiel]')

            // Ou pour revenir au masquage complet classique de la colonne :
            // .setSecurityStrategy('COL_HIDE')

            .setColumns([
                { title: "ID", field: "id", width: 70, sorter: "number", hozAlign: "center", headerFilterFunc: "=" }
            ])

            // AUTOMATISATION INDUSTRIELLE : Capture et publication universelle du clic de ligne
            // .addEvent("rowClick", function (e, row) {
            //     // Mouchard à la source (Si ce texte s'affiche, c'est que Tabulator va bien !)
            //     console.log("=> [Tabulator NATIF] Clic intercepté sur la ligne ID :", row.getData().id);

            //     if (typeof globalTabulatorObserver !== "undefined") {
            //         // Nettoyage du sélecteur pour générer un nom de canal propre (ex: "#users-table" -> "users-table")
            //         const channelPrefix = selector.replace('#', '');

            //         // Publication dynamique du payload de l'enregistrement concerné
            //         globalTabulatorObserver.publish(`${channelPrefix}:rowClick`, row.getData());
            //     }
            // })
            ;
        // Plus aucun événement ici, le build() s'occupe de tout en interne !
    }

    /**
     * GABARIT "ACTIONS"
     * @private
     */
    static #getActionsTable(selector) {
        return this.#getBaseTable(selector).setWithActions();
    }

    // ==========================================
    // FABRIQUES DES GRILLES APPLICATIVES (Publiques)
    // ==========================================

    /**
     * Fabrique la grille finale des Utilisateurs (Allégée au maximum)
     * @param {string} selector - Le sélecteur CSS de l'élément DOM.
     * @returns {Tabulator}
     */
    static createUsersTable(selector) {
        return this.#getActionsTable(selector)
            .setAjaxSource("/api/users.json")
            .setController('Users')
            .addColumns([
                { title: "Nom", field: "lastname", sorter: "string" },
                { title: "Prénom", field: "firstname", sorter: "string" },
                { title: "Email", field: "email", sorter: "string" },
                { title: "Rôle", field: "role.name", sorter: "string", headerSort: false, headerFilter: false },
                { title: "Super Admin", field: "issuperuser", formatter: "tickCross", hozAlign: "center", headerSort: false, headerFilter: false }
            ])
            .addActions(['impersonate'])
            .build(); // Plus besoin d'y écrire l'écouteur dataLoaded, il est hérité !
    }
}

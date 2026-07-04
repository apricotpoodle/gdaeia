class TabulatorFactory {

    // ==========================================
    // GABARITS DE CONFIGURATION STRUCTUTELS (Privés)
    // ==========================================

    /**
     * GABARIT "BASE" : Initialise un Builder avec la configuration technique socle.
     * Intègre nativement le filtrage automatique et générique des colonnes par rapport aux droits AppEntity.
     * @private
     * @param {string} selector - Le sélecteur CSS de l'élément DOM cible.
     * @returns {TabulatorBuilder} Un monteur pré-configuré avec l'ID, la pagination et la sécurité des colonnes.
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
            .setColumns([
                { title: "ID", field: "id", width: 70, sorter: "number", hozAlign: "center", headerFilterFunc: "=" }
            ])
            // INJECTION DANS LE SOCLE COMMUN : Sécurité des colonnes globale
            .addEvent("dataLoaded", function (data) {
                // Lecture de la structure propre grid_rights
                if (data && data.length > 0 && data[0].grid_rights) {
                    const columnPermissions = data[0].grid_rights.columns || {};
                    const tableInstance = this;

                    if (tableInstance) {
                        Object.keys(columnPermissions).forEach(fieldKey => {
                            if (columnPermissions[fieldKey] === false) {
                                tableInstance.hideColumn(fieldKey);
                            }
                        });
                    }
                }
            });
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
            .addEvent("rowClick", function (e, row) {
                if (typeof globalTabulatorObserver !== "undefined") {
                    globalTabulatorObserver.publish('usersTable:rowClick', row.getData());
                }
            })
            .build(); // Plus besoin d'y écrire l'écouteur dataLoaded, il est hérité !
    }
}

/**
 * @class TabulatorFactory
 * @description Implémentation du patron de conception "Factory Method" (Fabrique).
 * Centralise et applique les standards ergonomiques et graphiques de l'entreprise.
 * Utilise un mécanisme de chaînage de gabarits internes (`#getBaseTable`, `#getActionsTable`)
 * pour instancier des configurations métiers hautement standardisées (DRY).
 * * @package Core\Tabulator
 * @author L'Équipe de Développement
 */
class TabulatorFactory {

    // ==========================================
    // GABARITS DE CONFIGURATION STRUCTURELS (Privés)
    // ==========================================

    /**
     * GABARIT "BASE" : Initialise un Builder avec la configuration technique socle.
     * Aligne automatiquement la colonne ID avec l'opérateur d'égalité stricte.
     *
     * @private
     * @param {string} selector - Le sélecteur CSS de l'élément DOM cible.
     * @returns {TabulatorBuilder} Un monteur pré-configuré avec l'ID et la pagination distante.
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
                {
                    title: "ID",
                    field: "id",
                    width: 70,
                    sorter: "number",
                    hozAlign: "center",
                    headerFilterFunc: "=" // Force l'opérateur d'égalité stricte côté Front-End
                }
            ]);
    }

    /**
     * GABARIT "ACTIONS" : Enrichit le gabarit BASE en y injectant les boutons CRUD par défaut.
     *
     * @private
     * @param {string} selector - Le sélecteur CSS de l'élément DOM cible.
     * @returns {TabulatorBuilder} Un monteur pré-configuré prêt pour la gestion des actions.
     */
    static #getActionsTable(selector) {
        return this.#getBaseTable(selector)
            .setWithActions(); // Injecte par défaut ['view', 'edit', 'delete']
    }

    // ==========================================
    // FABRIQUES DES GRILLES APPLICATIVES (Publiques)
    // ==========================================

    /**
     * Fabrique la grille finale optimisée pour le module d'administration des Utilisateurs.
     * Consomme le gabarit ACTIONS pour hériter de l'ID et du triptyque CRUD.
     *
     * @param {string} selector - Le sélecteur CSS de l'élément DOM cible (ex: "#users-table").
     * @returns {Tabulator} L'instance active et opérationnelle de la grille Utilisateurs.
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
            .addActions(['impersonate'])
            .build();
    }
}

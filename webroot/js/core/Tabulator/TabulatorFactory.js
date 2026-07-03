/**
 * @class TabulatorFactory
 * * Implémentation du patron de conception "Factory" (Fabrique).
 * Centralise et normalise la création des grilles de données métiers de l'application.
 * Consomme le TabulatorBuilder pour injecter les requis d'entreprise de manière standardisée.
 * * @package Core\Tabulator
 */
class TabulatorFactory {

    /**
     * Fabrique la grille standard pour le module de gestion des Utilisateurs.
     * Active par défaut le tri multi-colonnes et le filtrage par en-tête sur chaque colonne.
     * * @param {string} selector Le sélecteur DOM cible dans lequel injecter la grille.
     * @returns {Tabulator} L'instance configurée de la grille Utilisateurs.
     */
    static createUsersTable(selector) {
        return new TabulatorBuilder(selector)
            .setAjaxSource("/api/users.json")
            .setController('Users')
            .setRemotePagination(20)
            // CARACTÉRISTIQUE DE BASE UNIQUE : On active globalement le tri et le filtre textuel
            .setColumnDefaults({
                headerSort: true,          // Active le tri sur toutes les colonnes par défaut
                headerFilter: "input",     // Active un champ de recherche textuel sur toutes les colonnes
                headerFilterPlaceholder: "Filtrer...",
                headerFilterLiveFilter: true // Attend la touche Entrée pour ne pas surcharger l'API à chaque lettre
            })
            .setColumns([
                { title: "ID", field: "id", width: 70, sorter: "number" },
                { title: "Nom", field: "lastname", sorter: "string" },
                { title: "Prénom", field: "firstname", sorter: "string" },
                { title: "Email", field: "email", sorter: "string" },
                // Surcharge locale : on désactive le filtrage/tri sur les relations ou booléens complexes si nécessaire
                { title: "Rôle", field: "role.name", sorter: "string", headerSort: false, headerFilter: false },
                { title: "Super Admin", field: "issuperuser", formatter: "tickCross", hozAlign: "center", headerSort: false, headerFilter: false }
            ])
            .addEvent("rowClick", function (e, row) {
                if (typeof globalTabulatorObserver !== "undefined") {
                    globalTabulatorObserver.publish('usersTable:rowClick', row.getData());
                }
            })
            // En appelant la méthode à vide, elle injecte automatiquement ['view', 'edit', 'delete']
            .setWithActions()
            .addActions(['impersonate'])
            .build();
    }
}

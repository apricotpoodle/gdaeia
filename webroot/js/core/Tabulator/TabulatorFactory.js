/**
 * @class TabulatorFactory
 * * Implémentation du patron de conception "Factory" (Fabrique).
 * Centralise la définition des grilles métier de l'application. 
 * Utilise le TabulatorBuilder pour garantir l'uniformité.
 */
class TabulatorFactory {
    
    /**
     * Fabrique la grille standard pour la liste des Utilisateurs.
     * * @param {string} selector Le sélecteur DOM cible
     * @returns {Tabulator} L'instance configurée de la grille Utilisateurs
     */
    static createUsersTable(selector) {
        return new TabulatorBuilder(selector)
            .setAjaxSource("/api/users.json")
            .setRemotePagination(20)
            .setColumns([
                {title: "ID", field: "id", width: 70, sorter: "number"},
                {title: "Nom", field: "lastname", sorter: "string"},
                {title: "Prénom", field: "firstname", sorter: "string"},
                {title: "Email", field: "email", sorter: "string"},
                {title: "Rôle", field: "role.name", sorter: "string", headerSort: false},
                {title: "Super Admin", field: "issuperuser", formatter: "tickCross", align: "center", headerSort: false}
            ])
            .addEvent("rowClick", function(e, row) {
                // Exemple d'intégration avec l'Observer
                globalTabulatorObserver.publish('usersTable:rowClick', row.getData());
            })
            .build();
    }

    // Nous pourrons ajouter ici static createDepartmentsTable(), createRolesTable(), etc.
}
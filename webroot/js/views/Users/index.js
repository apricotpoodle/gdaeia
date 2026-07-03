/**
 * Script d'instanciation de la grille des utilisateurs.
 * Dépendance : Tabulator.js
 * * Connecté au point de terminaison : GET /api/users.json
 */
document.addEventListener('DOMContentLoaded', function() {
    
    /**
     * Configuration et montage de la table des utilisateurs.
     * La pagination et le tri sont délégués au serveur (mode "remote").
     * @type {Tabulator}
     */
    const usersTable = new Tabulator("#users-table", {
        ajaxURL: "/api/users.json",
        pagination: true,
        paginationMode: "remote",
        sortMode: "remote",
        paginationSize: 20,
        layout: "fitColumns",
        columns: [
            {title: "ID", field: "id", width: 70, sorter: "number"},
            {title: "Nom", field: "lastname", sorter: "string"},
            {title: "Prénom", field: "firstname", sorter: "string"},
            {title: "Email", field: "email", sorter: "string"},
            // Navigation dans l'objet imbriqué renvoyé par l'ORM (Users contain Roles)
            {title: "Rôle", field: "role.name", sorter: "string", headerSort: false},
            // Formateur visuel pour les booléens
            {title: "Super Admin", field: "issuperuser", formatter: "tickCross", align: "center", headerSort: false}
        ],
    });
});
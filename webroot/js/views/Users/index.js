/**
 * Script d'instanciation de la grille des utilisateurs.
 * Dépendance : Tabulator.js
 * * Connecté au point de terminaison : GET /api/users.json
 */
document.addEventListener('DOMContentLoaded', function () {

    /**
     * Configuration et montage de la table des utilisateurs.
     * La pagination et le tri sont délégués au serveur (mode "remote").
     * @type {Tabulator}
     */
    // Instanciation ultra-propre via la Factory
    const usersTable = TabulatorFactory.createUsersTable("#users-table");

    // // Exemple de réaction à un événement global via l'Observer (pour test)
    // globalTabulatorObserver.subscribe('usersTable:rowClick', (userData) => {
    //     console.log("Utilisateur sélectionné via l'Observer :", userData.email);
    // });

    // 2. Écoute des actions Globales (En-tête)
    globalTabulatorObserver.subscribe('#users-table:action:create', () => {
        console.log("Événement reçu : Création d'un nouvel utilisateur.");
        alert("Action : Ouvrir le formulaire de création");
    });

    globalTabulatorObserver.subscribe('#users-table:action:reset', () => {
        console.log("Événement reçu : Réinitialisation des tris et filtres.");
    });

    // 3. Écoute des actions Locales (Lignes)
    globalTabulatorObserver.subscribe('#users-table:action:view', (user) => {
        console.log("Voir l'utilisateur :", user);
    });

    globalTabulatorObserver.subscribe('#users-table:action:edit', (user) => {
        console.log("Éditer l'utilisateur :", user);
        alert(`Action : Éditer l'utilisateur ${user.firstname} ${user.lastname}`);
    });

    globalTabulatorObserver.subscribe('#users-table:action:delete', (user) => {
        if (confirm(`Êtes-vous sûr de vouloir supprimer l'utilisateur ${user.email} ?`)) {
            console.log("Suppression confirmée pour l'ID :", user.id);
        }
    });

    globalTabulatorObserver.subscribe('#users-table:action:impersonate', (user) => {
        console.log("Incarner :", user.email);
    });
});

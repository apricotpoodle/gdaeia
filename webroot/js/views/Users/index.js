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
    // Instanciation ultra-propre via la Factory
    const usersTable = TabulatorFactory.createUsersTable("#users-table");

    // Exemple de réaction à un événement global via l'Observer (pour test)
    globalTabulatorObserver.subscribe('usersTable:rowClick', (userData) => {
        console.log("Utilisateur sélectionné via l'Observer :", userData.email);
    });
});
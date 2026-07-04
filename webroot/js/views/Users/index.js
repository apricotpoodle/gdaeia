/**
 * Script d'instanciation de la grille des utilisateurs.
 * Dépendance : Tabulator.js
 * Connecté au point de terminaison : GET /api/users.json
 */
document.addEventListener('DOMContentLoaded', function () {

    /**
     * Configuration et montage de la table des utilisateurs.
     * La pagination et le tri sont délégués au serveur (mode "remote").
     * @type {Tabulator}
     */
    // Instanciation ultra-propre via la Factory
    const usersTable = TabulatorFactory.createUsersTable("#users-table");

    if (typeof globalTabulatorObserver !== "undefined") {

        // 1. Écoute du clic sur une ligne (hors actions)
        globalTabulatorObserver.subscribe('#users-table:rowClick', (userData) => {
            console.log("=== LIGNE CLIQUÉE ===");
            console.log("Utilisateur sélectionné via l'Observer :", userData.email);
        });

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

        // ==========================================
        // ACTION : MODIFIER (edit)
        // ==========================================
        globalTabulatorObserver.subscribe('#users-table:action:edit', (user) => {
            if (confirm(`Voulez-vous ouvrir la page de modification pour l'utilisateur ${user.firstname} ${user.lastname} ?`)) {
                // Plus d'URL en dur ! On utilise la route dynamique calculée par le Builder
                window.location.href = user._actionUrl;
            }
        });

        // ==========================================
        // ACTION : SUPPRIMER (delete)
        // ==========================================
        // globalTabulatorObserver.subscribe('#users-table:action:delete', (user) => {
        //     if (confirm(`Êtes-vous sûr de vouloir supprimer l'utilisateur ${user.email} ?`)) {
        //         // Plus d'URL en dur ! On utilise la route dynamique calculée par le Builder
        //         window.location.href = user._actionUrl;
        //     }
        // });
        // ==========================================
        // ACTION : SUPPRIMER (Silencieux / Ajax)
        // ==========================================
        globalTabulatorObserver.subscribe('#users-table:action:delete', async (user) => {
            if (confirm(`⚠️ ATTENTION : Êtes-vous sûr de vouloir supprimer définitivement l'utilisateur ${user.email} ?`)) {

                try {
                    // 1. Récupération du jeton de sécurité CakePHP
                    const csrfToken = document.querySelector('meta[name="csrfToken"]')?.getAttribute('content');

                    // 2. Appel asynchrone (Ajax) vers l'URL calculée par l'infrastructure
                    const response = await fetch(user._actionUrl, {
                        method: 'POST', // ou DELETE selon votre configuration de route CakePHP
                        headers: {
                            'X-CSRF-Token': csrfToken,
                            'Accept': 'application/json',
                            'X-Requested-With': 'XMLHttpRequest'
                        }
                    });

                    // 3. Traitement de la réponse
                    if (response.ok) {
                        // Succès : Suppression visuelle et notification
                        usersTable.deleteRow(user.id);
                        FlashManager.success(`L'utilisateur <strong>${user.lastname}</strong> a été supprimé avec succès.`);
                    } else {
                        // Échec : Tentative de récupération du message d'erreur natif de CakePHP
                        let serverMessage = `Erreur serveur (Code ${response.status})`;

                        try {
                            const errorPayload = await response.json();
                            if (errorPayload && errorPayload.message) {
                                serverMessage = errorPayload.message; // Message métier précis renvoyé par CakePHP
                            }
                        } catch (e) {
                            // Repli au cas où le serveur crashe violemment et renvoie du HTML brut au lieu du JSON
                            console.warn("La réponse d'erreur du serveur n'est pas un JSON valide.");
                        }

                        // On lève l'exception pour passer directement dans le catch avec le bon message
                        throw new Error(serverMessage);
                    }
                } catch (error) {
                    console.error("Erreur lors de la suppression :", error);

                    // 4. Notification Flash dynamique d'erreur
                    // Si l'erreur vient du fetch (réseau coupé), error.message vaudra "Failed to fetch"
                    // Si elle vient du serveur, elle contiendra le message métier CakePHP
                    FlashManager.error(`<strong>Action refusée :</strong> ${error.message}`);
                }
            }
        });

        // ==========================================
        // ACTION : INCARNER (Impersonate)
        // ==========================================
        globalTabulatorObserver.subscribe('#users-table:action:impersonate', (user) => {
            console.log("Incarner :", user.email);
        });
    }
});

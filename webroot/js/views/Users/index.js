// // ==============================================================================
// // Fichier : webroot/js/views/Users/index.js
// // Rôle : Orchestrateur de la vue Index des Utilisateurs
// // ==============================================================================

import { TabulatorFactory } from '../../core/Tabulator/TabulatorFactory.js';
import { globalTabulatorObserver } from '../../core/Tabulator/TabulatorObserver.js';
import { FlashManager } from '../../core/FlashManager.js';

// 1. Instanciation directe (plus besoin d'attendre le DOMContentLoaded)
const usersTable = TabulatorFactory.createUsersGrid("#users-table");

// 2. Écouteurs d'événements
if (globalTabulatorObserver) {

    globalTabulatorObserver.subscribe('#users-table:rowClick', (userData) => {
        console.log("=== LIGNE CLIQUÉE ===");
        console.log("Utilisateur sélectionné :", userData.email);
    });

    globalTabulatorObserver.subscribe('#users-table:action:create', () => {
        alert("Action : Ouvrir le formulaire de création");
    });

    globalTabulatorObserver.subscribe('#users-table:action:edit', (user) => {
        if (confirm(`Voulez-vous ouvrir la page de modification pour l'utilisateur ${user.firstname} ${user.lastname} ?`)) {
            window.location.href = user._actionUrl;
        }
    });

    globalTabulatorObserver.subscribe('#users-table:action:delete', async (user) => {
        if (confirm(`⚠️ ATTENTION : Êtes-vous sûr de vouloir supprimer définitivement l'utilisateur ${user.email} ?`)) {
            try {
                const csrfToken = document.querySelector('meta[name="csrfToken"]')?.getAttribute('content');
                if (!csrfToken) throw new Error("Jeton CSRF manquant.");

                const response = await fetch(user._actionUrl, {
                    method: 'POST', // ou DELETE selon votre route
                    headers: {
                        'X-CSRF-Token': csrfToken,
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });

                if (response.ok) {
                    usersTable.deleteRow(user.id);
                    FlashManager.success(`L'utilisateur <strong>${user.lastname}</strong> a été supprimé avec succès.`);
                } else {
                    let serverMessage = `Erreur serveur (Code ${response.status})`;
                    try {
                        const errorPayload = await response.json();
                        if (errorPayload && errorPayload.message) {
                            serverMessage = errorPayload.message;
                        }
                    } catch (e) {
                        console.warn("La réponse d'erreur du serveur n'est pas un JSON valide.");
                    }
                    throw new Error(serverMessage);
                }
            } catch (error) {
                console.error("Erreur lors de la suppression :", error);
                FlashManager.error(`<strong>Action refusée :</strong> ${error.message}`);
            }
        }
    });
}

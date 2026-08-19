// ==============================================================================
// Fichier : webroot/js/views/Applicationforms/index.js
// Rôle : Orchestrateur de la vue Index des Demandes de Recrutement
// Standard : Module ES6 & Event-Driven (Miroir de Users/index.js)
// ==============================================================================

import { TabulatorFactory } from '../../core/Tabulator/TabulatorFactory.js';
import { globalTabulatorObserver } from '../../core/Tabulator/TabulatorObserver.js';
import { FlashManager } from '../../core/FlashManager.js';

const tableSelector = "#applicationforms-table";

// 1. Instanciation directe de la grille via la Factory (identique à Users)
const applicationformsTable = TabulatorFactory.createApplicationformsGrid(tableSelector);

// 2. Écouteurs d'événements Pub/Sub sur le bus global
if (globalTabulatorObserver) {

    // Signal d'action : Clic sur "Créer" dans le menu engrenage d'en-tête
    globalTabulatorObserver.subscribe(`${tableSelector}:action:create`, () => {
        window.location.href = '/applicationforms/add';
    });

    // Signal d'action : Clic sur le bouton de ligne "Consulter"
    globalTabulatorObserver.subscribe(`${tableSelector}:action:view`, (demande) => {
        window.location.href = demande._actionUrl;
    });

    // Signal d'action : Clic sur le bouton de ligne "Éditer"
    globalTabulatorObserver.subscribe(`${tableSelector}:action:edit`, (demande) => {
        window.location.href = demande._actionUrl;
    });

    // Signal d'action : Suppression asynchrone (AJAX / Fetch) avec jeton CSRF
    globalTabulatorObserver.subscribe(`${tableSelector}:action:delete`, async (demande) => {
        const posteTitle = demande.jobtitle || `#${demande.id}`;
        const demandeId = demande.id;
        if (confirm(`⚠️ ATTENTION : Êtes-vous sûr de vouloir supprimer définitivement la demande n° ${demandeId} ("${posteTitle}") ?`)) {
            try {
                // Extraction du jeton CSRF injecté par le Layout principal
                const csrfToken = document.querySelector('meta[name="csrfToken"]')?.getAttribute('content');
                if (!csrfToken) throw new Error("Jeton CSRF manquant.");

                const response = await fetch(demande._actionUrl, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-Token': csrfToken,
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });

                if (response.ok) {
                    applicationformsTable.deleteRow(demande.id);
                    FlashManager.success(`La demande pour le poste <strong>${posteTitle}</strong> a été supprimée.`);
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

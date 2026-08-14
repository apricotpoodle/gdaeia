// ==============================================================================
// Fichier : webroot/js/views/FieldAuthorizations/index.js
// Rôle : Orchestrateur de la vue Index des Autorisations de Champs (Standardisé sur Users)
// ==============================================================================

import { TabulatorFactory } from '../../core/Tabulator/TabulatorFactory.js';
import { globalTabulatorObserver } from '../../core/Tabulator/TabulatorObserver.js';
import { FlashManager } from '../../core/FlashManager.js';

const tableSelector = "#fieldauthorizations-grid";

// 1. Instanciation directe de la grille via la Factory
const fieldAuthTable = TabulatorFactory.createFieldAuthorizationsGrid(tableSelector);

// 2. Écouteurs d'événements (Identique à Users/index.js)
if (globalTabulatorObserver) {

    // Redirection vers la page de création
    globalTabulatorObserver.subscribe(`${tableSelector}:action:create`, () => {
        window.location.href = '/field-authorizations/add';
    });

    // Redirection vers la page d'édition (utilise l'URL auto-générée par le TabulatorBuilder)
    globalTabulatorObserver.subscribe(`${tableSelector}:action:edit`, (rule) => {
        window.location.href = rule._actionUrl;
    });

    // Suppression asynchrone (AJAX) avec jeton CSRF
    globalTabulatorObserver.subscribe(`${tableSelector}:action:delete`, async (rule) => {
        if (confirm(`⚠️ ATTENTION : Êtes-vous sûr de vouloir supprimer la règle pour la ressource ${rule.resource}.${rule.field} ?`)) {
            try {
                // Récupération du jeton CSRF natif CakePHP
                const csrfToken = document.querySelector('meta[name="csrfToken"]')?.getAttribute('content');
                if (!csrfToken) throw new Error("Jeton CSRF manquant.");

                // L'URL cible est directement fournie par la structure (ex: /field-authorizations/delete/1)
                const response = await fetch(rule._actionUrl, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-Token': csrfToken,
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });

                if (response.ok) {
                    fieldAuthTable.deleteRow(rule.id);
                    FlashManager.success(`La règle a été supprimée avec succès.`);
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

// ==============================================================================
// Fichier : webroot/js/core/Tabulator/TabulatorObserver.js
// Rôle : Bus d'événements centralisé (Observer Pattern) pour Tabulator
// Standard : Module ES6 (Etanchéité et Singleton applicatif)
// ==============================================================================

/**
 * @class TabulatorObserver
 * @description Centralise la gestion des abonnements et des publications d'événements
 * pour découpler les grilles Tabulator de leurs orchestrateurs de vues métiers.
 */
class TabulatorObserver {
    constructor() {
        /**
         * Registre des événements et de leurs fonctions de rappel (callbacks).
         * @type {Object.<string, Function[]>}
         */
        this.events = {};
    }

    /**
     * S'abonne à un événement spécifique.
     * @param {string} event - Le nom unique de l'événement (ex: '#users-table:action:delete').
     * @param {Function} callback - La fonction à exécuter lors du déclenchement.
     */
    subscribe(event, callback) {
        if (!this.events[event]) {
            this.events[event] = [];
        }
        this.events[event].push(callback);
    }

    /**
     * Publie un événement et transmet les données associées à tous les abonnés.
     * @param {string} event - Le nom unique de l'événement.
     * @param {*} data - Le payload ou l'entité de données associée (ex: l'objet User).
     */
    publish(event, data) {
        if (!this.events[event]) return;
        this.events[event].forEach(callback => callback(data));
    }
}

/**
 * SOURCE UNIQUE DE VÉRITÉ : Instance unique (Singleton) partagée par l'ensemble de l'application.
 * Les modules doivent importer uniquement cette constante pour communiquer.
 * @type {TabulatorObserver}
 */
export const globalTabulatorObserver = new TabulatorObserver();

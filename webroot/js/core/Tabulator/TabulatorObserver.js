/**
 * @class TabulatorObserver
 * * Implémentation du patron de conception "Observer" (Pub/Sub).
 * Agit comme un bus d'événements global pour orchestrer les interactions 
 * entre plusieurs instances de grilles Tabulator sur une même page, 
 * sans créer de couplage fort entre elles.
 */
class TabulatorObserver {
    constructor() {
        /**
         * Dictionnaire des abonnés.
         * @type {Object.<string, Array<Function>>}
         */
        this.subscribers = {};
    }

    /**
     * S'abonne à un événement spécifique.
     * * @param {string} event Le nom de l'événement (ex: 'usersTable:rowClick')
     * @param {Function} callback La fonction à exécuter lors de l'émission
     */
    subscribe(event, callback) {
        if (!this.subscribers[event]) {
            this.subscribers[event] = [];
        }
        this.subscribers[event].push(callback);
    }

    /**
     * Émet un événement vers tous les abonnés.
     * * @param {string} event Le nom de l'événement
     * @param {*} [data] Les données à transmettre aux abonnés
     */
    publish(event, data = null) {
        if (!this.subscribers[event]) return;
        this.subscribers[event].forEach(callback => callback(data));
    }
}

// Instance globale pour l'application
const globalTabulatorObserver = new TabulatorObserver();
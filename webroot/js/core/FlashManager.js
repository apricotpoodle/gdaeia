// ==============================================================================
// Fichier : webroot/js/core/FlashManager.js
// Rôle : Gestionnaire de notifications Flash dynamiques (Etanchéité Module ES6)
// ==============================================================================

/**
 * @class FlashManager
 * @description Générateur dynamique de messages Flash (Toasts/Alerts) basés sur Bootstrap 5.
 * Ne nécessite aucun conteneur HTML préexistant (généré à la volée).
 */
export class FlashManager {

    /**
     * Crée ou récupère le conteneur flottant pour les alertes.
     * @private
     * @returns {HTMLElement}
     */
    static #getContainer() {
        let container = document.getElementById('dynamic-flash-container');
        if (!container) {
            container = document.createElement('div');
            container.id = 'dynamic-flash-container';
            // Positionnement Bootstrap : Fixé en haut à droite, au-dessus de tout (z-index élevé)
            container.className = 'position-fixed top-0 end-0 p-3';
            container.style.zIndex = '1055';
            document.body.appendChild(container);
        }
        return container;
    }

    /**
     * Affiche un message Flash.
     * @param {string} message - Le texte ou HTML à afficher.
     * @param {string} type - Variant Bootstrap ('success', 'danger', 'warning', 'info').
     * @param {number} duration - Durée d'affichage en millisecondes (0 = infini).
     */
    static show(message, type = 'success', duration = 5000) {
        const container = this.#getContainer();
        const alert = document.createElement('div');

        // Classes Bootstrap 5 pour une alerte avec animation d'apparition
        alert.className = `alert alert-${type} alert-dismissible fade show shadow-sm d-flex align-items-center`;
        alert.innerHTML = `
            <div>${message}</div>
            <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert" aria-label="Fermer"></button>
        `;

        container.appendChild(alert);

        // Auto-destruction propre (DOM Garbage Collection)
        if (duration > 0) {
            setTimeout(() => {
                alert.classList.remove('show');
                // Attente de la fin de l'animation CSS avant suppression physique
                setTimeout(() => alert.remove(), 150);
            }, duration);
        }
    }

    // Raccourcis sémantiques restaurés (Syntactic sugar)
    static success(msg, duration = 5000) { this.show(`<i class="fas fa-check-circle me-2"></i>${msg}`, 'success', duration); }
    static error(msg, duration = 7000) { this.show(`<i class="fas fa-exclamation-triangle me-2"></i>${msg}`, 'danger', duration); }
    static warning(msg, duration = 5000) { this.show(`<i class="fas fa-exclamation-circle me-2"></i>${msg}`, 'warning', duration); }
    static info(msg, duration = 5000) { this.show(`<i class="fas fa-info-circle me-2"></i>${msg}`, 'info', duration); }
}

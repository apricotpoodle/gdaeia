/**
 * @typedef {Object} ButtonConfig
 * @property {string} icon - Les classes FontAwesome de l'icône (ex: 'fas fa-eye')
 * @property {string} color - Le variant de couleur Bootstrap (ex: 'info', 'primary')
 * @property {string} title - Le libellé d'accessibilité et tooltip du bouton
 * @property {string} [target='_self'] - La cible de navigation ('_self' ou '_blank')
 * @property {boolean} [isEvent=false] - Si true, bypass la redirection et émet un événement JS via l'Observer
 */

/**
 * @class ButtonFactory
 * @description Fabrique centralisée pour la génération des boutons et menus d'actions des tables Tabulator.
 * Aligné sur les conventions de routage CakePHP.
 */
class ButtonFactory {

    /**
     * Registre centralisé des configurations de boutons de l'application.
     * Évolutif jusqu'à plus de 30 boutons différents sans altérer la logique métier.
     * @private
     * @type {Object<string, ButtonConfig>}
     */
    static #configs = {
        view: { icon: 'fas fa-eye', color: 'info', title: 'Visualiser la fiche', target: '_self' },
        edit: { icon: 'fas fa-edit', color: 'primary', title: 'Modifier l\'enregistrement', target: '_self' },
        delete: { icon: 'fas fa-trash', color: 'danger', title: 'Supprimer l\'enregistrement', isEvent: true },
        viewpdf: { icon: 'fas fa-file-pdf', color: 'warning', title: 'Ouvrir le document PDF', target: '_blank' },
        impersonate: { icon: 'fas fa-user-secret', color: 'secondary', title: 'Infiltrer la session utilisateur', target: '_self' }
    };

    /**
     * Génère le balisage HTML d'un bouton d'action en prenant en compte ses permissions.
     * @param {string} key - Clé de l'action (`view`, `edit`, `delete`)
     * @param {Object} rowPermissions - L'objet de permissions de la ligne (ex: {view: true, edit: false})
     * @returns {string} Code HTML brut du bouton
     */
    static getCellButton(key, rowPermissions = {}) {
        const config = this.#configs[key];
        if (!config) return '';

        // Déduction du droit : si la clé vaut explicitement false, on bloque !
        const isAllowed = rowPermissions[key] !== false;

        const target = config.target || '_self';
        const isEvent = config.isEvent ? 'true' : 'false';

        // Si interdit, on injecte les classes Bootstrap d'invalidation
        const disabledClass = isAllowed ? '' : 'disabled pe-none opacity-25';

        return `
            <button type="button"
                    class="btn btn-sm btn-${config.color} shadow-sm me-1 btn-action ${disabledClass}"
                    data-action="${key}"
                    data-target="${target}"
                    data-is-event="${isEvent}"
                    ${isAllowed ? '' : 'disabled="disabled"'}
                    title="${isAllowed ? config.title : 'Action non autorisée'}">
                <i class="${config.icon} fa-fw"></i>
            </button>
        `;
    }

    /**
     * Génère le menu d'administration globale déporté pour l'en-tête de la colonne Actions.
     * Exclut Popper.js pour éliminer les conflits de débordement DOM avec Tabulator.
     * @returns {string} Code HTML du menu déroulant manuel d'en-tête
     */
    static getHeaderDropdown() {
        return `
            <div class="d-flex align-items-center justify-content-center" style="position: relative;">
                <button class="btn shadow-sm btn-sm btn-danger action-menu-btn" type="button" title="Menu des actions globales">
                    <i class="fas fa-cog"></i>
                </button>
                <ul class="dropdown-menu shadow" style="position: absolute; top: 100%; right: 0; z-index: 9999; margin-top: 5px;">
                    <li>
                        <button class="dropdown-item text-success action-create fw-bold" type="button">
                            <i class="fas fa-plus-circle me-2"></i> Créer un enregistrement
                        </button>
                    </li>
                    <li><hr class="dropdown-divider"></li>
                    <li>
                        <button class="dropdown-item text-warning action-reset fw-bold" type="button">
                            <i class="fas fa-undo me-2"></i> Réinitialiser les filtres
                        </button>
                    </li>
                </ul>
                <span class="fw-bold ms-2">Actions</span>
            </div>
        `;
    }
}

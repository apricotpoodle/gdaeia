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
 */
export class ButtonFactory {

    /**
     * Registre centralisé des configurations de boutons de l'application.
     * @private
     * @type {Object<string, ButtonConfig>}
     */
    static #configs = {
        view: { icon: 'fas fa-eye', color: 'info', title: 'Visualiser la fiche', target: '_self' },
        edit: { icon: 'fas fa-edit', color: 'primary', title: 'Modifier l\'enregistrement', target: '_self', isEvent: true },
        delete: { icon: 'fas fa-trash', color: 'danger', title: 'Supprimer l\'enregistrement', isEvent: true },
        viewpdf: { icon: 'fas fa-file-pdf', color: 'warning', title: 'Ouvrir le document PDF', target: '_blank' },
        impersonate: { icon: 'fas fa-user-secret', color: 'secondary', title: 'Infiltrer la session utilisateur', target: '_self' }
    };

    /**
     * Génère le balisage HTML d'un bouton d'action en prenant en compte ses permissions.
     */
    static getCellButton(key, rowPermissions = {}) {
        const config = this.#configs[key];
        if (!config) return '';

        const isAllowed = rowPermissions[key] !== false;

        // 💡 BAIL EARLY : Si l'action n'est pas autorisée, on ne génère pas de balise HTML
        if (!isAllowed) {
            return '';
        }

        const target = config.target || '_self';
        const isEvent = config.isEvent ? 'true' : 'false';

        // Le bouton généré est systématiquement cliquable et pur
        return `
            <button type="button"
                    class="btn btn-sm btn-${config.color} shadow-sm me-1 btn-action"
                    data-action="${key}"
                    data-target="${target}"
                    data-is-event="${isEvent}"
                    title="${config.title}">
                <i class="${config.icon} fa-fw"></i>
            </button>
        `;
    }

    /**
     * Génère le menu d'actions globales pour l'en-tête de la colonne Actions.
     * @param {Object} globalPermissions - Les permissions globales (ex: {create: true})
     * @returns {string} Code HTML du menu déroulant manuel d'en-tête
     */
    static getHeaderDropdown(globalPermissions = {}) {
        const canCreate = globalPermissions.create === true;

        // 💡 On ne génère le bouton "Créer" que s'il est autorisé
        const createItemHtml = canCreate ? `
                    <li>
                        <button class="dropdown-item text-success action-create fw-bold"
                                data-action="create"
                                type="button">
                            <i class="fas fa-plus-circle me-2"></i> Créer un enregistrement
                        </button>
                    </li>
                    <li><hr class="dropdown-divider"></li>
        ` : '';

        return `
            <div class="dropdown d-flex align-items-center justify-content-center" style="position: relative;">
                <button class="btn shadow-sm btn-sm btn-danger action-menu-btn" type="button" title="Menu des actions globales">
                    <i class="fas fa-cog"></i>
                </button>
                <ul class="dropdown-menu shadow position-absolute" style="top: 100%; right: 0; z-index: 9999; margin-top: 5px; display: none;">
                    ${createItemHtml}
                    <li>
                        <button class="dropdown-item text-warning action-reset fw-bold" data-action="reset" type="button">
                            <i class="fas fa-undo me-2"></i> Réinitialiser les filtres
                        </button>
                    </li>
                </ul>
                <span class="fw-bold ms-2">Actions</span>
            </div>
        `;
    }
}

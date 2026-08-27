/**
 * @typedef {Object} ButtonConfig
 * @property {string} icon - Les classes FontAwesome de l'icône (ex: 'fas fa-eye')
 * @property {string} color - Le variant de couleur Bootstrap (ex: 'info', 'primary')
 * @property {string} title - Le libellé d'accessibilité et tooltip du bouton
 * @property {string} [target='_self'] - La cible de navigation ('_self' ou '_blank')
 * @property {boolean} [isEvent=false] - Si true, bypass la redirection et émet un événement JS via l'Observer
 */

import { ButtonBuilder } from './ButtonBuilder.js';
import { DropdownBuilder } from './DropdownBuilder.js';

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
        impersonate: { icon: 'fas fa-user-secret', color: 'secondary', title: 'Incarner la session utilisateur', target: '_self' }
    };

    /**
     * Génère le balisage HTML d'un bouton d'action en prenant en compte ses permissions.
     */
    static getCellButton(key, rowPermissions = {}) {
        const config = this.#configs[key];
        if (!config) return '';


        // 💡 VERROU DE SÉCURITÉ OPT-IN :
        // 1. On vérifie d'abord si la clé existe explicitement dans rowPermissions.
        // 2. On s'assure que sa valeur est strictement vraie (true).
        // Si la clé n'est pas transmise par l'API (ex: 'impersonate' omit en mode usurpation), le bouton N'EST PAS généré.
        const hasKey = Object.prototype.hasOwnProperty.call(rowPermissions, key);
        const isAllowed = hasKey && Boolean(rowPermissions[key]);
        // const isAllowed = rowPermissions[key] !== false;

        // 💡 BAIL EARLY : Si l'action n'est pas autorisée, on ne génère pas de balise HTML
        if (!isAllowed) {
            return '';
        }

        const target = config.target || '_self';
        const isEvent = config.isEvent ? 'true' : 'false';

        return new ButtonBuilder()
            .setColor(config.color)
            .setAction(key)
            .setData('target', config.target || '_self')
            .setData('is-event', config.isEvent ? 'true' : 'false')
            .setTitle(config.title)
            .setIcon(`${config.icon} fa-fw`)
            .build();

    }

    /**
     * Génère le menu d'actions globales pour l'en-tête de la colonne Actions.
     * @param {Object} globalPermissions - Les permissions globales (ex: {create: true})
     * @returns {string} Code HTML du menu déroulant d'en-tête
     */
    static getHeaderDropdown(globalPermissions = {}) {
        const canCreate = globalPermissions.create === true;

        // 1. Bouton déclencheur (Engrenage) construit via ButtonBuilder
        const triggerBtnHtml = new ButtonBuilder()
            .setClasses(['btn', 'shadow-sm', 'btn-sm', 'btn-danger', 'action-menu-btn'])
            .setTitle('Menu des actions globales')
            .setIcon('fas fa-cog')
            .build();

        // 2. Assemblage du Dropdown via DropdownBuilder
        const dropdown = new DropdownBuilder()
            .setTrigger(triggerBtnHtml)
            .setLabel('Actions');

        // 3. Ajout conditionnel du bouton "Créer"
        if (canCreate) {
            const createBtnHtml = new ButtonBuilder()
                .setClasses(['dropdown-item', 'text-success', 'action-create', 'fw-bold'])
                .setAction('create')
                .setIcon('fas fa-plus-circle me-2')
                .setText('Créer un enregistrement')
                .build();

            dropdown
                .addItem(createBtnHtml)
                .addDivider();
        }

        // 4. Ajout du bouton "Réinitialiser les filtres"
        const resetBtnHtml = new ButtonBuilder()
            .setClasses(['dropdown-item', 'text-warning', 'action-reset', 'fw-bold'])
            .setAction('reset')
            .setIcon('fas fa-undo me-2')
            .setText('Réinitialiser les filtres')
            .build();

        dropdown.addItem(resetBtnHtml);

        // 5. Génération du HTML final
        return dropdown.build();
    }

    /**
     * Génère le menu d'actions globales pour l'en-tête de la colonne Actions.
     * @param {Object} globalPermissions - Les permissions globales (ex: {create: true})
     * @returns {string} Code HTML du menu déroulant d'en-tête
     */
    static getHeaderDropdown_backup2(globalPermissions = {}) {
        const canCreate = globalPermissions.create === true;

        // 1. Bouton "Créer un enregistrement"
        let createItemHtml = '';
        if (canCreate) {
            const createBtnHtml = new ButtonBuilder()
                .setClasses(['dropdown-item', 'text-success', 'action-create', 'fw-bold'])
                .setAction('create')
                .setIcon('fas fa-plus-circle me-2')
                .setText('Créer un enregistrement')
                .build();

            createItemHtml = `
                <li>${createBtnHtml}</li>
                <li><hr class="dropdown-divider"></li>
            `;
        }

        // 2. Bouton "Réinitialiser les filtres"
        const resetBtnHtml = new ButtonBuilder()
            .setClasses(['dropdown-item', 'text-warning', 'action-reset', 'fw-bold'])
            .setAction('reset')
            .setIcon('fas fa-undo me-2')
            .setText('Réinitialiser les filtres')
            .build();

        return `
            <div class="dropdown d-flex align-items-center justify-content-center" style="position: relative;">
                <button class="btn shadow-sm btn-sm btn-danger action-menu-btn" type="button" title="Menu des actions globales">
                    <i class="fas fa-cog"></i>
                </button>
                <ul class="dropdown-menu shadow position-absolute" style="top: 100%; right: 0; z-index: 9999; margin-top: 5px; display: none;">
                    ${createItemHtml}
                    <li>${resetBtnHtml}</li>
                </ul>
                <span class="fw-bold ms-2">Actions</span>
            </div>
        `;
    }

    /**
     * Génère le menu d'actions globales pour l'en-tête de la colonne Actions.
     * @param {Object} globalPermissions - Les permissions globales (ex: {create: true})
     * @returns {string} Code HTML du menu déroulant manuel d'en-tête
     */
    static getHeaderDropdown_backup(globalPermissions = {}) {
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

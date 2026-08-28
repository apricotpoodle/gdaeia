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
        impersonate: { icon: 'fas fa-user-secret', color: 'secondary', title: 'Incarner la session utilisateur', target: '_self' },
        moveUp: { icon: 'fas fa-arrow-up', color: 'secondary', title: 'Monter', isEvent: true },
        moveDown: { icon: 'fas fa-arrow-down', color: 'secondary', title: 'Descendre', isEvent: true }
    };

    /**
     * Génère le balisage HTML d'un bouton d'action en prenant en compte ses permissions.
     *
     * @param {string} key L'identifiant de l'action
     * @param {Object} [rowPermissions={}] L'objet des permissions pour la ligne courante
     * @returns {string} Le balisage HTML du bouton ou une chaîne vide
     */
    static getCellButton(key, rowPermissions = {}, recordId = null) {
        const config = this.#configs[key];
        if (!config) return '';

        // 💡 VERROU DE SÉCURITÉ (Cross-Browser Chrome/Firefox) :
        // 1. Validation de l'objet pour éviter les TypeError si rowPermissions est null/undefined (Proxy Tabulator)
        if (!rowPermissions || typeof rowPermissions !== 'object') {
            return '';
        }

        // 2. Vérification stricte : la permission doit explicitement valoir `true`.
        // L'accès direct `rowPermissions[key]` gère mieux les Proxies/Getters que `hasOwnProperty` dans Firefox.
        const isAllowed = (rowPermissions[key] === true);

        // 💡 BAIL EARLY : Si l'action n'est pas strictement autorisée, on retourne une chaîne vide.
        if (!isAllowed) {
            return '';
        }

        const builder = new ButtonBuilder()
            .setColor(config.color)
            .setAction(key)
            .setData('target', config.target || '_self')
            .setData('is-event', config.isEvent ? 'true' : 'false')
            .setTitle(config.title)
            .setIcon(`${config.icon} fa-fw`);

        // Injecte l'ID s'il est fourni
        if (recordId !== null && recordId !== undefined) {
            builder.setData('id', String(recordId));
        }

        return builder.build();
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

}

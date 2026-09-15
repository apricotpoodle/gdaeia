// ==============================================================================
// Fichier : webroot/js/core/Tabulator/TabulatorFactory.js
// Rôle : Centralisation de l'instanciation des composants grilles métiers
// ==============================================================================

import { TabulatorBuilder } from './TabulatorBuilder.js';
import { ColumnsFactory } from './ColumnsFactory.js'; // inutile à terme car définitions des colonnes déléguée *-columns.js
import { getApplicationformColumns } from '../../views/Applicationforms/applicationform-columns.js';
import { getMenusColumns } from '../../views/Menus/menus-columns.js';

/**
 * @class TabulatorFactory
 * @description Centralisation des fabriques de création de grilles Tabulator métiers.
 */
export class TabulatorFactory {

    /**
     * SOCLE COMMUN (DRY)
     * Définit le standard UX/UI de l'entreprise pour une grille de données classique.
     * Maintient impérativement le système de contrôle et la pagination en HAUT.
     * @private
     * @param {string} selector - Le sélecteur CSS de l'élément cible.
     * @returns {TabulatorBuilder} L'instance configurée du builder.
     */
    static _createBaseGrid(selector) {
        return new TabulatorBuilder(selector)
            .enableStatePersistence() // Mémorisation locale
            .setContinuousScroll(20)  // 💡 NOUVEAU STANDARD : Défilement infini par lots de 20
            // 💡 Applique une règle de base à toutes les colonnes de la table
            .setColumnDefaults({
                widthGrow: 1,
                tooltip: true // (Exemple : active les infobulles partout par défaut)
            })
            ;
    }

    static createBaseGrid(selector) {
        return this._createBaseGrid(selector);
    }
    /**
     * SOCLE COMMUN (DRY)
     * Définit le standard UX/UI de l'entreprise pour une grille de données avec colonne Actions.
     * @private
     * @param {string} selector - Le sélecteur CSS de l'élément cible.
     * @returns {TabulatorBuilder} L'instance configurée du builder.
     */
    static _createActionGrid(selector) {
        return this._createBaseGrid(selector)
            .setWithActions()
            ;
    }

    static createActionGrid(selector) {
        return this._createActionGrid(selector);
    }

    /**
     * Fabrique dédiée à la configuration de la grille des Utilisateurs (USERS).
     * @static
     * @param {string} selector - Le sélecteur CSS cible.
     * @returns {Tabulator} L'instance finale de la grille Tabulator.
     */
    static createUsersGrid(selector) {
        return this._createActionGrid(selector)
            .setAjaxSource('/api/users.json')
            .setController('users')
            .addActions(['impersonate'])
            // .disablePagination() // < --- SUPPRIMÉ : On veut conserver les 20 lignes!
            // .setHeight("100%)") // 100% du parent flexbox
            // 💡 LA FENÊTRE : Il FAUT une hauteur absolue pour forcer l'ascenseur INTERNE.
            // Si Flexbox bugue, utilise un calc strict pour garantir l'apparition de l'ascenseur.
            .setHeight("calc(100vh - 180px)") // <--- AJOUT : Bloque la grille avant le bas de l'écran
            // .setHeight("calc(100vh - 220px)")
            // .setHeight("calc(100vh)")
            .setColumns([
                ColumnsFactory.id({ visible: true }),
                ColumnsFactory.text("firstname", "Prénom"),
                ColumnsFactory.text("lastname", "Nom"),
                ColumnsFactory.text("role.name", "Rôle"),
                // ColumnsFactory.text("username", "Identifiant", { frozen: true }),
                ColumnsFactory.text("email", "Adresse Email"),
                ColumnsFactory.boolean("issuperuser", "Admin."),
                // ColumnsFactory.dateRange("created", "Date d'inscription"),
                // ColumnsFactory.dateRange("modified", "Dernière modification")
            ])
            .build();
    }

    /**
     * Fabrique de la grille distante du référentiel des rôles.
     *
     * @param {string} selector Sélecteur CSS cible.
     * @returns {Tabulator} Instance Tabulator configurée.
     */
    static createRolesGrid(selector) {
        return this._createActionGrid(selector)
            .setAjaxSource('/api/roles.json')
            .setController('roles')
            .setHeight('calc(100vh - 180px)')
            .setColumns([
                ColumnsFactory.id({ visible: true }),
                ColumnsFactory.text('code', 'Code'),
                ColumnsFactory.text('name', 'Libellé'),
                ColumnsFactory.text('sort', 'Clé de tri'),
            ])
            .build();
    }

    /**
     * Fabrique de la grille distante des utilisateurs à ajouter à un périmètre.
     * Les filtres d'en-tête sont traduits par l'API Tabulator existante.
     *
     * @param {string} selector Sélecteur CSS cible.
     * @param {Map<number, Object>} selectedUsers Utilisateurs déjà retenus, indexés par identifiant.
     * @returns {Tabulator} Instance Tabulator.
     */
    static createBulkDepartmentsGrid(selector = '#bulk-departments-table') {
        return this._createBaseGrid(selector)
            .setAjaxSource('/api/users/bulk-departments-tree.json')
            .disablePagination()
            .setHeight('100%')
            .addOptions({
                persistence: false,
                dataTree: true,
                dataTreeStartExpanded: false,
                dataTreeChildField: 'children',
                selectableRows: true,
                layout: 'fitColumns',
            })
            .setColumns([ColumnsFactory.text('name', 'Département')])
            .build();
    }

    static createBulkAvailableUsersGrid(selector = '#bulk-available-users-table') {
        return this._createBaseGrid(selector)
            .setAjaxSource('/api/users/bulk-departments-users.json')
            .addOptions({ persistence: false, layout: 'fitColumns' })
            .setColumns(this.getBulkUsersColumns())
            .build();
    }

    /**
     * Fabrique de la grille locale des utilisateurs retenus pour l'opération de masse.
     *
     * @param {string} selector Sélecteur CSS cible.
     * @returns {Tabulator} Instance Tabulator.
     */
    static createBulkSelectedUsersGrid(selector = '#bulk-selected-users-table') {
        return this._createBaseGrid(selector)
            .setAjaxSource('/api/users/bulk-departments-assigned-users.json')
            .addOptions({ persistence: false, layout: 'fitColumns' })
            .setColumns(this.getBulkUsersColumns())
            .build();
    }

    /**
     * Colonnes communes aux deux grilles de sélection de masse.
     *
     * @returns {Array<Object>} Configurations de colonnes Tabulator.
     */
    static getBulkUsersColumns() {
        return [
            ColumnsFactory.text('lastname', 'Nom'),
            ColumnsFactory.text('firstname', 'Prénom'),
            ColumnsFactory.text('email', 'Adresse courriel'),
            ColumnsFactory.text('role.code', 'Rôle'),
        ];
    }

    static createFieldAuthorizationsGrid(selector = "#fieldauthorizations-grid") {
        return this._createActionGrid(selector)
            .setAjaxSource('/api/field-authorizations.json')
            .setController('field_authorizations')
            .setHeight('calc(100vh - 240px)')
            .setColumns([
                { title: 'ID', field: 'id', width: 70 },
                {
                    title: 'Rôle',
                    field: 'role.name',
                    formatter: (cell) => cell.getRow().getData().role?.name || 'N/A'
                },
                { title: 'Ressource', field: 'resource', headerFilter: 'input' },
                { title: 'Champ', field: 'field', headerFilter: 'input' },
                {
                    title: 'Niveau d\'Accès',
                    field: 'access_level',
                    formatter: (cell) => {
                        const val = cell.getValue();
                        const classes = { 'EDIT': 'bg-success', 'VIEW': 'bg-info text-dark', 'NONE': 'bg-danger' };
                        return `<span class="badge ${classes[val] || 'bg-secondary'}">${val || 'N/A'}</span>`;
                    }
                }
            ])
            .setWithActions(['edit', 'delete'])
            .build();
    }

    /**
     * Fabrique dédiée à la configuration de la grille des Demandes de Recrutement (APPLICATIONFORMS).
     * @static
     * @param {string} [selector="#applicationforms-table"] - Le sélecteur CSS cible.
     * @returns {Tabulator} L'instance finale de la grille Tabulator.
     */
    static createApplicationformsGrid(selector = "#applicationforms-table") {
        return this._createActionGrid(selector)
            .setAjaxSource('/api/applicationforms.json')
            // 💡 SUPPRESSION DE fitColumns : On autorise le comportement fitDataFill
            .setLayout("fitDataFill")
            .setController('applicationforms')
            .setHeight("calc(100vh - 180px)")
            .setColumns(getApplicationformColumns())
            .setWithActions(['view', 'edit', 'delete'])
            .build();
    }

    /**
         * Fabrique dédiée à la configuration de l'arbre des Menus (MENUS).
         * @static
         * @param {string} [selector="#menus-grid"] - Le sélecteur CSS cible.
         * @returns {Tabulator} L'instance finale de la grille Tabulator.
         */
    static createMenusGrid(selector = "#menus-grid") {
        return this._createActionGrid(selector)
            .setAjaxSource('/api/menus/grid.json')
            .setController('menus')
            .setHeight('calc(100vh - 180px)')
            .disablePagination()
            .addOptions({
                pagination: false,
                progressiveLoad: false,
                persistence: false, // Bloque la propagation de l'option aux colonnes
                dataTree: true,
                dataTreeStartExpanded: true,
                dataTreeChildField: "children",
                layout: "fitColumns",
                ajaxResponse: function(url, params, response) {
                    return response.data;
                }
            })
            .setColumns(getMenusColumns())
            .setWithActions(['edit', 'delete', 'moveUp', 'moveDown'])
            .build();
    }

    /**
     * Arbre des options à administrer dans l'écran d'accès par rôle.
     * Cette grille est locale au workflow : elle ne porte ni actions CRUD,
     * ni pagination distante, et accepte une sélection multiple.
     *
     * @param {string} selector Sélecteur CSS cible.
     * @returns {Tabulator} Instance Tabulator.
     */
    static createRoleAccessMenusGrid(selector = '#role-access-menus-table') {
        return this._createBaseGrid(selector)
            .setAjaxSource('/api/menus/role-access-tree.json')
            .disablePagination()
            .setHeight('100%')
            .addOptions({
                persistence: false,
                dataTree: true,
                dataTreeStartExpanded: false,
                dataTreeChildField: 'children',
                selectableRows: true,
                layout: 'fitColumns',
            })
            .setColumns([
                ColumnsFactory.text('name', 'Option de menu'),
            ])
            .build();
    }

    /**
     * Grille locale des rôles accessibles à l'opérateur.
     * Son contenu est fourni par l'orchestrateur afin d'exclure immédiatement
     * les rôles présents dans la grille des associations.
     *
     * @param {string} selector Sélecteur CSS cible.
     * @returns {Tabulator} Instance Tabulator.
     */
    static createRoleAccessAvailableRolesGrid(selector = '#role-access-available-roles-table') {
        return this._createBaseGrid(selector)
            .setLocalData([])
            .disablePagination()
            .setHeight('100%')
            .addOptions({ persistence: false, layout: 'fitColumns' })
            .setColumns(this.getRoleAccessColumns())
            .build();
    }

    /**
     * Grille locale des rôles qui possèdent toutes les options sélectionnées.
     *
     * @param {string} selector Sélecteur CSS cible.
     * @returns {Tabulator} Instance Tabulator.
     */
    static createRoleAccessSelectedRolesGrid(selector = '#role-access-selected-roles-table') {
        return this._createBaseGrid(selector)
            .setLocalData([])
            .disablePagination()
            .setHeight('100%')
            .addOptions({ persistence: false, layout: 'fitColumns' })
            .setColumns(this.getRoleAccessColumns())
            .build();
    }

    /**
     * Définit les colonnes partagées par les deux grilles de rôles.
     *
     * @returns {Array<Object>} Configurations de colonnes Tabulator.
     */
    static getRoleAccessColumns() {
        return [
            ColumnsFactory.text('code', 'Code', { widthGrow: 0.6 }),
            ColumnsFactory.text('name', 'Rôle'),
        ];
    }

    static createValidationSequencesDepartmentsGrid(selector = '#validation-sequences-departments-table') {
        return this._createBaseGrid(selector)
            .setAjaxSource('/api/validationsequences/departments-tree.json')
            .disablePagination()
            .setHeight('100%')
            .addOptions({ persistence: false, dataTree: true, dataTreeStartExpanded: false, dataTreeChildField: 'children', selectableRows: 1, layout: 'fitColumns' })
            .setColumns([ColumnsFactory.text('name', 'Département')])
            .build();
    }

    static createValidationSequencesAvailableRolesGrid(selector = '#validation-sequences-available-roles-table') {
        return this._createBaseGrid(selector)
            .setLocalData([])
            .disablePagination()
            .setHeight('100%')
            .addOptions({ persistence: false, layout: 'fitColumns' })
            .setColumns(this.getRoleAccessColumns())
            .build();
    }

    static createValidationSequencesAssignedRolesGrid(selector = '#validation-sequences-assigned-roles-table') {
        return this._createBaseGrid(selector)
            .setLocalData([])
            .disablePagination()
            .setHeight('100%')
            .addOptions({ persistence: false, layout: 'fitColumns' })
            .setColumns([
                ...this.getRoleAccessColumns(),
                { title: 'Séquence', field: 'sequence', editor: 'number', editorParams: { min: 1, step: 1 }, sorter: 'number', hozAlign: 'center', width: 110 },
            ])
            .build();
    }

}

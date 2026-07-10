/**
 * @class TabulatorBuilder
 * @description Implémentation du patron de conception "Builder" (Monteur).
 * Classe technique agnostique dont l'unique responsabilité est d'accumuler
 * les configurations de la grille front-end Tabulator de manière fluide (chaînage).
 * Elle ne contient aucune règle métier ou colonne par défaut.
 * @package Core\Tabulator
 * @author L'Équipe de Développement
 */
import { globalTabulatorObserver } from './TabulatorObserver.js';
import { FlashManager } from '../FlashManager.js';
import { ButtonFactory } from './ButtonFactory.js';

export class TabulatorBuilder {

    /**
     * Initialise une configuration vierge avec le socle UX et l'internationalisation.
     * @param {string} selector - Le sélecteur CSS de l'élément DOM cible (ex: "#users-table").
     */
    constructor(selector) {
        /** @type {string} */
        this.selector = selector;

        /** @type {string|null} */
        this.controller = null;

        /** @type {Array<string>} */
        this.actionButtons = [];

        // Configuration par défaut de la stratégie de masquage visuel
        /** @type {string} - 'COL_HIDE' (colonne entière) ou 'CELL_MASK' (cellule par cellule) */
        this.securityStrategy = 'COL_HIDE';

        /** @type {string} - Texte affiché en cas de stratégie CELL_MASK */
        this.maskPlaceholder = 'Masqué';

        /** @type {Object} */
        this.config = {
            layout: "fitColumns",
            responsiveLayout: "collapse",
            multiSort: true,
            headerFilterLiveFilterDelay: 300,
            placeholder: "<div class='tabulator-empty-msg p-4 text-center text-muted'>Aucune donnée trouvée !</div>",
            ajaxLoader: true,
            ajaxLoaderLoading: "<div class='tabulator-loading-msg'><span>Chargement des données en cours...</span></div>",
            // 💡 FIX : Défini ici par défaut (Sera conservé par l'infrastructure)
            paginationPosition: "top",
            locale: "fr-fr",
            langs: {
                "fr-fr": {
                    "ajax": {
                        "loading": "Chargement",
                        "error": "Erreur de chargement des données"
                    },
                    "pagination": {
                        "page_size": "Afficher :",
                        "first": "Premier",
                        "last": "Dernier",
                        "prev": "Précédent",
                        "next": "Suivant",
                        "all": "Toutes",
                        "counter": {
                            "showing": "Lignes",
                            "of": "sur",
                            "rows": "au total",
                            "pages": "sur"
                        }
                    },
                    "headerFilters": {
                        "default": "Filtrer..."
                    }
                }
            }
        };
    }

    /**
     * Force le positionnement du système de pagination et des contrôles tout en haut de la grille.
     * @returns {this} L'instance du builder pour le chaînage.
     */
    setControlsAtTop() {
        // 1. Détection ou création d'un conteneur dédié à la pagination haute
        const targetTable = document.querySelector(this.selector);
        if (targetTable) {
            let containerId = `pagination-top-${targetTable.id || 'default'}`;
            let pContainer = document.getElementById(containerId);

            if (!pContainer) {
                pContainer = document.createElement("div");
                pContainer.id = containerId;
                // Classes de style Bootstrap pour rendre le bandeau propre et compact en haut
                pContainer.className = "tabulator-controls-top-wrapper mb-2 p-2 bg-light border rounded shadow-sm d-flex justify-content-between align-items-center";

                // Insertion physique immédiate juste AVANT l'élément de la table
                targetTable.parentNode.insertBefore(pContainer, targetTable);
            }

            // 2. Assignation de l'élément cible dans la configuration de Tabulator
            this.config.paginationElement = pContainer;
        }
        return this;
    }

    /**
     * Désactive, retire et rend complètement invisible le système de pagination et ses contrôles.
     * Supprime physiquement le conteneur HTML de pagination haute s'il a été injecté au-dessus de la table.
     * @returns {this} L'instance du builder pour le chaînage.
     */
    disablePagination() {
        this.config.pagination = false;
        // 💡 OPTIMISATION FLUIDE : Remplace "400px" par "100%" pour occuper tout l'espace disponible
        this.config.height = "100%";

        // 💡 LE FIX ABSOLU : Nettoyage physique du DOM
        // On cherche le conteneur que 'setControlsAtTop' a pu injecter juste avant
        const targetTable = document.querySelector(this.selector);
        if (targetTable) {
            const containerId = `pagination-top-${targetTable.id || 'default'}`;
            const pContainer = document.getElementById(containerId);

            // S'il existe, on le supprime définitivement du DOM pour faire disparaître la bande grise
            if (pContainer) {
                pContainer.remove();
            }
        }

        // Nettoyage complet des attributs de configuration devenus obsolètes
        if (this.config.paginationElement) {
            delete this.config.paginationElement;
        }
        delete this.config.paginationMode;
        delete this.config.paginationSize;
        delete this.config.paginationPosition;

        return this;
    }

    /**
     * Active la mémorisation de l'état de la grille (Tris et Filtres) dans le navigateur.
     * @returns {TabulatorBuilder} L'instance courante pour le chaînage.
     */
    enableStatePersistence() {
        const safePath = window.location.pathname.replace(/[\/\\]/g, '-').replace(/^-+|-+$/g, '');
        const safeSelector = this.selector.replace(/[^a-zA-Z0-9_-]/g, '');

        this.config.persistenceID = `${safePath}-${safeSelector}`;
        this.config.persistenceMode = "local";
        this.config.persistence = {
            sort: true,
            filter: true,
            headerFilter: true
        };

        return this;
    }

    /**
     * Enregistre un écouteur d'événement directement dans la configuration native.
     * @param {string} eventName
     * @param {Function} callback
     * @returns {TabulatorBuilder}
     */
    addEvent(eventName, callback) {
        this.config[eventName] = callback;
        return this;
    }

    setSecurityStrategy(strategy, placeholder = 'Masqué') {
        this.securityStrategy = strategy;
        this.maskPlaceholder = placeholder;
        return this;
    }

    setAjaxSource(url) {
        this.config.ajaxURL = url;
        return this;
    }

    setRemotePagination(size = 20) {
        this.config.pagination = true; // 💡 Note : en Tabulator 5+, préférez 'remote' au lieu de true si besoin
        this.config.paginationMode = "remote";
        this.config.sortMode = "remote";
        this.config.filterMode = "remote";
        this.config.paginationSize = size;
        this.config.dataSendParams = { "sort": "sorters", "filter": "filters" };
        return this;
    }

    setColumnDefaults(defaults) {
        this.config.columnDefaults = defaults;
        return this;
    }

    setColumns(columns = []) {
        this.config.columns = columns;
        return this;
    }

    addColumns(columns = []) {
        if (!this.config.columns) this.config.columns = [];
        this.config.columns = [...this.config.columns, ...columns];
        return this;
    }

    setWithActions(buttons = ['view', 'edit', 'delete']) {
        this.actionButtons = buttons;
        return this;
    }

    addActions(extraButtons = []) {
        this.actionButtons = [...this.actionButtons, ...extraButtons];
        return this;
    }

    setController(controllerName) {
        this.controller = controllerName.trim().toLowerCase();
        return this;
    }

    /**
     * Compile et injecte la colonne "Actions" avec routage dynamique et gestion des droits.
     * @private
     */
    _compileActionColumn() {
        if (!this.actionButtons || this.actionButtons.length === 0) {
            return;
        }

        const actionColumn = {
            title: typeof ButtonFactory !== 'undefined' ? ButtonFactory.getHeaderDropdown() : 'Actions',
            field: "_actions",
            headerSort: false,
            headerFilter: false,
            hozAlign: "left",
            width: 240,
            formatter: (cell) => {
                let html = '<div class="d-flex justify-content-start align-items-center ps-2">';
                const rowData = cell.getRow().getData();
                const gridRights = rowData.grid_rights || {};
                const actionPermissions = gridRights.actions || {};

                this.actionButtons.forEach(btnKey => {
                    html += ButtonFactory.getCellButton(btnKey, actionPermissions);
                });

                html += '</div>';
                return html;
            },
            cellClick: (e, cell) => {
                e.stopPropagation();
                const btn = e.target.closest('.btn-action');
                if (!btn || btn.classList.contains('disabled')) return;

                const action = btn.dataset.action;
                const target = btn.dataset.target || '_self';
                const isEvent = btn.dataset.isEvent === 'true';
                const rowData = cell.getRow().getData();
                const targetController = rowData.table_controller || this.controller;
                const id = rowData.id;

                let generatedUrl = '#';
                if (targetController && action && id) {
                    generatedUrl = `/${targetController}/${action}/${id}`;
                }

                if (isEvent) {
                    if (typeof globalTabulatorObserver !== 'undefined') {
                        rowData._actionUrl = generatedUrl;
                        globalTabulatorObserver.publish(`${this.selector}:action:${action}`, rowData);
                    }
                    return;
                }

                if (generatedUrl !== '#') {
                    if (target === '_blank') {
                        window.open(generatedUrl, '_blank');
                    } else {
                        window.location.href = generatedUrl;
                    }
                }
            },
            headerClick: function (e, column) {
                const toggleBtn = e.target.closest('.action-menu-btn');
                if (toggleBtn) {
                    e.preventDefault();
                    e.stopPropagation();
                    const dropdownContainer = toggleBtn.closest('.dropdown');
                    const menu = dropdownContainer ? dropdownContainer.querySelector('.dropdown-menu') : null;

                    if (menu) {
                        const isVisible = menu.style.display === 'block';
                        document.querySelectorAll('.dropdown-menu').forEach(m => {
                            m.style.display = 'none';
                            m.classList.remove('show');
                        });

                        if (!isVisible) {
                            menu.style.display = 'block';
                            menu.classList.add('show');
                            const closeOnOutsideClick = (event) => {
                                if (!dropdownContainer.contains(event.target)) {
                                    menu.style.display = 'none';
                                    menu.classList.remove('show');
                                    document.removeEventListener('click', closeOnOutsideClick);
                                }
                            };
                            setTimeout(() => document.addEventListener('click', closeOnOutsideClick), 10);
                        }
                    }
                    return;
                }

                const actionBtn = e.target.closest('.action-create') || e.target.closest('.action-reset') || e.target.closest('[data-action]');
                if (actionBtn) {
                    e.stopPropagation();
                    e.preventDefault();

                    let action = actionBtn.dataset.action;
                    if (!action) {
                        action = actionBtn.classList.contains('action-create') ? 'create' : 'reset';
                    }

                    if (action === 'reset') {
                        const currentTable = column.getTable();
                        currentTable.clearHeaderFilter();
                        currentTable.clearSort();
                        if (typeof FlashManager !== 'undefined') {
                            FlashManager.info("Filtres et tris réinitialisés.", 3000);
                        }
                    }

                    const tableElement = column.getTable().element;
                    const currentSelector = tableElement && tableElement.id ? `#${tableElement.id}` : '#users-table';

                    if (typeof globalTabulatorObserver !== 'undefined') {
                        globalTabulatorObserver.publish(`${currentSelector}:action:${action}`);
                    }

                    const menuToHide = e.target.closest('.dropdown-menu');
                    if (menuToHide) {
                        menuToHide.style.display = 'none';
                        menuToHide.classList.remove('show');
                    }
                }
            }
        };

        const tableElement = document.querySelector(this.selector);
        const canCreateRecord = tableElement ? tableElement.getAttribute('data-can-create') === 'true' : false;
        actionColumn.title = ButtonFactory.getHeaderDropdown({ create: canCreateRecord });

        if (!this.config.columns) this.config.columns = [];
        this.config.columns.push(actionColumn);
    }

    /**
     * Finalise la construction de la grille et injecte les verrous de sécurité.
     * @returns {Tabulator} L'instance active et initialisée de Tabulator.
     */
    build() {
        // 1. Compilation automatique de la colonne d'actions
        this._compileActionColumn();

        // Stockage du statut de la pagination pour la fermeture de contexte dans la fonction anonyme
        const isPaginationDisabled = this.config.pagination === false;

        // 2. STRATÉGIE A : 'COL_HIDE' (Entièrement sécurisée pour l'Ajax brut et paginé)
        if (this.securityStrategy === 'COL_HIDE') {
            this.config.ajaxResponse = function (url, params, response) {
                // Étape A : Extraction défensive des lignes si la structure est paginée { data: [...] }
                let rowsData = [];
                if (response && response.data && Array.isArray(response.data)) {
                    rowsData = response.data;
                } else if (Array.isArray(response)) {
                    rowsData = response;
                }

                // Étape B : Application de la logique des droits d'affichage des colonnes
                if (rowsData.length > 0) {
                    const finalColumnVisibility = {};
                    Object.assign(finalColumnVisibility, rowsData[0].grid_rights?.columns || {});

                    rowsData.forEach(row => {
                        if (row.grid_rights && row.grid_rights.columns) {
                            Object.keys(row.grid_rights.columns).forEach(fieldKey => {
                                if (row.grid_rights.columns[fieldKey] === false) {
                                    finalColumnVisibility[fieldKey] = false;
                                }
                            });
                        }
                    });

                    const tableInstance = this;
                    setTimeout(() => {
                        Object.keys(finalColumnVisibility).forEach(fieldKey => {
                            if (finalColumnVisibility[fieldKey] === false) {
                                tableInstance.hideColumn(fieldKey);
                            } else {
                                tableInstance.showColumn(fieldKey);
                            }
                        });
                        tableInstance.redraw(true);
                    }, 10);
                }

                // Étape C : Retour du format attendu selon l'état de la pagination
                // Si pas de pagination -> Tabulator exige le tableau brut de lignes
                // Si pagination distante -> Tabulator exige l'objet enveloppe global
                if (isPaginationDisabled) {
                    return rowsData;
                }
                return response;
            };
        } else {
            // Si pas de stratégie COL_HIDE mais pagination désactivée, on applique l'extracteur minimal
            if (isPaginationDisabled) {
                this.config.ajaxResponse = function (url, params, response) {
                    if (response && response.data && Array.isArray(response.data)) {
                        return response.data;
                    }
                    return response;
                };
            }
        }

        // 3. STRATÉGIE B : 'CELL_MASK'
        if (this.securityStrategy === 'CELL_MASK') {
            const originalRowFormatter = this.config.rowFormatter;
            const placeholderText = this.maskPlaceholder;

            this.config.rowFormatter = function (row) {
                if (typeof originalRowFormatter === "function") {
                    originalRowFormatter(row);
                }

                const rowData = row.getData();
                if (rowData.grid_rights && rowData.grid_rights.columns) {
                    const columnPermissions = rowData.grid_rights.columns;

                    row.getCells().forEach(cell => {
                        const fieldName = cell.getColumn().getField();
                        if (columnPermissions[fieldName] === false) {
                            cell.getElement().innerHTML = `
                                <span class="text-muted text-decoration-line-through opacity-50" title="Donnée confidentielle">
                                    ${placeholderText}
                                </span>
                            `;
                        }
                    });
                }
            };
        }

        // 4. Instanciation physique du tableau
        const table = new Tabulator(this.selector, this.config);

        if (!this.config.ajaxURL) {
            console.warn("TabulatorBuilder: Aucune source Ajax configurée avant l'appel à build().");
        }

        // 5. Attachement des événements
        table.on("rowClick", (e, row) => {
            if (typeof globalTabulatorObserver !== "undefined") {
                globalTabulatorObserver.publish(`${this.selector}:rowClick`, row.getData());
            }
        });

        return table;
    }

    /**
     * Définit la hauteur de la grille pour forcer l'apparition de l'ascenseur interne
     * de Tabulator et empêcher le défilement de la fenêtre globale du navigateur.
     * @param {string} height - Valeur CSS (ex: "100%", "400px", "calc(100vh - 150px)")
     * @returns {TabulatorBuilder} L'instance courante pour le chaînage.
     */
    setHeight(height) {
        this.config.height = height;
        return this;
    }

    /**
     * Active le défilement infini (Progressive Loading).
     * Remplace la pagination classique par un chargement transparent au défilement.
     * @param {number} size - Nombre d'enregistrements récupérés par requête Ajax.
     * @returns {this} L'instance du builder pour le chaînage.
     */
    setContinuousScroll(size = 40) {
        this.config.pagination = true; // Pré-requis Tabulator pour le chargement progressif
        this.config.paginationMode = "remote";
        this.config.sortMode = "remote";
        this.config.filterMode = "remote";
        this.config.paginationSize = size;
        this.config.dataSendParams = { "sort": "sorters", "filter": "filters" };

        // Activation de la magie Tabulator
        this.config.progressiveLoad = "scroll";
        this.config.progressiveLoadScrollMargin = 300; // Marge en pixels avant de déclencher l'Ajax

        // Nettoyage de l'interface : On purge la barre de pagination classique si présente
        const targetTable = document.querySelector(this.selector);
        if (targetTable) {
            const containerId = `pagination-top-${targetTable.id || 'default'}`;
            const pContainer = document.getElementById(containerId);
            if (pContainer) {
                pContainer.remove();
            }
        }
        delete this.config.paginationElement;

        return this;
    }
}

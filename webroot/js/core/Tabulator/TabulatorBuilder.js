/**
 * @class TabulatorBuilder
 * @description Implémentation du patron de conception "Builder" (Monteur).
 * Classe technique agnostique dont l'unique responsabilité est d'accumuler
 * les configurations de la grille front-end Tabulator de manière fluide (chaînage).
 * Elle ne contient aucune règle métier ou colonne par défaut.
 * @package Core\Tabulator
 * @author L'Équipe de Développement
 */
class TabulatorBuilder {

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

        // NOUVEAU : Configuration par défaut de la stratégie de masquage visuel
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
     * Active la mémorisation de l'état de la grille (Tris et Filtres) dans le navigateur.
     * Génère automatiquement un identifiant de persistance absolument unique en combinant
     * l'URL de la page courante et le sélecteur de la table, libérant le développeur de cette contrainte.
     * @returns {TabulatorBuilder} L'instance courante pour le chaînage.
     */
    enableStatePersistence() {
        // 1. Nettoyage du chemin de l'URL (ex: "/users/index" devient "users-index")
        const safePath = window.location.pathname.replace(/[\/\\]/g, '-').replace(/^-+|-+$/g, '');

        // 2. Nettoyage du sélecteur (ex: "#main-table" devient "main-table")
        const safeSelector = this.selector.replace(/[^a-zA-Z0-9_-]/g, '');

        // 3. Combinaison infaillible assignée à la configuration
        this.config.persistenceID = `${safePath}-${safeSelector}`;
        this.config.persistenceMode = "local";
        this.config.persistence = {
            sort: true,
            filter: true,
            headerFilter: true // <--- LA CLÉ MANQUANTE : Active la mémoire des champs de saisie !
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
        this.config.pagination = true;
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
            hozAlign: "center",
            width: 240,

            formatter: (cell) => {
                // ========================================================
                // PRÉSERVATION DE VOTRE DESIGN BOOTSTRAP (ButtonFactory)
                // ========================================================
                let html = '<div class="d-flex justify-content-center align-items-center">';
                const rowData = cell.getRow().getData();

                const gridRights = rowData.grid_rights || {};
                const actionPermissions = gridRights.actions || {};

                if (typeof ButtonFactory !== 'undefined') {
                    this.actionButtons.forEach(btnKey => {
                        html += ButtonFactory.getCellButton(btnKey, actionPermissions);
                    });
                }
                html += '</div>';
                return html;
            },

            cellClick: (e, cell) => {
                // ========================================================
                // ARCHITECTURE UNIFIÉE : ÉVÉNEMENTS ET CALCUL D'URL
                // ========================================================

                // INTERCEPTION : Coupe net le bouillonnement vers le rowClick de la ligne
                e.stopPropagation();

                const btn = e.target.closest('.btn-action');
                if (!btn || btn.classList.contains('disabled')) return; // Sécurité anti-clic

                const action = btn.dataset.action;
                const target = btn.dataset.target || '_self';
                const isEvent = btn.dataset.isEvent === 'true';
                const rowData = cell.getRow().getData();

                // 1. CALCUL UNIVERSEL DE L'URL (Générique et Polymorphe)
                const targetController = rowData.table_controller || this.controller;
                const id = rowData.id;

                let generatedUrl = '#';
                if (targetController && action && id) {
                    generatedUrl = `/${targetController}/${action}/${id}`;
                }

                // 2. ROUTAGE PUB/SUB (Événement Observer)
                if (isEvent) {
                    if (typeof globalTabulatorObserver !== 'undefined') {
                        // Injection de l'URL pré-calculée dans l'objet de données
                        rowData._actionUrl = generatedUrl;
                        globalTabulatorObserver.publish(`${this.selector}:action:${action}`, rowData);
                    }
                    return;
                }

                // 3. NAVIGATION MATÉRIELLE DIRECTE
                if (generatedUrl !== '#') {
                    if (target === '_blank') {
                        window.open(generatedUrl, '_blank');
                    } else {
                        window.location.href = generatedUrl;
                    }
                }
            },

            headerClick: (e, column) => {
                // ... (votre code de gestion de menu déroulant existant)
            }
        };

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

        // 2. STRATÉGIE A : 'COL_HIDE'
        if (this.securityStrategy === 'COL_HIDE') {
            this.config.ajaxResponse = function (url, params, response) {
                if (response && response.data && response.data.length > 0) {
                    const finalColumnVisibility = {};

                    Object.assign(finalColumnVisibility, response.data[0].grid_rights?.columns || {});

                    response.data.forEach(row => {
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
                return response;
            };
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
                                <span class="text-muted text-decoration-line-through opacity-50"
                                      title="Donnée confidentielle">
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

        // 5. ATTACHEMENT DIRECT (API Tabulator 5)
        table.on("rowClick", (e, row) => {
            console.log("=> [TEST ULTIME DOM] Tabulator a détecté un clic sur la ligne ID :", row.getData().id);

            if (typeof globalTabulatorObserver !== "undefined") {
                globalTabulatorObserver.publish(`${this.selector}:rowClick`, row.getData());
            }
        });

        return table;
    }
}

/**
 * @class TabulatorBuilder
 * @description Implémentation du patron de conception "Builder" (Monteur).
 * Classe technique agnostique dont l'unique responsabilité est d'accumuler
 * les configurations de la grille front-end Tabulator de manière fluide (chaînage).
 * Elle ne contient aucune règle métier ou colonne par défaut.
 * * @package Core\Tabulator
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
     * Enregistre un écouteur d'événement directement dans la configuration native.
     * C'est la méthode la plus stable pour Tabulator 5.
     * @param {string} eventName - Le nom de l'événement Tabulator (ex: 'rowClick')
     * @param {Function} callback - La fonction à exécuter
     * @returns {TabulatorBuilder}
     */
    addEvent(eventName, callback) {
        // Injection directe et immuable dans l'objet de configuration
        this.config[eventName] = callback;
        return this;
    }

    /**
     * Configure la stratégie de sécurité visuelle applicable aux colonnes de la grille.
     * @param {'COL_HIDE'|'CELL_MASK'} strategy - Le nom court de la stratégie choisie.
     * @param {string} [placeholder='Masqué'] - Le texte de substitution requis pour CELL_MASK.
     * @returns {TabulatorBuilder} L'instance courante pour permettre le chaînage fluide.
     */
    setSecurityStrategy(strategy, placeholder = 'Masqué') {
        this.securityStrategy = strategy;
        this.maskPlaceholder = placeholder;
        return this;
    }

    /**
     * Configure l'URL de la source de données distante (API REST JSON).
     * @param {string} url - L'URL du point de terminaison JSON de l'API.
     * @returns {TabulatorBuilder} L'instance courante pour permettre le chaînage.
     */
    setAjaxSource(url) {
        this.config.ajaxURL = url;
        return this;
    }

    /**
     * Active la pagination, le tri et le filtrage distants (Remote).
     * Délègue les calculs à l'ORM de CakePHP pour préserver les performances du client.
     * @param {number} [size=20] - Le nombre de lignes à afficher par page.
     * @returns {TabulatorBuilder} L'instance courante pour permettre le chaînage.
     */
    setRemotePagination(size = 20) {
        this.config.pagination = true;
        this.config.paginationMode = "remote";
        this.config.sortMode = "remote";
        this.config.filterMode = "remote";
        this.config.paginationSize = size;
        this.config.dataSendParams = { "sort": "sorters", "filter": "filters" };
        return this;
    }

    /**
     * Spécifie la configuration globale par défaut applicable à TOUTES les colonnes.
     * @param {Object} defaults - Objet de configuration Tabulator (ex: {headerSort: true}).
     * @returns {TabulatorBuilder} L'instance courante pour permettre le chaînage.
     */
    setColumnDefaults(defaults) {
        this.config.columnDefaults = defaults;
        return this;
    }

    /**
     * Écrase et définit le tableau initial de colonnes de la grille.
     * @param {Array<Object>} columns - Tableau d'objets de définitions de colonnes.
     * @returns {TabulatorBuilder} L'instance courante pour permettre le chaînage.
     */
    setColumns(columns = []) {
        this.config.columns = columns;
        return this;
    }

    /**
     * Ajoute un groupe de colonnes à la suite du tableau de colonnes déjà configuré.
     * @param {Array<Object>} columns - Tableau de colonnes à cumuler.
     * @returns {TabulatorBuilder} L'instance courante pour permettre le chaînage.
     */
    addColumns(columns = []) {
        if (!this.config.columns) this.config.columns = [];
        this.config.columns = [...this.config.columns, ...columns];
        return this;
    }

    /**
     * Définit ou écrase l'ensemble des boutons d'actions de ligne.
     * @param {Array<string>} [buttons=['view', 'edit', 'delete']] - Le tableau complet des boutons requis.
     * @returns {TabulatorBuilder} L'instance courante pour le chaînage.
     */
    setWithActions(buttons = ['view', 'edit', 'delete']) {
        this.actionButtons = buttons;
        return this;
    }

    /**
     * Ajoute des boutons d'actions supplémentaires sans altérer le CRUD existant.
     * @param {Array<string>} extraButtons - Liste des boutons à cumuler.
     * @returns {TabulatorBuilder} L'instance courante pour le chaînage.
     */
    addActions(extraButtons = []) {
        this.actionButtons = [...this.actionButtons, ...extraButtons];
        return this;
    }

    /**
     * Associe le contrôleur CakePHP principal de la table pour le routage dynamique des URLs.
     * @param {string} controllerName - Nom du contrôleur en minuscules (ex: 'users').
     * @returns {TabulatorBuilder} L'instance courante pour le chaînage.
     */
    setController(controllerName) {
        this.controller = controllerName.trim().toLowerCase();
        return this;
    }

    /**
     * Compile et injecte la colonne "Actions" avec routage dynamique et gestion des droits.
     * @private
     * @returns {void}
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
                let html = '<div class="d-flex justify-content-center align-items-center">';
                const rowData = cell.getRow().getData();

                // Extraction chirurgicale
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
                const btn = e.target.closest('.btn-action');
                if (!btn || btn.classList.contains('disabled')) return; // Sécurité anti-clic additionnelle

                const action = btn.dataset.action;
                const target = btn.dataset.target || '_self';
                const isEvent = btn.dataset.isEvent === 'true';
                const rowData = cell.getRow().getData();

                if (isEvent) {
                    if (typeof globalTabulatorObserver !== 'undefined') {
                        globalTabulatorObserver.publish(`${this.selector}:action:${action}`, rowData);
                    }
                    return;
                }

                const targetController = rowData.table_controller || this.controller;
                const id = rowData.id;

                if (targetController && action && id) {
                    const url = `/${targetController}/${action}/${id}`;
                    if (target === '_blank') {
                        window.open(url, '_blank');
                    } else {
                        window.location.href = url;
                    }
                }
            },

            headerClick: (e, column) => {
                // ... (votre code de gestion de menu déroulant existant reste identique)
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
        // 1. Compilation automatique de la colonne d'actions (Ligne)
        this._compileActionColumn();

        // 2. STRATÉGIE A : 'COL_HIDE' (Masquage structurel pessimiste de la colonne entière)
        if (this.securityStrategy === 'COL_HIDE') {
            this.config.ajaxResponse = function (url, params, response) {
                if (response && response.data && response.data.length > 0) {
                    const finalColumnVisibility = {};

                    // Initialisation de la grille de droits avec le premier enregistrement
                    Object.assign(finalColumnVisibility, response.data[0].grid_rights?.columns || {});

                    // Règle pessimiste cumulative : si une seule ligne exige le masquage, on applique
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

        // 3. STRATÉGIE B : 'CELL_MASK' (Anonymisation cellulaire fine à géométrie constante)
        if (this.securityStrategy === 'CELL_MASK') {
            const originalRowFormatter = this.config.rowFormatter;
            const placeholderText = this.maskPlaceholder;

            // Décoration du rowFormatter pour écraser l'affichage des cellules sans casser leur formatage natif
            this.config.rowFormatter = function (row) {
                if (typeof originalRowFormatter === "function") {
                    originalRowFormatter(row);
                }

                const rowData = row.getData();
                if (rowData.grid_rights && rowData.grid_rights.columns) {
                    const columnPermissions = rowData.grid_rights.columns;

                    // On parcourt les cellules physiques de la ligne
                    row.getCells().forEach(cell => {
                        const fieldName = cell.getColumn().getField();

                        // Si l'entité PHP a marqué ce champ précis comme interdit pour cette ligne
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

        // 4. Instanciation physique du tableau Tabulator
        const table = new Tabulator(this.selector, this.config);

        if (!this.config.ajaxURL) {
            console.warn("TabulatorBuilder: Aucune source Ajax configurée avant l'appel à build().");
        }

        // ========================================================
        // 5. ATTACHEMENT DIRECT (API Tabulator 5)
        // ========================================================
        table.on("rowClick", (e, row) => {
            console.log("=> [TEST ULTIME DOM] Tabulator a détecté un clic sur la ligne ID :", row.getData().id);

            if (typeof globalTabulatorObserver !== "undefined") {
                // const channelPrefix = this.selector.replace('#', '');
                // HARMONISATION : On garde le '#' pour être identique aux boutons d'actions
                globalTabulatorObserver.publish(`${this.selector}:rowClick`, row.getData());
            }
        });

        return table
    }
}

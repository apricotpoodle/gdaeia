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
     * Enregistre un gestionnaire d'événement natif sur l'instance de la grille.
     * @param {string} eventName - Le nom de l'événement Tabulator (ex: "rowClick").
     * @param {Function} callback - La fonction de rappel à exécuter.
     * @returns {TabulatorBuilder} L'instance courante pour permettre le chaînage.
     */
    addEvent(eventName, callback) {
        this.config[eventName] = callback;
        return this;
    }

    /**
     * Compile et injecte la colonne "Actions" avec routage dynamique et gestion des droits.
     * Appelée automatiquement en fin de cycle de montage lors du build().
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
                const rowPermissions = rowData.permissions || {};

                if (typeof ButtonFactory !== 'undefined') {
                    this.actionButtons.forEach(btnKey => {
                        html += ButtonFactory.getCellButton(btnKey, rowPermissions);
                    });
                }
                html += '</div>';
                return html;
            },

            cellClick: (e, cell) => {
                const btn = e.target.closest('.btn-action');
                if (!btn) return;

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
                } else {
                    console.error(`TabulatorBuilder: Routage impossible. ID, Action ou Contrôleur manquant.`);
                }
            },

            headerClick: (e, column) => {
                const toggleBtn = e.target.closest('.action-menu-btn');
                if (toggleBtn) {
                    e.preventDefault();
                    e.stopPropagation();
                    const menu = toggleBtn.nextElementSibling;
                    if (menu && menu.classList.contains('dropdown-menu')) {
                        menu.classList.toggle('show');
                        if (menu.classList.contains('show')) {
                            const closeMenu = (event) => {
                                if (!menu.contains(event.target) && event.target !== toggleBtn) {
                                    menu.classList.remove('show');
                                    document.removeEventListener('click', closeMenu);
                                }
                            };
                            setTimeout(() => document.addEventListener('click', closeMenu), 10);
                        }
                    }
                    return;
                }

                const target = e.target.closest('.dropdown-item');
                if (!target) return;

                const parentMenu = target.closest('.dropdown-menu');
                if (parentMenu) parentMenu.classList.remove('show');

                if (target.classList.contains('action-create')) {
                    if (typeof globalTabulatorObserver !== 'undefined') {
                        globalTabulatorObserver.publish(`${this.selector}:action:create`);
                    }
                }
                else if (target.classList.contains('action-reset')) {
                    const table = column.getTable();
                    table.clearFilter(true);
                    table.clearSort();
                    if (typeof globalTabulatorObserver !== 'undefined') {
                        globalTabulatorObserver.publish(`${this.selector}:action:reset`);
                    }
                }
            }
        };

        if (!this.config.columns) this.config.columns = [];
        this.config.columns.push(actionColumn);
    }

    /**
     * Finalise l'assemblage et instancie l'objet de grille Tabulator opérationnel.
     * @returns {Tabulator} L'instance active et initialisée de Tabulator.
     */
    build() {
        this._compileActionColumn();

        if (!this.config.ajaxURL) {
            console.warn("TabulatorBuilder: Aucune source Ajax configurée avant l'appel à build().");
        }
        return new Tabulator(this.selector, this.config);
    }
}

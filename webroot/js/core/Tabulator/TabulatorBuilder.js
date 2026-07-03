/**
 * @class TabulatorBuilder
 * * Implémentation enrichie du patron de conception "Builder" (Monteur).
 * Standardise la création des configurations Tabulator pour garantir
 * le respect des principes DRY (Don't Repeat Yourself) sur le front-end.
 * Intègre nativement la possibilité d'activer/désactiver globalement ou
 * par colonne le tri unitaire/multiple et le filtrage dynamique.
 * * @package Core\Tabulator
 */
class TabulatorBuilder {
    /**
     * Initialise une configuration vierge avec les comportements structurels par défaut.
     * * @param {string} selector Le sélecteur CSS de l'élément DOM cible (ex: "#users-table").
     */
    constructor(selector) {
        /** @type {string} */
        this.selector = selector;

        /** @type {Object} */
        this.config = {
            layout: "fitColumns",
            responsiveLayout: "collapse",
            multiSort: true,
            headerFilterLiveFilterDelay: 300, // valeur par défaut
            headerFilter: "input",     // Active un champ de recherche textuel sur toutes les colonnes
            headerFilterPlaceholder: "Filtrer...",
            headerFilterLiveFilter: true, // Attend la touche Entrée pour ne pas surcharger l'API à chaque lettre
            placeholderHeaderFilter: "Aucune donnée trouvée !",

            // --- UX & INTERNATIONALISATION (SOCLE COMMUN) ---
            // placeholder: "<div class='tabulator-empty-msg'>Aucune donnée trouvée</div>",
            placeholder: function () {
                return this.getHeaderFilters().length ? "Aucune donnée trouvée !" : "Aucune Donnée."; //set placeholder based on if there are currently any header filters
            },
            dataLoaderLoading: "Chargement en cours...",
            // Configuration de l'indicateur visuel de chargement natif
            ajaxLoader: true,
            ajaxLoaderLoading: "<div class='tabulator-loading-msg'><span>Chargement des données en cours...</span></div>",

            // Dictionnaire de traduction en Français
            locale: "fr-fr",
            langs: {
                "fr-fr": {
                    "ajax": {
                        "loading": "Chargement",
                        "error": "Erreur de chargement des données",
                    },
                    "pagination": {
                        "page_size": "Afficher :",
                        "page_title": "Afficher la Page",
                        "first": "Premier",
                        "first_title": "Première Page",
                        "last": "Dernier",
                        "last_title": "Dernière Page",
                        "prev": "Précédent",
                        "prev_title": "Page Précédente",
                        "next": "Suivant",
                        "next_title": "Page Suivante",
                        "all": "Toutes",
                        "counter": {
                            "showing": "Lignes",
                            "of": "sur",
                            "rows": "au total",
                            "pages": "sur",
                        }
                    },
                    "headerFilters": {
                        "default": "Filtrer...",
                    }
                }
            }
        };
        /** @type {Array<string>} */
        this.actionButtons = ['view', 'edit', 'delete']; // CRUD par défaut
    }

    /**
     * Configure la source de données distante (API REST JSON).
     * * @param {string} url L'URL du point de terminaison JSON de l'API.
     * @returns {TabulatorBuilder} L'instance courante pour permettre le chaînage.
     */
    setAjaxSource(url) {
        this.config.ajaxURL = url;
        return this;
    }

    /**
     * Active et configure de concert la pagination, le tri et le filtrage distants.
     * Délègue l'intégralité des calculs lourds au serveur de base de données (CakePHP).
     *
     * @param {number} [size=20] Le nombre de lignes à afficher par page.
     * @returns {TabulatorBuilder} L'instance courante pour permettre le chaînage.
     */
    setRemotePagination(size = 20) {
        this.config.pagination = true;
        this.config.paginationMode = "remote";
        this.config.sortMode = "remote";
        this.config.filterMode = "remote"; // Les filtres tapés par l'utilisateur sont envoyés à l'API
        this.config.paginationSize = size;

        // MAPPING CRITIQUE : Empêche la collision avec le Paginator natif de CakePHP
        this.config.dataSendParams = {
            "sort": "sorters",   // Tabulator enverra '?sorters[...]' au lieu de '?sort[...]'
            "filter": "filters"  // Anticipation pour les filtres
        };

        return this;
    }

    /**
     * Spécifie la configuration globale par défaut applicable à TOUTES les colonnes.
     * C'est ici que l'on peut activer/désactiver le tri ou le filtrage en une seule ligne.
     * * @param {Object} defaults Objet de configuration Tabulator (ex: {headerSort: true}).
     * @returns {TabulatorBuilder} L'instance courante pour permettre le chaînage.
     */
    setColumnDefaults(defaults) {
        this.config.columnDefaults = defaults;
        return this;
    }

    /**
     * Déclare le tableau de définition des colonnes de la grille.
     * * @param {Array<Object>} columns Tableau d'objets définissant les propriétés de chaque colonne.
     * @returns {TabulatorBuilder} L'instance courante pour permettre le chaînage.
     */
    setColumns(columns) {
        this.config.columns = columns;
        return this;
    }

    /**
     * Enregistre un gestionnaire d'événement natif sur l'instance de la grille.
     * * @param {string} eventName Le nom de l'événement Tabulator (ex: "rowClick", "tableBuilt").
     * @param {Function} callback La fonction anonyme ou de rappel à exécuter.
     * @returns {TabulatorBuilder} L'instance courante pour permettre le chaînage.
     */
    addEvent(eventName, callback) {
        this.config[eventName] = callback;
        return this;
    }

    /**
         * Ajoute automatiquement une colonne "Actions" standardisée en fin de tableau.
         * Contient par défaut les boutons CRUD de ligne (Read/View, Update/Edit, Delete).
         * Le bouton 'Create' est quant à lui géré globalement dans l'en-tête.
         * * @param {Array<string>} [buttons=['view', 'edit', 'delete']] Liste des boutons à afficher par ligne.
         * @returns {TabulatorBuilder} L'instance courante pour permettre le chaînage.
         */
    setWithActions(buttons = ['view', 'edit', 'delete']) {
        const actionColumn = {
            title: typeof ButtonFactory !== 'undefined' ? ButtonFactory.getHeaderDropdown() : 'Actions',
            field: "_actions",
            headerSort: false,
            headerFilter: false,
            hozAlign: "center",
            width: 220,

            formatter: () => {
                let html = '<div class="d-flex justify-content-center align-items-center">';
                if (typeof ButtonFactory !== 'undefined') {
                    buttons.forEach(btnKey => {
                        html += ButtonFactory.getCellButton(btnKey);
                    });
                }
                html += '</div>';
                return html;
            },

            cellClick: (e, cell) => {
                const btn = e.target.closest('.btn-action');
                if (btn) {
                    const action = btn.dataset.action;
                    const rowData = cell.getRow().getData();
                    if (typeof globalTabulatorObserver !== 'undefined') {
                        globalTabulatorObserver.publish(`${this.selector}:action:${action}`, rowData);
                    }
                }
            },

            /**
             * 4. ROUTAGE DES ÉVÉNEMENTS D'EN-TÊTE
             * Intercepte et orchestre les clics sur l'en-tête de la colonne d'actions.
             * Court-circuite Popper.js pour forcer l'ouverture du menu déroulant et
             * diffuse les intentions d'actions globales via le TabulatorObserver.
             *
             * @param {Event} e L'événement de clic natif du navigateur
             * @param {Tabulator.ColumnComponent} column Le composant colonne Tabulator
             * @returns {void}
             */
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

        return this;
    }

    /**
         * Définit ou écrase l'ensemble des boutons d'actions de ligne.
         * Passer un tableau vide `[]` permet de retirer les boutons CRUD par défaut.
         * * @param {Array<string>} buttons Le tableau complet des boutons (ex: ['view', 'edit'])
         * @returns {TabulatorBuilder} L'instance courante pour le chaînage.
         */
    setWithActions(buttons = ['view', 'edit', 'delete']) {
        this.actionButtons = buttons;
        return this;
    }

    /**
     * Ajoute des boutons d'actions supplémentaires (hors CRUD) sans écraser l'existant.
     * Idéal pour compléter le triptyque de base (ex: .addActions(['impersonate'])).
     * * @param {Array<string>} extraButtons Liste des boutons à cumuler
     * @returns {TabulatorBuilder} L'instance courante pour le chaînage.
     */
    addActions(extraButtons = []) {
        this.actionButtons = [...this.actionButtons, ...extraButtons];
        return this;
    }

    /**
     * Compile la configuration et injecte la colonne Actions finale.
     * (Auparavant, cette logique était directement dans setWithActions, elle est désormais
     * exécutée au moment du build() si des boutons ont été configurés).
     * * @private
     * @returns {void}
     */
    _compileActionColumn() {
        // Si aucun bouton n'est configuré et que l'en-tête n'est pas requis, on n'ajoute pas la colonne
        if (!this.actionButtons || (this.actionButtons.length === 0 && typeof ButtonFactory === 'undefined')) {
            return;
        }

        const actionColumn = {
            title: typeof ButtonFactory !== 'undefined' ? ButtonFactory.getHeaderDropdown() : 'Actions',
            field: "_actions",
            headerSort: false,
            headerFilter: false,
            hozAlign: "center",
            width: 240, // Légèrement élargi pour accueillir les boutons cumulés

            formatter: () => {
                let html = '<div class="d-flex justify-content-center align-items-center">';
                if (typeof ButtonFactory !== 'undefined') {
                    this.actionButtons.forEach(btnKey => {
                        html += ButtonFactory.getCellButton(btnKey);
                    });
                }
                html += '</div>';
                return html;
            },

            cellClick: (e, cell) => {
                const btn = e.target.closest('.btn-action');
                if (btn) {
                    const action = btn.dataset.action;
                    const rowData = cell.getRow().getData();
                    if (typeof globalTabulatorObserver !== 'undefined') {
                        globalTabulatorObserver.publish(`${this.selector}:action:${action}`, rowData);
                    }
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
     * Finalise la construction et instancie l'objet Tabulator.
     * * @returns {Tabulator} L'instance active de Tabulator.
     */
    build() {
        // Compilation automatique de la colonne d'actions juste avant le rendu
        this._compileActionColumn();

        if (!this.config.ajaxURL) {
            console.warn("TabulatorBuilder: Aucune source Ajax configurée.");
        }
        return new Tabulator(this.selector, this.config);
    }

    /**
     * Compile la configuration accumulée et instancie l'objet de grille Tabulator.
     * * @returns {Tabulator} L'instance active et opérationnelle de Tabulator.
     */
    build() {
        if (!this.config.ajaxURL) {
            console.warn("TabulatorBuilder: Aucune source Ajax configurée avant l'appel à build().");
        }
        return new Tabulator(this.selector, this.config);
    }
}

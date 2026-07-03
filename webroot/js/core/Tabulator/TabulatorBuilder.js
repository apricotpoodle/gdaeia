/**
 * @class TabulatorBuilder
 * Implémentation enrichie du patron de conception "Builder" (Monteur).
 * Standardise la création des configurations Tabulator pour garantir
 * le respect des principes DRY (Don't Repeat Yourself) sur le front-end.
 */
class TabulatorBuilder {

    constructor(selector) {
        this.selector = selector;
        this.controller = null;

        this.config = {
            layout: "fitColumns",
            responsiveLayout: "collapse",
            multiSort: true,
            headerFilterLiveFilterDelay: 300,
            headerFilter: "input",
            headerFilterPlaceholder: "Filtrer...",
            headerFilterLiveFilter: true,

            // CORRECTION 1 : Placeholder en chaîne de caractères pure (Fini l'erreur Table Not Initialized)
            placeholder: "<div class='tabulator-empty-msg p-4 text-center text-muted'>Aucune donnée trouvée !</div>",

            ajaxLoader: true,
            ajaxLoaderLoading: "<div class='tabulator-loading-msg'><span>Chargement des données en cours...</span></div>",
            locale: "fr-fr",
            langs: {
                "fr-fr": {
                    "ajax": { "loading": "Chargement", "error": "Erreur de chargement des données" },
                    "pagination": {
                        "page_size": "Afficher :", "first": "Premier", "last": "Dernier",
                        "prev": "Précédent", "next": "Suivant", "all": "Toutes",
                        "counter": { "showing": "Lignes", "of": "sur", "rows": "au total", "pages": "sur" }
                    },
                    "headerFilters": { "default": "Filtrer..." }
                }
            }
        };

        this.actionButtons = ['view', 'edit', 'delete']; // CRUD par défaut
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

    setColumns(columns) {
        this.config.columns = columns;
        return this;
    }

    addEvent(eventName, callback) {
        this.config[eventName] = callback;
        return this;
    }

    // CORRECTION 2 : Une seule méthode setWithActions, propre et KISS
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

    _compileActionColumn() {
        if (!this.actionButtons || (this.actionButtons.length === 0 && typeof ButtonFactory === 'undefined')) {
            return;
        }

        const actionColumn = {
            title: typeof ButtonFactory !== 'undefined' ? ButtonFactory.getHeaderDropdown() : 'Actions',
            field: "_actions",
            headerSort: false,
            headerFilter: false,
            hozAlign: "center",
            width: 240,

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
                    console.error(`TabulatorBuilder: Routage impossible. ID (${id}), Action (${action}) ou Contrôleur (${targetController}) manquant.`);
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

    // CORRECTION 3 : Une seule méthode build(), la bonne !
    build() {
        this._compileActionColumn(); // Compile la colonne d'actions magique

        if (!this.config.ajaxURL) {
            console.warn("TabulatorBuilder: Aucune source Ajax configurée avant l'appel à build().");
        }
        return new Tabulator(this.selector, this.config);
    }
}

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
            // Permet par défaut le tri sur plusieurs colonnes via la touche Shift
            multiSort: true 
        };
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
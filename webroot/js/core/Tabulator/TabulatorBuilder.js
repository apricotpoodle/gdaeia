/**
 * @class TabulatorBuilder
 * * Implémentation du patron de conception "Builder" (Monteur).
 * Standardise la création des configurations Tabulator pour garantir
 * le respect des principes DRY (Don't Repeat Yourself) sur le front-end.
 */
class TabulatorBuilder {
    /**
     * Initialise une configuration vierge avec les comportements par défaut de l'application.
     * @param {string} selector Le sélecteur CSS de l'élément DOM (ex: "#users-table")
     */
    constructor(selector) {
        this.selector = selector;
        this.config = {
            layout: "fitColumns",
            responsiveLayout: "collapse"
        };
    }

    /**
     * Configure la source de données distante (API REST).
     * * @param {string} url L'URL du point de terminaison JSON
     * @returns {TabulatorBuilder} L'instance courante pour le chaînage
     */
    setAjaxSource(url) {
        this.config.ajaxURL = url;
        return this;
    }

    /**
     * Active et configure la pagination distante (pilotée par CakePHP).
     * * @param {number} size Le nombre de lignes par page (défaut: 20)
     * @returns {TabulatorBuilder} L'instance courante pour le chaînage
     */
    setRemotePagination(size = 20) {
        this.config.pagination = true;
        this.config.paginationMode = "remote";
        this.config.sortMode = "remote";
        this.config.paginationSize = size;
        return this;
    }

    /**
     * Définit les colonnes de la grille.
     * * @param {Array<Object>} columns La définition des colonnes au format Tabulator
     * @returns {TabulatorBuilder} L'instance courante pour le chaînage
     */
    setColumns(columns) {
        this.config.columns = columns;
        return this;
    }

    /**
     * Ajoute un gestionnaire d'événement sur la grille.
     * * @param {string} eventName Le nom de l'événement Tabulator (ex: "rowClick")
     * @param {Function} callback La fonction de rappel
     * @returns {TabulatorBuilder} L'instance courante pour le chaînage
     */
    addEvent(eventName, callback) {
        this.config[eventName] = callback;
        return this;
    }

    /**
     * Finalise la construction et instancie l'objet Tabulator.
     * * @returns {Tabulator} L'instance native de Tabulator
     */
    build() {
        return new Tabulator(this.selector, this.config);
    }
}
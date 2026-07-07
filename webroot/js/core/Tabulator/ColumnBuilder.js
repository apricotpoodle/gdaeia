// webroot/js/core/Tabulator/ColumnBuilder.js

export class ColumnBuilder {
    constructor(field = "", title = "") {
        this.config = {
            field: field,
            title: title
        };
    }

    setField(field) {
        this.config.field = field;
        return this;
    }

    setTitle(title) {
        this.config.title = title;
        return this;
    }

    setSorter(sorter) {
        this.config.sorter = sorter;
        return this;
    }

    setHozAlign(align) {
        this.config.hozAlign = align;
        return this;
    }

    setFormatter(formatter, params = {}) {
        this.config.formatter = formatter;
        if (Object.keys(params).length > 0) {
            this.config.formatterParams = params;
        }
        return this;
    }

    setHeaderFilter(filter, params = {}, attributes = {}) {
        this.config.headerFilter = filter;
        if (Object.keys(params).length > 0) {
            this.config.headerFilterParams = params;
        }
        if (Object.keys(attributes).length > 0) {
            this.config.headerFilterAttributes = attributes;
        }
        return this;
    }

    setHeaderFilterFunc(func) {
        this.config.headerFilterFunc = func;
        return this;
    }

    setLiveFilter(live = false) {
        this.config.headerFilterLiveFilter = live;
        return this;
    }

    setOptions(overrides = {}) {
        this.config = { ...this.config, ...overrides };
        return this;
    }

    /**
     * Retourne l'objet de configuration final pour Tabulator
     */
    build() {
        return this.config;
    }
}

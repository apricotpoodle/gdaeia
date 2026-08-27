/**
 * @class ButtonBuilder
 * Implémentation du patron "Builder" (Monteur) pour les boutons HTML.
 * Élimine la concaténation manuelle de chaînes de caractères HTML.
 */
export class ButtonBuilder {
    constructor() {
        this.classes = ['btn', 'btn-sm', 'shadow-sm', 'me-1', 'btn-action'];
        this.attributes = { type: 'button' };
        this.iconHtml = '';
        this.textStr = '';
    }

    setSize(size) {
        if (size) this.classes.push(`btn-${size}`);
        return this;
    }

    /**
     * Définit le variant de couleur Bootstrap (info, primary, danger, etc.)
     * @param {string} color
     * @returns {ButtonBuilder}
     */
    setColor(color) {
        if (color) {
            this.classes.push(`btn-${color}`);
        }
        return this;
    }

    /**
     * Remplace totalement les classes CSS par défaut (ex: pour un item de dropdown)
     * @param {string[]} classesArray
     * @returns {ButtonBuilder}
     */
    setClasses(classesArray) {
        if (Array.isArray(classesArray)) {
            this.classes = [...classesArray];
        }
        return this;
    }

    /**
     * Ajoute une classe CSS complémentaire si besoin
     * @param {string} className
     * @returns {ButtonBuilder}
     */
    addClass(className) {
        if (className && !this.classes.includes(className)) {
            this.classes.push(className);
        }
        return this;
    }

    /**
     * Dénomination directe pour data-action
     * @param {string} actionName
     * @returns {ButtonBuilder}
     */
    setAction(actionName) {
        this.attributes['data-action'] = actionName;
        return this;
    }

    /**
     * Ajoute n'importe quel attribut data-* (ex: target -> data-target, is-event -> data-is-event)
     * @param {string} key
     * @param {string} value
     * @returns {ButtonBuilder}
     */
    setData(key, value) {
        this.attributes[`data-${key}`] = value;
        return this;
    }

    /**
     * Définit le survol / l'accessibilité
     * @param {string} title
     * @returns {ButtonBuilder}
     */
    setTitle(title) {
        this.attributes['title'] = title;
        return this;
    }

    /**
     * Injection de l'icône FontAwesome avec espacement fixe fa-fw
     * @param {string} iconClass
     * @returns {ButtonBuilder}
     */
    setIcon(iconClass) {
        if (iconClass) {
            this.iconHtml = `<i class="${iconClass}"></i>`;
        }
        return this;
    }

    /**
     * Définit le texte du bouton
     * @param {string} text
     * @returns {ButtonBuilder}
     */
    setText(text) {
        this.textStr = text;
        return this;
    }

    /**
     * Génère la balise <button> finale
     * @returns {string}
     */
    build() {
        const attrs = Object.entries(this.attributes)
            .map(([key, value]) => `${key}="${value}"`)
            .join(' ');

        const innerContent = `${this.iconHtml} ${this.textStr}`.trim();
        const classList = this.classes.join(' ');

        return `<button class="${classList}" ${attrs}>${innerContent}</button>`;
    }

}


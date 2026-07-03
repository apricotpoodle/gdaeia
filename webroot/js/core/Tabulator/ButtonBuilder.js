/**
 * @class ButtonBuilder
 * Implémentation du patron "Builder" (Monteur) pour les boutons HTML.
 * Élimine la concaténation manuelle de chaînes de caractères HTML.
 */
class ButtonBuilder {
    constructor() {
        this.classes = ['btn', 'shadow-sm'];
        this.attributes = { type: 'button' };
        this.iconHtml = '';
        this.textStr = '';
    }

    setSize(size) {
        if (size) this.classes.push(`btn-${size}`);
        return this;
    }

    setColor(color) {
        this.classes.push(`btn-${color}`);
        return this;
    }

    addClass(className) {
        this.classes.push(className);
        return this;
    }

    setAction(actionName) {
        this.attributes['data-action'] = actionName;
        return this;
    }

    setTitle(title) {
        this.attributes['title'] = title;
        return this;
    }

    setIcon(iconClass) {
        this.iconHtml = `<i class="${iconClass}"></i>`;
        return this;
    }

    setText(text) {
        this.textStr = text;
        return this;
    }

    build() {
        // Construction des attributs HTML (ex: data-action="edit" title="Modifier")
        const attrs = Object.entries(this.attributes)
            .map(([key, value]) => `${key}="${value}"`)
            .join(' ');

        const innerContent = `${this.iconHtml} ${this.textStr}`.trim();
        const classList = this.classes.join(' ');

        return `<button class="${classList}" ${attrs}>${innerContent}</button>`;
    }
}

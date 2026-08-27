/**
 * @file webroot/js/core/Tabulator/DropdownBuilder.js
 * @class DropdownBuilder
 * @description Builder fluent pour l'assemblage sécurisé des menus déroulants Bootstrap.
 */
export class DropdownBuilder {
    constructor() {
        this.triggerHtml = '';
        this.items = [];
        this.labelText = '';
    }

    /**
     * Définit le bouton déclencheur (ex: le bouton engrenage)
     * @param {string} buttonHtml
     * @returns {DropdownBuilder}
     */
    setTrigger(buttonHtml) {
        this.triggerHtml = buttonHtml;
        return this;
    }

    /**
     * Ajoute un item dans la liste <ul> (ex: un bouton généré par ButtonBuilder)
     * @param {string} itemHtml
     * @returns {DropdownBuilder}
     */
    addItem(itemHtml) {
        if (itemHtml) {
            this.items.push(`<li>${itemHtml}</li>`);
        }
        return this;
    }

    /**
     * Ajoute un séparateur horizontal
     * @returns {DropdownBuilder}
     */
    addDivider() {
        this.items.push('<li><hr class="dropdown-divider"></li>');
        return this;
    }

    /**
     * Ajoute un libellé texte à côté du déclencheur (ex: "Actions")
     * @param {string} text
     * @returns {DropdownBuilder}
     */
    setLabel(text) {
        this.labelText = text;
        return this;
    }

    /**
     * Génère la structure HTML complète du dropdown
     * @returns {string}
     */
    build() {
        const listItemsHtml = this.items.join('');
        const labelHtml = this.labelText ? `<span class="fw-bold ms-2">${this.labelText}</span>` : '';

        return `
            <div class="dropdown d-flex align-items-center justify-content-center" style="position: relative;">
                ${this.triggerHtml}
                <ul class="dropdown-menu shadow position-absolute" style="top: 100%; right: 0; z-index: 9999; margin-top: 5px; display: none;">
                    ${listItemsHtml}
                </ul>
                ${labelHtml}
            </div>
        `.trim();
    }
}

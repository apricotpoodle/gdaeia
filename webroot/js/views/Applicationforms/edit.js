/**
 * @file edit.js
 * @description Orchestrateur du formulaire d'édition des demandes de recrutement.
 * @module views/Applicationforms/edit
 */
import { FlashManager } from '../../core/FlashManager.js';
import { NavigationManager } from '../../core/NavigationManager.js';

class ApplicationformEditForm {
    constructor() {
        // Tolérance si l'ID varie entre la version backup et la production
        this.formElement = document.getElementById('applicationform-edit-form') || document.getElementById('applicationform-main-form');
        this.entityId = this.formElement?.dataset.id;
        this.schema = {};
    }

    init() {
        if (!this.formElement || !this.entityId) return;

        // 1. Chargement des droits de sécurité et hydratation des options
        fetch('/api/applicationforms/get-form-schema.json', {
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
            .then(response => response.json())
            .then(payload => {
                this.schema = payload.schema || {};
                this.hydrateSelect('department-id', payload.departments || {});
                this.hydrateSelect('contracttype-id', payload.contracttypes || {});
                this.hydrateSelect('hiringreason-id', payload.hiringreasons || {});
                this.hydrateSelect('professionalcategory-id', payload.professionalcategories || {});
                this.hydrateSelect('worktime-id', payload.worktimes || {});
                this.hydrateSelect('period-id', payload.periods || {});
                this.hydrateSelect('budgetfeature-id', payload.budgetfeatures || {});
                this.hydrateSelect('yesno-id', payload.yesnos || {});
                this.hydrateSelect('collaborator-id', payload.collaborators || {});
                this.applyFieldAuthorizations();
            })
            .catch(err => console.error("Erreur lors du chargement des référentiels :", err));

        // 2. Soumission du formulaire
        this.formElement.addEventListener('submit', (e) => this.handleSubmit(e));

        // 3. Raccourci clavier Échap
        NavigationManager.registerEscapeRedirect('/applicationforms/index');
    }

    /**
     * Hydrate dynamiquement un champ select avec des options.
     * Conserve les options existantes si générées par CakePHP côté serveur (Non-régression).
     *
     * @param {string} elementId - L'ID de l'élément HTML
     * @param {Object|Array} items - Données de l'API (Dictionnaire ou Tableau d'objets)
     */
    hydrateSelect(elementId, items) {
        const select = document.getElementById(elementId);

        // Ignorer si ce n'est pas un select (protège le input hidden de TreeselectJS)
        if (!select || select.tagName !== 'SELECT') return;

        // CakePHP FormHelper a-t-il DÉJÀ généré les options côté serveur ?
        // Si le select contient déjà les options de la BDD (longueur > 1 car option vide incluse),
        // on ne touche à rien pour préserver la sélection native de CakePHP !
        if (select.options.length > 1) {
            return; // 🛑 ARRET IMMÉDIAT : On laisse CakePHP gérer !
        }

        // Si le select est vide, on procède à l'hydratation JS :
        select.innerHTML = '<option value="">-- Sélectionner --</option>';
        if (!items) return;

        const selectedValue = select.dataset.selected || select.getAttribute('value');

        // Itération 100% blindée, gérant dictionnaires simples ET objets imbriqués
        Object.entries(items).forEach(([key, val]) => {
            let optionValue, optionText;

            // Si "val" est un objet (ex: {id: 1, name: "CDI"} ou {code: "CDD", label: "CDD"})
            if (typeof val === 'object' && val !== null) {
                optionValue = val.id !== undefined ? val.id : (val.value || val.code || key);
                optionText = val.name !== undefined ? val.name : (val.label !== undefined ? val.label : val.title);
            }
            // Si "val" est une chaîne (ex: dictionnaire plat {"1": "CDI"})
            else {
                optionValue = key;
                optionText = val;
            }

            // Sécurité anti "[object Object]"
            if (typeof optionText === 'object') {
                optionText = "Erreur_Format_API";
            }

            const option = new Option(optionText, optionValue);

            // Appliquer la pré-sélection si une donnée correspond
            if (String(optionValue) === String(selectedValue)) {
                option.selected = true;
            }

            select.add(option);
        });

        // Déclenche l'événement "change" pour les composants dépendants (ex: CGR)
        if (select.value) {
            select.dispatchEvent(new Event('change', { bubbles: true }));
        }
    }

    applyFieldAuthorizations() {
        Object.entries(this.schema).forEach(([field, accessLevel]) => {
            const input = document.getElementById(field) || document.getElementsByName(field)[0];
            if (!input) return;

            const container = input.closest('.form-group-wrapper') || input.parentElement;

            if (accessLevel === 'NONE') {
                container.classList.add('d-none');
            } else if (accessLevel === 'VIEW' || accessLevel === 'READONLY') {
                input.setAttribute('disabled', 'disabled');
                input.classList.add('bg-light', 'pe-none');
            }
        });
    }

    async handleSubmit(e) {
        e.preventDefault();
        const formData = new FormData(this.formElement);
        const csrfToken = document.querySelector('meta[name="csrfToken"]')?.getAttribute('content');

        try {
            const response = await fetch(`/api/applicationforms/edit/${this.entityId}.json`, {
                method: 'POST',
                headers: {
                    'X-CSRF-Token': csrfToken,
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'application/json'
                },
                body: formData
            });

            const result = await response.json();

            if (response.ok && result.success) {
                FlashManager.success("La demande de recrutement a été mise à jour.");
                window.location.href = '/applicationforms/index';
            } else {
                throw new Error(result.message || "Erreur de validation lors de la mise à jour.");
            }
        } catch (error) {
            FlashManager.error(`<strong>Échec :</strong> ${error.message}`);
        }
    }
}

document.addEventListener('DOMContentLoaded', () => {
    const form = new ApplicationformEditForm();
    form.init();
});

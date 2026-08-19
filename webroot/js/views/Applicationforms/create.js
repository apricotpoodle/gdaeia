/**
 * @file create.js
 * @description Orchestrateur du formulaire de création d'une demande de recrutement.
 * @module views/Applicationforms/create
 */

import { FlashManager } from '../../core/FlashManager.js';
import { NavigationManager } from '../../core/NavigationManager.js';

class ApplicationformCreateForm {
    constructor() {
        this.formElement = document.getElementById('applicationform-create-form');
        this.schema = {};
    }

    init() {
        if (!this.formElement) return;

        // 1. Récupération des droits et hydratation des options
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
                this.applyFieldAuthorizations();
            })
            .catch(err => console.error("Erreur lors de l'initialisation :", err));

        // 2. Soumission asynchrone
        this.formElement.addEventListener('submit', (e) => this.handleSubmit(e));

        // 3. Navigation au clavier (Touche Échap)
        NavigationManager.registerEscapeRedirect('/applicationforms/index');
    }

    hydrateSelect(elementId, items) {
        const select = document.getElementById(elementId);
        if (!select) return;
        Object.entries(items).forEach(([id, name]) => {
            select.add(new Option(name, id));
        });
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
            const response = await fetch('/api/applicationforms/add.json', {
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
                FlashManager.success("La demande de recrutement a été créée avec succès.");
                window.location.href = '/applicationforms/index';
            } else {
                throw new Error(result.message || "Erreur de validation lors de l'enregistrement.");
            }
        } catch (error) {
            FlashManager.error(`<strong>Échec :</strong> ${error.message}`);
        }
    }
}

document.addEventListener('DOMContentLoaded', () => {
    const form = new ApplicationformCreateForm();
    form.init();
});

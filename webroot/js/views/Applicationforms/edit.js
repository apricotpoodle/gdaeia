/**
 * @file edit.js
 * @description Orchestrateur du formulaire d'édition des demandes de recrutement.
 * @module views/Applicationforms/edit
 */

import { FlashManager } from '../../core/FlashManager.js';
import { NavigationManager } from '../../core/NavigationManager.js';

class ApplicationformEditForm {
    constructor() {
        this.formElement = document.getElementById('applicationform-edit-form');
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
                this.applyFieldAuthorizations();
            })
            .catch(err => console.error("Erreur lors du chargement des référentiels :", err));

        // 2. Soumission du formulaire
        this.formElement.addEventListener('submit', (e) => this.handleSubmit(e));

        // 3. Raccourci clavier Échap
        NavigationManager.registerEscapeRedirect('/applicationforms/index');
    }

    hydrateSelect(elementId, items) {
        const select = document.getElementById(elementId);
        if (!select) return;

        const selectedValue = select.dataset.selected;
        select.innerHTML = '<option value="">-- Sélectionner --</option>';

        Object.entries(items).forEach(([id, name]) => {
            const option = new Option(name, id);
            if (String(id) === String(selectedValue)) {
                option.selected = true;
            }
            select.add(option);
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

/**
 * @file create.js
 * @description Orchestrateur du formulaire de création des utilisateurs avec bridage dynamique des champs.
 * @module views/Users/create
 */

import { FlashManager } from '/js/core/FlashManager.js';
import { NavigationManager } from '/js/core/NavigationManager.js';

class UserCreateForm {
    constructor() {
        this.formElement = document.getElementById('user-create-form');
        this.schema = {};
    }

    init() {
        if (!this.formElement) return;

        // 1. Récupération asynchrone des droits sur les champs
        fetch('/api/users/get-form-schema.json', {
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
            .then(response => response.json())
            .then(payload => {
                this.schema = payload.schema || {};
                this.hydrateRoleSelect(payload.roles || {});
                this.applyFieldAuthorizations();
            })
            .catch(err => console.error("Erreur d'initialisation du schéma:", err));

        // 2. Écouteur de soumission
        this.formElement.addEventListener('submit', (e) => this.handleSubmit(e));

        // 3. 💡 ACCESSIBILITÉ : Quitter la page avec la touche ESCAPE
        NavigationManager.registerEscapeRedirect('/users/index');
    }

    /**
     * Hydrate dynamiquement la liste des rôles
     */
    hydrateRoleSelect(roles) {
        const select = document.getElementById('role-id');
        if (!select) return;
        Object.entries(roles).forEach(([id, name]) => {
            const option = new Option(name, id);
            select.add(option);
        });
    }

    /**
     * Applique les verrous de sécurité visuels (Hiding / Disabling) sur le DOM
     */
    applyFieldAuthorizations() {
        Object.entries(this.schema).forEach(([field, accessLevel]) => {
            const input = document.getElementById(field) || document.getElementsByName(field)[0];
            if (!input) return;

            const container = input.closest('.form-group-wrapper') || input.parentElement;

            if (accessLevel === 'NONE') {
                container.classList.add('d-none'); // Masquage total
            } else if (accessLevel === 'VIEW' || accessLevel === 'READONLY') { // 💡 FIX : Intercepte "READONLY"
                input.setAttribute('disabled', 'disabled');
                input.classList.add('bg-light', 'pe-none'); // Passage en lecture seule
            }
        });
    }

    /**
     * Traitement de la soumission AJAX du formulaire
     */
    async handleSubmit(e) {
        e.preventDefault();
        const formData = new FormData(this.formElement);
        const csrfToken = document.querySelector('meta[name="csrfToken"]')?.getAttribute('content');

        try {
            const response = await fetch('/api/users/add.json', {
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
                FlashManager.success("Utilisateur créé avec succès.");
                window.location.href = '/users/index';
            } else {
                throw new Error(result.message || "Erreur lors de la validation.");
            }
        } catch (error) {
            FlashManager.error(`<strong>Échec :</strong> ${error.message}`);
        }
    }
}

document.addEventListener('DOMContentLoaded', () => {
    const form = new UserCreateForm();
    form.init();
});

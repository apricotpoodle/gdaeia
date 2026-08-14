// ==============================================================================
// Fichier : webroot/js/views/FieldAuthorizations/index.js
// Rôle : Orchestrateur de la vue Index des Autorisations de Champs
// ==============================================================================

import { TabulatorFactory } from '../../core/Tabulator/TabulatorFactory.js';
import { globalTabulatorObserver } from '../../core/Tabulator/TabulatorObserver.js';
import { FlashManager } from '../../core/FlashManager.js';

// 1. Instanciation directe via la Factory
const fieldAuthTable = TabulatorFactory.createFieldAuthorizationsGrid("#fieldauthorizations-grid");

// Métadonnées globales pour alimenter les listes déroulantes de la modale
let metadata = { roles: {}, resources: {}, accessLevels: {} };

// Chargement asynchrone des métadonnées
(async () => {
    try {
        const response = await fetch('/api/field-authorizations/get-resources-and-fields.json');
        if (response.ok) {
            metadata = await response.json();
            populateFormSelects(metadata);
        }
    } catch (e) {
        console.error('Erreur chargement métadonnées :', e);
    }
})();

// 2. Écouteurs d'événements via le canal Pub/Sub global
if (globalTabulatorObserver) {

    // Action : Ouvrir la modale pour Ajouter
    globalTabulatorObserver.subscribe('#fieldauthorizations-grid:action:create', () => {
        openModalForCreate();
    });

    // Action : Ouvrir la modale pour Éditer
    globalTabulatorObserver.subscribe('#fieldauthorizations-grid:action:edit', (rule) => {
        openModalForEdit(rule);
    });

    // Action : Supprimer une règle
    globalTabulatorObserver.subscribe('#fieldauthorizations-grid:action:delete', async (rule) => {
        if (confirm(`⚠️ Voulez-vous supprimer la règle pour ${rule.resource}.${rule.field} ?`)) {
            try {
                const csrfToken = document.querySelector('meta[name="csrfToken"]')?.getAttribute('content');
                if (!csrfToken) throw new Error("Jeton CSRF manquant.");

                const response = await fetch(`/api/field-authorizations/delete/${rule.id}.json`, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-Token': csrfToken,
                        'Accept': 'application/json',
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                });

                if (response.ok) {
                    fieldAuthTable.deleteRow(rule.id);
                    FlashManager.success(`Règle pour <strong>${rule.resource}.${rule.field}</strong> supprimée avec succès.`);
                } else {
                    let serverMessage = `Erreur serveur (Code ${response.status})`;
                    try {
                        const errorPayload = await response.json();
                        if (errorPayload && errorPayload.message) serverMessage = errorPayload.message;
                    } catch (e) {}
                    throw new Error(serverMessage);
                }
            } catch (error) {
                console.error("Erreur suppression :", error);
                FlashManager.error(`<strong>Action refusée :</strong> ${error.message}`);
            }
        }
    });
}

// 3. Gestion de la Modale Bootstrap & Formulaire
const modalEl = document.getElementById('ruleModal');
const modal = modalEl ? new bootstrap.Modal(modalEl) : null;
const form = document.getElementById('ruleForm');

document.getElementById('btn-add-rule')?.addEventListener('click', () => {
    openModalForCreate();
});

document.getElementById('rule-resource')?.addEventListener('change', () => {
    triggerResourceChange();
});

form?.addEventListener('submit', async (e) => {
    e.preventDefault();
    const id = document.getElementById('rule-id').value;
    const url = id ? `/api/field-authorizations/edit/${id}.json` : '/api/field-authorizations/add.json';
    const payload = Object.fromEntries(new FormData(form).entries());

    try {
        const csrfToken = document.querySelector('meta[name="csrfToken"]')?.getAttribute('content');
        const response = await fetch(url, {
            method: 'POST',
            headers: { 
                'Content-Type': 'application/json',
                'X-CSRF-Token': csrfToken || '' 
            },
            body: JSON.stringify(payload)
        });

        if (response.ok) {
            modal?.hide();
            fieldAuthTable.replaceData(); // Rafraîchit les données de la grille
            FlashManager.success('Règle d\'autorisation enregistrée.');
        } else {
            const err = await response.json();
            FlashManager.error(err.message || 'Erreur lors de l\'enregistrement.');
        }
    } catch (err) {
        FlashManager.error('Erreur réseau.');
    }
});

// Helpers internes
function openModalForCreate() {
    form?.reset();
    document.getElementById('rule-id').value = '';
    triggerResourceChange();
    modal?.show();
}

function openModalForEdit(data) {
    document.getElementById('rule-id').value = data.id;
    document.getElementById('rule-role-id').value = data.role_id;
    document.getElementById('rule-resource').value = data.resource;
    triggerResourceChange();
    document.getElementById('rule-field').value = data.field;
    document.getElementById('rule-access-level').value = data.access_level;
    modal?.show();
}

function populateFormSelects(meta) {
    if (meta.roles) {
        document.getElementById('rule-role-id').innerHTML = Object.entries(meta.roles)
            .map(([id, name]) => `<option value="${id}">${name}</option>`).join('');
    }
    if (meta.resources) {
        document.getElementById('rule-resource').innerHTML = Object.keys(meta.resources)
            .map(res => `<option value="${res}">${res}</option>`).join('');
    }
    if (meta.accessLevels) {
        document.getElementById('rule-access-level').innerHTML = Object.entries(meta.accessLevels)
            .map(([code, label]) => `<option value="${code}">${label}</option>`).join('');
    }
}

function triggerResourceChange() {
    const res = document.getElementById('rule-resource')?.value;
    const columns = metadata.resources[res] || [];
    document.getElementById('rule-field').innerHTML = columns
        .map(col => `<option value="${col}">${col}</option>`).join('');
}
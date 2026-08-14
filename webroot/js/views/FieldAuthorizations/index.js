// ==============================================================================
// Fichier : webroot/js/views/FieldAuthorizations/index.js
// Rôle : Orchestrateur de la vue Index des Autorisations de Champs
// ==============================================================================

import { TabulatorFactory } from '../../core/Tabulator/TabulatorFactory.js';
import { globalTabulatorObserver } from '../../core/Tabulator/TabulatorObserver.js';
import { FlashManager } from '../../core/FlashManager.js';

const tableSelector = "#fieldauthorizations-grid";

// 1. Instanciation directe via la Factory
const fieldAuthTable = TabulatorFactory.createFieldAuthorizationsGrid(tableSelector);

// Métadonnées globales pour alimenter les listes déroulantes de la modale
let metadata = { roles: {}, resources: {}, accessLevels: {} };

// Chargement des métadonnées (Rôles, Tables, Champs, Accès)
(async () => {
    try {
        const response = await fetch('/api/field-authorizations/get-resources-and-fields.json', {
            method: 'GET',
            headers: {
                'Accept': 'application/json'
            }
        });

        if (response.ok) {
            metadata = await response.json();
            populateFormSelects(metadata);
        } else {
            console.error('Erreur HTTP métadonnées :', response.status);
        }
    } catch (e) {
        console.error('Erreur chargement métadonnées :', e);
    }
})();

// 2. Écouteurs d'événements via le canal Pub/Sub global
if (globalTabulatorObserver) {

    // Action : Ouvrir la modale pour Ajouter
    globalTabulatorObserver.subscribe(`${tableSelector}:action:create`, () => {
        openModalForCreate();
    });

    // Action : Ouvrir la modale pour Éditer
    globalTabulatorObserver.subscribe(`${tableSelector}:action:edit`, (rule) => {
        if (rule) openModalForEdit(rule);
    });

    // Action : Supprimer une règle
    globalTabulatorObserver.subscribe(`${tableSelector}:action:delete`, async (rule) => {
        if (!rule || !rule.id) return;

        if (confirm(`⚠️ Voulez-vous supprimer la règle pour ${rule.resource}.${rule.field} ?`)) {
            try {
                const csrfToken = getCsrfToken();
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
const modalInstance = modalEl ? bootstrap.Modal.getOrCreateInstance(modalEl) : null;
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
    const isEdit = Boolean(id);
    const url = isEdit ? `/api/field-authorizations/edit/${id}.json` : '/api/field-authorizations/add.json';
    const payload = Object.fromEntries(new FormData(form).entries());

    try {
        const csrfToken = getCsrfToken();
        const response = await fetch(url, {
            method: 'POST',
            headers: { 
                'Content-Type': 'application/json',
                'X-CSRF-Token': csrfToken,
                'Accept': 'application/json'
            },
            body: JSON.stringify(payload)
        });

        const result = await response.json().catch(() => ({}));

        if (response.ok && (result.success ?? true)) {
            modalInstance?.hide();
            if (fieldAuthTable && typeof fieldAuthTable.replaceData === 'function') {
                fieldAuthTable.replaceData(); // Rafraîchit la grille
            }
            FlashManager.success(isEdit ? 'Règle mise à jour avec succès.' : 'Nouvelle règle enregistrée.');
        } else {
            FlashManager.error(result.message || 'Erreur lors de l\'enregistrement de la règle.');
        }
    } catch (err) {
        console.error('Erreur soumission :', err);
        FlashManager.error('Erreur réseau ou serveur inaccessible.');
    }
});

// 4. Helpers internes

function openModalForCreate() {
    form?.reset();
    const idInput = document.getElementById('rule-id');
    if (idInput) idInput.value = '';
    
    const labelEl = document.getElementById('ruleModalLabel');
    if (labelEl) labelEl.textContent = 'Nouvelle Règle';

    triggerResourceChange();
    modalInstance?.show();
}

function openModalForEdit(data) {
    if (!data) return;

    const idInput = document.getElementById('rule-id');
    const roleInput = document.getElementById('rule-role-id');
    const resourceInput = document.getElementById('rule-resource');
    const fieldInput = document.getElementById('rule-field');
    const accessInput = document.getElementById('rule-access-level');
    const labelEl = document.getElementById('ruleModalLabel');

    if (idInput) idInput.value = data.id || '';
    if (roleInput) roleInput.value = data.role_id || '';
    if (resourceInput) resourceInput.value = data.resource || '';
    
    // 💡 Indispensable : génère d'abord les options <option> correspondant à la ressource
    triggerResourceChange();
    
    if (fieldInput) fieldInput.value = data.field || '';
    if (accessInput) accessInput.value = data.access_level || '';
    if (labelEl) labelEl.textContent = 'Modifier la Règle';

    modalInstance?.show();
}

function populateFormSelects(meta) {
    const roleSelect = document.getElementById('rule-role-id');
    const resourceSelect = document.getElementById('rule-resource');
    const accessSelect = document.getElementById('rule-access-level');

    if (roleSelect && meta.roles && Object.keys(meta.roles).length > 0) {
        roleSelect.innerHTML = Object.entries(meta.roles)
            .map(([id, name]) => `<option value="${id}">${name}</option>`).join('');
    }

    if (resourceSelect && meta.resources && Object.keys(meta.resources).length > 0) {
        resourceSelect.innerHTML = Object.keys(meta.resources)
            .map(res => `<option value="${res}">${res}</option>`).join('');
    }

    if (accessSelect && meta.accessLevels && Object.keys(meta.accessLevels).length > 0) {
        accessSelect.innerHTML = Object.entries(meta.accessLevels)
            .map(([code, label]) => `<option value="${code}">${label}</option>`).join('');
    }

    // Peuple immédiatement la liste des champs selon la première ressource active
    triggerResourceChange();
}

function triggerResourceChange() {
    const resourceSelect = document.getElementById('rule-resource');
    const fieldSelect = document.getElementById('rule-field');

    if (!resourceSelect || !fieldSelect) return;

    const selectedResource = resourceSelect.value;
    const columns = metadata.resources[selectedResource] || [];

    if (Array.isArray(columns) && columns.length > 0) {
        fieldSelect.innerHTML = columns
            .map(col => `<option value="${col}">${col}</option>`).join('');
    } else {
        fieldSelect.innerHTML = '<option value="">-- Aucun champ disponible --</option>';
    }
}

function getCsrfToken() {
    return document.querySelector('meta[name="csrfToken"]')?.getAttribute('content') 
        || document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') 
        || '';
}
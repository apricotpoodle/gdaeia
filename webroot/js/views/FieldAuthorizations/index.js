import { TabulatorBuilder } from '../../core/TabulatorBuilder.js';
import { TabulatorObserver } from '../../core/TabulatorObserver.js';

/**
 * Orchestrateur de vue pour l'administration des FieldAuthorizations.
 */
document.addEventListener('DOMContentLoaded', async () => {
    const tableSelector = '#field-authorizations-table';
    let metadata = { roles: {}, resources: {}, accessLevels: {} };

    // 1. Récupération des métadonnées pour hydrater les selects
    try {
        const response = await fetch('/api/field-authorizations/get-resources-and-fields.json');
        if (response.ok) {
            metadata = await response.json();
            populateFormSelects(metadata);
        }
    } catch (e) {
        console.error('Erreur lors du chargement des métadonnées :', e);
    }

    // 2. Construction de la grille Tabulator
    const grid = new TabulatorBuilder(tableSelector)
        .setController('field-authorizations')
        .setContinuousScroll(40)
        .setHeight('calc(100vh - 240px)')
        .setColumns([
            { title: 'ID', field: 'id', width: 70, sorter: 'number' },
            { 
                title: 'Rôle', 
                field: 'role.name', 
                headerFilter: 'select', 
                headerFilterParams: { values: metadata.roles } 
            },
            { 
                title: 'Ressource', 
                field: 'resource', 
                headerFilter: 'input' 
            },
            { 
                title: 'Champ', 
                field: 'field', 
                headerFilter: 'input' 
            },
            { 
                title: 'Niveau d\'Accès', 
                field: 'access_level', 
                headerFilter: 'select',
                headerFilterParams: { values: metadata.accessLevels },
                formatter: (cell) => {
                    const val = cell.getValue();
                    let badgeClass = 'bg-secondary';
                    if (val === 'EDIT') badgeClass = 'bg-success';
                    if (val === 'VIEW') badgeClass = 'bg-info text-dark';
                    if (val === 'NONE') badgeClass = 'bg-danger';
                    return `<span class="badge ${badgeClass}">${val}</span>`;
                }
            }
        ])
        .setWithActions(['edit', 'delete'])
        .build();

    // 3. Écoute des événements de ligne (Observer)
    const observer = new TabulatorObserver(tableSelector);

    observer.on('edit', (row) => {
        openModalForEdit(row.getData());
    });

    observer.on('delete', async (row) => {
        const data = row.getData();
        if (confirm(`Supprimer la règle pour ${data.resource}.${data.field} ?`)) {
            try {
                const res = await fetch(`/api/field-authorizations/delete/${data.id}.json`, {
                    method: 'POST',
                    headers: { 'X-CSRF-Token': getCsrfToken() }
                });
                if (res.ok) {
                    grid.replaceData();
                }
            } catch (err) {
                alert('Erreur lors de la suppression.');
            }
        }
    });

    // 4. Gestion de la Modale de Saisie
    const modalEl = document.getElementById('ruleModal');
    const modal = new bootstrap.Modal(modalEl);
    const form = document.getElementById('ruleForm');

    document.getElementById('btn-add-rule')?.addEventListener('click', () => {
        form.reset();
        document.getElementById('rule-id').value = '';
        document.getElementById('ruleModalLabel').textContent = 'Nouvelle Règle';
        triggerResourceChange();
        modal.show();
    });

    document.getElementById('rule-resource')?.addEventListener('change', () => {
        triggerResourceChange();
    });

    form?.addEventListener('submit', async (e) => {
        e.preventDefault();
        const id = document.getElementById('rule-id').value;
        const url = id 
            ? `/api/field-authorizations/edit/${id}.json` 
            : '/api/field-authorizations/add.json';

        const formData = new FormData(form);
        const payload = Object.fromEntries(formData.entries());

        try {
            const res = await fetch(url, {
                method: 'POST',
                headers: { 
                    'Content-Type': 'application/json',
                    'X-CSRF-Token': getCsrfToken() 
                },
                body: JSON.stringify(payload)
            });

            const result = await res.json();
            if (res.ok && result.success) {
                modal.hide();
                grid.replaceData();
            } else {
                alert(result.message || 'Erreur lors de l\'enregistrement.');
            }
        } catch (err) {
            alert('Erreur réseau.');
        }
    });

    function populateFormSelects(meta) {
        const roleSelect = document.getElementById('rule-role-id');
        const resourceSelect = document.getElementById('rule-resource');
        const accessSelect = document.getElementById('rule-access-level');

        if (roleSelect) {
            roleSelect.innerHTML = Object.entries(meta.roles)
                .map(([id, name]) => `<option value="${id}">${name}</option>`).join('');
        }

        if (resourceSelect) {
            resourceSelect.innerHTML = Object.keys(meta.resources)
                .map(res => `<option value="${res}">${res}</option>`).join('');
        }

        if (accessSelect) {
            accessSelect.innerHTML = Object.entries(meta.accessLevels)
                .map(([code, label]) => `<option value="${code}">${label}</option>`).join('');
        }
    }

    function triggerResourceChange() {
        const selectedResource = document.getElementById('rule-resource').value;
        const fieldSelect = document.getElementById('rule-field');
        const columns = metadata.resources[selectedResource] || [];

        if (fieldSelect) {
            fieldSelect.innerHTML = columns
                .map(col => `<option value="${col}">${col}</option>`).join('');
        }
    }

    function openModalForEdit(data) {
        document.getElementById('rule-id').value = data.id;
        document.getElementById('rule-role-id').value = data.role_id;
        document.getElementById('rule-resource').value = data.resource;
        triggerResourceChange();
        document.getElementById('rule-field').value = data.field;
        document.getElementById('rule-access-level').value = data.access_level;
        document.getElementById('ruleModalLabel').textContent = 'Modifier la Règle';
        modal.show();
    }

    function getCsrfToken() {
        const meta = document.querySelector('meta[name="csrf-token"]');
        return meta ? meta.getAttribute('content') : '';
    }
});
/**
 * @file webroot/js/menus/index.js
 * @description Initialisation et gestion des événements CRUD/Tree pour l'IHM Menus.
 */

import { TabulatorFactory } from '/js/core/Tabulator/TabulatorFactory.js';

document.addEventListener('DOMContentLoaded', () => {
    const gridId = '#menus-grid';
    const gridContainer = document.querySelector(gridId);
    if (!gridContainer) return;

    // 1. Initialisation de la grille via la Factory centralisée
    TabulatorFactory.createMenusGrid(gridId);

    // 2. Jeton CSRF (CakePHP 5)[cite: 1, 2]
    const csrfToken = document.querySelector('meta[name="csrfToken"]')?.getAttribute('content') || '';

    // Notification visuelle générique (Toast)
    const showNotification = (message, type = 'success') => {
        const alertBox = document.createElement('div');
        alertBox.className = `alert alert-${type} alert-dismissible fade show position-fixed top-0 end-0 m-3 z-3 shadow-sm`;
        alertBox.role = 'alert';
        alertBox.innerHTML = `
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        `;
        document.body.appendChild(alertBox);

        setTimeout(() => {
            alertBox.remove();
        }, 3000);
    };

    // 3. Traitement AJAX pour les actions de modification d'arbre (moveUp, moveDown)
    const moveMenuNode = async (action, id) => {
        const endpoint = action === 'moveUp' ? 'move-up' : 'move-down';

        try {
            const response = await fetch(`/menus/${endpoint}/${id}`, {
                method: 'POST',
                headers: {
                    'X-CSRF-Token': csrfToken,
                    'Accept': 'application/json',
                    'Content-Type': 'application/json'
                }
            });

            const result = await response.json();
            if (result.success) {
                showNotification(result.message || 'Le menu a été déplacé avec succès.', 'success');
                const table = Tabulator.findTable(gridId)[0];
                if (table) {
                    table.setData();
                }
            } else {
                showNotification(result.message || 'Impossible de déplacer ce menu.', 'warning');
            }
        } catch (error) {
            console.error('[AJAX Menu] Erreur réseau :', error);
            showNotification('Erreur réseau lors du déplacement.', 'danger');
        }
    };

    // 4. Traitement AJAX pour l'action Delete
    const deleteMenuNode = async (id) => {
        if (!confirm('Êtes-vous sûr de vouloir supprimer cette option de menu ?')) {
            return;
        }

        try {
            const response = await fetch(`/menus/delete/${id}`, {
                method: 'POST',
                headers: {
                    'X-CSRF-Token': csrfToken,
                    'Accept': 'application/json',
                    'Content-Type': 'application/json'
                }
            });

            const result = await response.json();
            if (result.success) {
                showNotification(result.message || 'Le menu a été supprimé avec succès.', 'success');
                const table = Tabulator.findTable(gridId)[0];
                if (table) {
                    table.setData();
                }
            } else {
                showNotification(result.message || 'Impossible de supprimer ce menu.', 'danger');
            }
        } catch (error) {
            console.error('[AJAX Delete] Erreur réseau :', error);
            showNotification('Erreur réseau lors de la suppression.', 'danger');
        }
    };

    // 5. Gestionnaire centralisé d'événements sur la grille (CRUD + Move)
    gridContainer.addEventListener('click', (e) => {
        const actionBtn = e.target.closest('[data-action]');
        if (!actionBtn) return;

        e.preventDefault();
        const action = actionBtn.dataset.action; // 'view', 'edit', 'delete', 'moveUp', 'moveDown'
        let recordId = null;

        // Extraction résiliente de l'ID depuis le conteneur .tabulator-row
        const rowEl = actionBtn.closest('.tabulator-row');
        if (rowEl) {
            const idCellEl = rowEl.querySelector('[tabulator-field="id"]');
            if (idCellEl) {
                recordId = idCellEl.textContent.trim();
            }
        }

        if (!recordId) {
            console.warn('[CRUD Menus] Impossible de résoudre l\'ID pour l\'action :', action);
            return;
        }

        // Routage unifié des actions CRUD et Tree
        switch (action) {
            case 'view':
                window.location.href = `/menus/view/${recordId}`;
                break;

            case 'edit':
                window.location.href = `/menus/edit/${recordId}`;
                break;

            case 'delete':
                deleteMenuNode(recordId);
                break;

            case 'moveUp':
            case 'moveDown':
                moveMenuNode(action, recordId);
                break;

            default:
                console.warn('[CRUD Menus] Action non gérée :', action);
                break;
        }
    });
});

/**
 * @file MenuManager.js
 * @description Gestionnaire d'infrastructure responsable du chargement asynchrone
 * et du rendu de la barre de navigation supérieure Bootstrap 5.
 * @module core/Navigation/MenuManager
 */

/**
 * Type définissant la structure des métadonnées de l'opérateur connecté.
 * @typedef {Object} UserData
 * @property {string} email - L'adresse courriel de l'opérateur.
 * @property {string} role_name - Le libellé du rôle de l'opérateur.
 * @property {boolean} issuperuser - Détermine si l'utilisateur possède les droits absolus.
 * @property {boolean} is_impersonated - Détermine si la session est actuellement usurpée.
 */

/**
 * @class MenuManager
 * @description Classe d'infrastructure gérant l'état visuel et l'arborescence de la navigation haute.
 */
export class MenuManager {

    /**
     * Point d'entrée de l'infrastructure de navigation.
     * Déclenche la requête Ajax vers l'API CakePHP de manière asynchrone.
     * @returns {void}
     */
    static bootstrap() {
        const menuContainer = document.getElementById('main-navbar-links');
        const userContainer = document.getElementById('main-navbar-user-zone');
        if (!menuContainer) return;

        fetch('/api/menus.json', {
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
            .then(response => {
                if (!response.ok) throw new Error("Impossible de charger le menu.");
                return response.json();
            })
            .then(payload => {
                if (payload && payload.menus) {
                    this.renderMenus(menuContainer, payload.menus);
                }
                if (payload && payload.userData && userContainer) {
                    this.renderUserZone(userContainer, payload.userData);
                }
            })
            .catch(error => console.error("⚠️ MenuManager Error:", error));
    }

    /**
     * Injecte les options de menus de navigation (Flanc gauche).
     * @private
     * @param {HTMLElement} container - Le conteneur HTML récepteur.
     * @param {Array<Object>} menuTree - L'arbre hiérarchique des entités menus.
     * @returns {void}
     */
    static renderMenus(container, menuTree) {
        let html = '';
        menuTree.forEach(node => {
            const hasChildren = node.children && node.children.length > 0;
            if (!hasChildren) {
                if (!node.url || node.url === '#') return;
                html += `
                    <li class="nav-item">
                        <a class="nav-link text-light" href="/${node.url}">${node.name}</a>
                    </li>
                `;
            } else {
                let childrenHtml = '';
                node.children.forEach(child => {
                    if (child.dividor_before) childrenHtml += `<li><hr class="dropdown-divider"></li>`;
                    const childUrl = (child.url && child.url !== '#') ? `/${child.url}` : '#';
                    childrenHtml += `<li><a class="dropdown-item" href="${childUrl}">${child.name}</a></li>`;
                });

                if (childrenHtml === '') return;

                html += `
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle text-light" href="#" id="dropdown-${node.id}" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                            ${node.name}
                        </a>
                        <ul class="dropdown-menu shadow-sm" aria-labelledby="dropdown-${node.id}">
                            ${childrenHtml}
                        </ul>
                    </li>
                `;
            }
        });
        container.innerHTML = html;
    }

    /**
     * Injecte les informations de l'opérateur et boutons d'actions (Flanc droit).
     * @private
     * @param {HTMLElement} container - Le conteneur HTML récepteur.
     * @param {UserData} user - Les métadonnées nettoyées issues de l'API.
     * @returns {void}
     */
    static renderUserZone(container, user) {
        let html = '';

        if (user.is_impersonated === true) {
            html += `
                <a href="/users/revert-identity" class="btn btn-sm btn-warning shadow-sm me-3 fw-bold" title="Quitter la session">
                    <i class="fas fa-user-ninja me-1"></i> Quitter Session
                </a>
            `;
        }

        const displayRole = user.role_name || user.role || 'Utilisateur';

        // 💡 CORRECTION ERGONOMIQUE : text-white pour l'email et text-white-50 pour le rôle !
        html += `
            <div class="d-flex flex-column text-end me-3 lh-sm">
                <span class="small fw-bold text-truncate text-white" style="max-width: 200px;">${user.email}</span>
                <span class="text-white-50" style="font-size: 0.75rem;">
                    <i class="fas fa-user-tag me-1 text-info"></i>${displayRole}
                </span>
            </div>
        `;

        html += `
            <a href="/users/logout" class="btn btn-sm btn-outline-danger shadow-sm fw-bold" title="Se déconnecter">
                <i class="fas fa-power-off"></i>
            </a>
        `;

        container.innerHTML = html;
    }
}

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => MenuManager.bootstrap());
} else {
    MenuManager.bootstrap();
}

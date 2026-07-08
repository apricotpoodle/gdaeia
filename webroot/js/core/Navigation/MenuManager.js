/**
 * @class MenuManager
 * @description Gestionnaire d'infrastructure responsable du chargement asynchrone
 * et du rendu de la barre de navigation supérieure Bootstrap 5.
 * Norme : Module ES6 (ADR 0030).
 */
export class MenuManager {

    /**
     * Point d'entrée de l'infrastructure de navigation.
     * Déclenche la requête Ajax vers l'API CakePHP.
     * @static
     */
    static bootstrap() {
        const targetContainer = document.getElementById('main-navbar-links');
        if (!targetContainer) return;

        // Requête asynchrone vers l'API des menus (ADR 0027)
        fetch('/api/menus.json', {
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
            .then(response => {
                if (!response.ok) throw new Error("Impossible de charger le flux des menus.");
                return response.json();
            })
            .then(payload => {
                // 💡 Alignement : Le contrôleur renvoie un objet contenant la clé 'menus'
                if (payload && payload.menus) {
                    this.render(targetContainer, payload.menus);
                }
            })
            .catch(error => console.error("⚠️ MenuManager Error:", error));
    }

    /**
     * Injecte dynamiquement les nœuds autorisés de l'arbre dans le DOM.
     *
     * @private
     * @param {HTMLElement} container - Le conteneur `<ul>` cible.
     * @param {Array} menuTree - L'arbre des menus issu du finder threaded.
     */
    static render(container, menuTree) {
        let html = '';

        menuTree.forEach(node => {
            const hasChildren = node.children && node.children.length > 0;

            if (!hasChildren) {
                // Élément racine simple sans sous-menu (Ex: Saisie Demande)
                // 💡 SÉCURITÉ : Si l'URL vaut '#' alors qu'il n'a pas d'enfants, c'est un dossier vide pour ce rôle, on l'ignore !
                if (!node.url || node.url === '#') return;

                html += `
                    <li class="nav-item">
                        <a class="nav-link" href="/${node.url}">${node.name}</a>
                    </li>
                `;
            } else {
                // Élément racine parent (Niveau 0 - Ex: Administration)
                let childrenHtml = '';

                // On pré-compile les enfants
                node.children.forEach(child => {
                    if (child.dividor_before) {
                        childrenHtml += `<li><hr class="dropdown-divider"></li>`;
                    }
                    const childUrl = (child.url && child.url !== '#') ? `/${child.url}` : '#';
                    const disabledClass = child.disabled ? 'disabled text-muted opacity-50' : '';

                    childrenHtml += `
                        <li>
                            <a class="dropdown-item ${disabledClass}" href="${childUrl}">${child.name}</a>
                        </li>
                    `;
                });

                // 💡 SÉCURITÉ : Si le filtrage serveur a purgé tous les enfants de ce dossier pour ce rôle,
                // on n'affiche tout simplement pas le dossier parent vide à l'écran.
                if (childrenHtml === '') return;

                html += `
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="dropdown-${node.id}" role="button" data-bs-toggle="dropdown" aria-expanded="false">
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
}

// Auto-initialisation sécurisée dès que le script module est chargé
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => MenuManager.bootstrap());
} else {
    MenuManager.bootstrap();
}

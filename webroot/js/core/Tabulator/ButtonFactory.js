/**
 * @class ButtonFactory
 * Implémentation du patron "Factory" pour les éléments d'interface.
 * Utilise le ButtonBuilder pour construire des composants Bootstrap 5.3 standardisés.
 */
class ButtonFactory {

    /**
     * Retourne le bouton d'action de ligne demandé.
     * @param {string} action Le nom de l'action
     * @returns {string} Le code HTML sécurisé du bouton
     */
    static getCellButton(action) {
        const builder = new ButtonBuilder()
            .setSize('sm')
            .addClass('btn-action')
            .addClass('me-1')
            .setAction(action);

        switch (action) {
            case 'view':
                return builder.setColor('info').addClass('text-white').setTitle('Voir').setIcon('fas fa-eye').build();
            case 'edit':
                return builder.setColor('primary').setTitle('Modifier').setIcon('fas fa-edit').build();
            case 'delete':
                return builder.setColor('danger').setTitle('Supprimer').setIcon('fas fa-trash').build();
            case 'impersonate':
                return builder.setColor('secondary').setTitle("Incarner l'utilisateur").setIcon('fas fa-user-secret').build();
            case 'viewpdf':
                return builder.setColor('dark').setTitle('Voir le PDF').setIcon('fas fa-file-pdf').build();
            default:
                return '';
        }
    }

    /**
     * Génère l'en-tête de la colonne d'actions avec le menu déroulant.
     * @returns {string} Le code HTML de l'en-tête
     */
    static getHeaderDropdown() {
        // Le bouton principal du dropdown utilise aussi le Builder !
        const mainBtn = new ButtonBuilder()
            .setSize('sm')
            .setColor('danger')
            .addClass('dropdown-toggle')
            .setTitle('Menu des actions')
            .setIcon('fas fa-cog')
            .build()
            .replace('type="button"', 'type="button" data-bs-toggle="dropdown" aria-expanded="false"');
        // Petite exception assumée pour injecter les data-attributes Bootstrap natifs

        return `
            <div class="d-flex align-items-center justify-content-center">
                <div class="dropdown me-2">
                    ${mainBtn}
                    <ul class="dropdown-menu shadow">
                        <li>
                            <button class="dropdown-item text-success action-create fw-bold" type="button">
                                <i class="fas fa-plus-circle me-2"></i> Créer
                            </button>
                        </li>
                        <li><hr class="dropdown-divider"></li>
                        <li>
                            <button class="dropdown-item text-warning action-reset fw-bold" type="button">
                                <i class="fas fa-undo me-2"></i> Réinitialiser l'affichage
                            </button>
                        </li>
                    </ul>
                </div>
                <span class="fw-bold">Actions</span>
            </div>
        `;
    }
}

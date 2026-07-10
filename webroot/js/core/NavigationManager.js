/**
 * @file NavigationManager.js
 * @description Centralise les comportements globaux de navigation au clavier (Accessibilité/UX).
 * @module core/NavigationManager
 */
export class NavigationManager {
    /**
     * Active l'écoute de la touche Escape pour rediriger l'utilisateur.
     * * @param {string} fallbackUrl L'URL de redirection absolue (ex: '/users/index').
     */
    static registerEscapeRedirect(fallbackUrl) {
        document.addEventListener('keydown', (e) => {
            // Sécurité : On ne redirige pas si l'utilisateur est en train de taper
            // dans un champ textuel multiligne (textarea) pour lui éviter de perdre ses données.
            if (e.key === 'Escape' && e.target.tagName !== 'TEXTAREA') {
                window.location.href = fallbackUrl;
            }
        });
    }
}

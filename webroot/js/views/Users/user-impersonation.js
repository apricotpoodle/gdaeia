/**
 * Confirme l’usurpation d’une ligne puis déclenche son action Tabulator.
 *
 * @param {Object|null|undefined} user Données de la ligne ciblée.
 * @param {Function} findActionButton Recherche le bouton d’usurpation de la ligne.
 * @param {Function} [confirmAction=window.confirm] Affiche la confirmation utilisateur.
 * @returns {boolean} Vrai si le bouton d’usurpation a été déclenché.
 */
export function confirmAndTriggerUserImpersonation(
    user,
    findActionButton,
    confirmAction = message => window.confirm(message),
) {
    if (user?.grid_rights?.actions?.impersonate !== true) {
        return false;
    }

    const displayName = user.display_name || user.email || String(user.id);
    if (!confirmAction(`Voulez-vous vraiment vous connecter sous l’identité de ${displayName} ?`)) {
        return false;
    }

    if (typeof findActionButton !== 'function') {
        return false;
    }

    const actionButton = findActionButton();
    if (!actionButton || typeof actionButton.click !== 'function') {
        return false;
    }

    actionButton.click();

    return true;
}

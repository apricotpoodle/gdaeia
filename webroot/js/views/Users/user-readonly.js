/**
 * Détermine si une ligne utilisateur peut recevoir une mutation d'interface.
 * L'autorisation serveur reste la décision finale.
 *
 * @param {Object|null|undefined} user Données de la ligne utilisateur.
 * @returns {boolean} Vrai si la ligne est gérable.
 */
export function isManageableUser(user) {
    return user?.is_manageable !== false;
}

/**
 * Applique le marquage visuel d'une ligne utilisateur non gérable.
 *
 * @param {Object} row Ligne Tabulator.
 * @returns {void}
 */
export function formatUserReadOnlyRow(row) {
    const data = row.getData();
    if (isManageableUser(data)) {
        return;
    }

    const element = row.getElement();
    element.classList.add('user-row-readonly');
    element.title = data.read_only_message
        || 'Utilisateur visible mais non gérable : consultation uniquement.';
}

/**
 * Met à jour les résumés d’associations affichés dans l’onglet Département.
 *
 * @param {Object} data Données du département retournées par l’API.
 * @param {Document} [documentRef=document] Document utilisé pour la mise à jour.
 * @returns {void}
 */
export function renderDepartmentSummary(data, documentRef = document) {
    const managers = Array.isArray(data?.managers) ? data.managers : [];
    const cgrCodes = Array.isArray(data?.cgr_codes) ? data.cgr_codes : [];
    const managerNames = managers
        .map((manager) => manager.display_name || manager.full_name || '')
        .filter(Boolean);
    const codes = cgrCodes
        .map((cgrCode) => cgrCode.code || '')
        .filter(Boolean);

    const managersElement = documentRef.querySelector('#department-managers-summary');
    const cgrsElement = documentRef.querySelector('#department-cgrs-summary');
    if (managersElement) {
        managersElement.textContent = managerNames.length > 0
            ? managerNames.join(', ')
            : 'Aucun responsable';
    }
    if (cgrsElement) {
        cgrsElement.textContent = codes.length > 0 ? codes.join(', ') : 'Aucun CGR';
    }
}

/**
 * Recharge les associations du département courant puis met à jour le résumé.
 *
 * @param {number|string} departmentId Identifiant du département.
 * @param {{fetchImpl?: Function, documentRef?: Document}} [options] Dépendances injectables.
 * @returns {Promise<void>} Promesse résolue après la mise à jour.
 */
export async function refreshDepartmentSummary(
    departmentId,
    { fetchImpl = fetch, documentRef = document } = {},
) {
    const response = await fetchImpl(`/api/departments/${departmentId}/view.json`, {
        headers: { Accept: 'application/json' },
    });
    const payload = await response.json().catch(() => null);
    if (!response.ok || payload?.success !== true) {
        throw new Error(payload?.message || 'Impossible de rafraîchir le département.');
    }

    renderDepartmentSummary(payload.data || {}, documentRef);
}

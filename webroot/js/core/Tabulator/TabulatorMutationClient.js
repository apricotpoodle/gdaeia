/**
 * Adaptateur HTTP pour les mutations déclenchées par une grille Tabulator.
 * @class TabulatorMutationClient
 */
export class TabulatorMutationClient {
    /**
     * @param {Object} [options={}] Options de transport.
     * @param {Function} [options.fetchImpl=fetch] Implémentation fetch injectable.
     * @param {Function} [options.csrfTokenProvider] Fournisseur du jeton CSRF.
     */
    constructor({ fetchImpl = fetch, csrfTokenProvider = TabulatorMutationClient.defaultCsrfToken } = {}) {
        this.fetchImpl = fetchImpl;
        this.csrfTokenProvider = csrfTokenProvider;
    }

    /**
     * Exécute une mutation et valide le contrat JSON applicatif.
     * @param {Object} request Requête HTTP à exécuter.
     * @returns {Promise<Object>} Charge utile JSON de l'API.
     */
    async send({ url, method = 'POST', body, headers = {} }) {
        const csrfToken = this.csrfTokenProvider();
        if (!csrfToken) {
            throw new Error('Jeton CSRF manquant.');
        }

        const response = await this.fetchImpl(url, {
            method,
            headers: {
                Accept: 'application/json',
                'X-Requested-With': 'XMLHttpRequest',
                'X-CSRF-Token': csrfToken,
                ...headers,
            },
            body,
        });
        const payload = await response.json().catch(() => null);

        if (!response.ok || !payload?.success) {
            throw new Error(payload?.message || `Erreur serveur (${response.status}).`);
        }

        return payload;
    }

    /** @returns {string} Jeton CSRF présent dans la page. */
    static defaultCsrfToken() {
        return document.querySelector('meta[name="csrfToken"]')?.getAttribute('content') || '';
    }
}

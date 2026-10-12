export const SESSION_EXPIRED_MESSAGE = 'Votre session a expiré. Veuillez vous reconnecter.';
const SESSION_MESSAGE_KEY = 'gdaetf2.session-expired-message';

/** Entoure fetch pour centraliser le traitement des réponses 401. */
export function createSessionAwareFetch(fetchImplementation, browser) {
    let redirecting = false;

    return async function sessionAwareFetch(input, init) {
        const response = await fetchImplementation(input, init);
        const currentPath = browser.location?.pathname ?? '';

        if (response.status === 401 && !redirecting && currentPath !== '/users/login') {
            redirecting = true;
            try {
                browser.sessionStorage?.setItem(SESSION_MESSAGE_KEY, SESSION_EXPIRED_MESSAGE);
            } catch (_) {
                // Le parcours reste fonctionnel si le stockage du navigateur est indisponible.
            }

            const loginUrl = new URL('/users/login', browser.location.origin);
            loginUrl.searchParams.set('session_expired', '1');
            browser.location.assign(loginUrl.pathname + loginUrl.search);
        }

        return response;
    };
}

export function installSessionAwareFetch(browser = window) {
    if (browser.fetch?.__gdaetf2SessionAware) return;

    const wrappedFetch = createSessionAwareFetch(browser.fetch.bind(browser), browser);
    wrappedFetch.__gdaetf2SessionAware = true;
    browser.fetch = wrappedFetch;
}

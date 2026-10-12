import assert from 'node:assert/strict';
import { test } from 'node:test';
import { createSessionAwareFetch } from '../../webroot/js/core/SessionManager.js';

function browser() {
    return {
        location: {
            origin: 'https://example.test',
            pathname: '/applicationforms/index',
            assigned: null,
            assign(url) { this.assigned = url; },
        },
        sessionStorage: {
            values: new Map(),
            setItem(key, value) { this.values.set(key, value); },
        },
    };
}

test('une réponse 401 redirige vers la connexion sans rejouer la requête', async () => {
    const page = browser();
    let calls = 0;
    const fetch = createSessionAwareFetch(async () => {
        calls += 1;
        return new Response('', { status: 401 });
    }, page);

    await fetch('/api/applicationforms/1/validation/start.json', { method: 'POST' });
    await fetch('/api/applicationforms/1/validation/start.json', { method: 'POST' });

    assert.equal(calls, 2);
    assert.equal(page.location.assigned, '/users/login?session_expired=1');
});

test('les erreurs réseau, timeout et 5xx ne déclenchent pas la reconnexion', async () => {
    for (const implementation of [
        async () => { throw new TypeError('Failed to fetch'); },
        async () => { throw new DOMException('Timeout', 'TimeoutError'); },
        async () => new Response('', { status: 503 }),
    ]) {
        const page = browser();
        const fetch = createSessionAwareFetch(implementation, page);
        try { await fetch('/api/applicationforms.json'); } catch (_) { /* erreur réseau attendue */ }
        assert.equal(page.location.assigned, null);
    }
});

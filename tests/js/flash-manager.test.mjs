import assert from 'node:assert/strict';
import { readFileSync } from 'node:fs';
import { test } from 'node:test';

const source = readFileSync(new URL('../../webroot/js/core/FlashManager.js', import.meta.url), 'utf8');
let pageNumber = 0;

class Element {
    constructor(tagName = 'div') {
        this.tagName = tagName;
        this.children = [];
        this.attributes = new Map();
        this.events = new Map();
        this.className = '';
        this.innerHTML = '';
        this.parent = null;
        this.classList = {
            add: name => {
                this.className = [...new Set([...this.className.split(/\s+/).filter(Boolean), name])].join(' ');
            }
        };
    }

    setAttribute(name, value) { this.attributes.set(name, value); }
    getAttribute(name) { return this.attributes.get(name) ?? null; }

    append(...elements) {
        for (const element of elements) {
            element.parent = this;
            this.children.push(element);
        }
    }

    appendChild(element) { this.append(element); }

    prepend(...elements) {
        for (const element of elements) element.parent = this;
        this.children.unshift(...elements);
    }

    querySelector(selector) {
        const className = selector.slice(1);
        return this.children.find(element => element.className.split(/\s+/).includes(className)) ?? null;
    }

    querySelectorAll(selector) {
        assert.equal(selector, '.flash-toast[data-flash-source="server"]');
        return this.children.filter(element =>
            element.className.split(/\s+/).includes('flash-toast') &&
            element.getAttribute('data-flash-source') === 'server'
        );
    }

    addEventListener(name, handler) {
        const handlers = this.events.get(name) ?? [];
        handlers.push(handler);
        this.events.set(name, handlers);
    }

    click() {
        for (const handler of this.events.get('click') ?? []) handler();
    }

    remove() {
        this.parent.children = this.parent.children.filter(element => element !== this);
        this.parent = null;
    }
}

class Storage {
    data = new Map();

    getItem(key) { return this.data.get(key) ?? null; }
    setItem(key, value) { this.data.set(key, value); }
    removeItem(key) { this.data.delete(key); }
    pending() { return JSON.parse(this.getItem('gdaetf2.flash-toasts') || '[]'); }
}

class Clock {
    constructor(now) {
        this.now = now;
        this.nextId = 1;
        this.timers = new Map();
    }

    setTimeout(callback, delay) {
        const id = this.nextId++;
        this.timers.set(id, { at: this.now + delay, callback });
        return id;
    }

    clearTimeout(id) { this.timers.delete(id); }

    advanceTo(time) {
        while (true) {
            const next = [...this.timers].sort((a, b) => a[1].at - b[1].at)[0];
            if (!next || next[1].at > time) break;
            this.timers.delete(next[0]);
            this.now = next[1].at;
            next[1].callback();
        }
        this.now = time;
    }
}

function serverToast(message, type = 'danger') {
    const toast = new Element();
    toast.className = `toast flash-toast flash-toast--${type} show`;
    toast.setAttribute('data-flash-source', 'server');
    toast.setAttribute('data-flash-type', type);
    const body = new Element();
    body.className = 'flash-toast__body';
    body.innerHTML = message;
    const close = new Element('button');
    close.className = 'flash-toast__close';
    toast.append(body, close);
    return toast;
}

async function loadPage(storage, now, messages = [], pathname = '/roles/add') {
    const clock = new Clock(now);
    const body = new Element('body');
    const container = new Element();
    container.id = 'flash-container';
    container.className = 'flash-container';
    container.append(...messages.map(message => serverToast(message)));
    body.appendChild(container);

    const previous = {
        document: globalThis.document,
        window: globalThis.window,
        sessionStorage: globalThis.sessionStorage,
        setTimeout: globalThis.setTimeout,
        clearTimeout: globalThis.clearTimeout,
        dateNow: Date.now
    };
    globalThis.document = {
        body,
        getElementById: id => id === 'flash-container' ? container : null,
        createElement: tagName => new Element(tagName)
    };
    globalThis.window = { location: { pathname } };
    globalThis.sessionStorage = storage;
    globalThis.setTimeout = (callback, delay) => clock.setTimeout(callback, delay);
    globalThis.clearTimeout = id => clock.clearTimeout(id);
    Date.now = () => clock.now;

    const url = `data:text/javascript;base64,${Buffer.from(source).toString('base64')}#page-${++pageNumber}`;
    const { FlashManager } = await import(url);
    FlashManager.init();

    return {
        container,
        manager: FlashManager,
        clock,
        restore() {
            globalThis.document = previous.document;
            globalThis.window = previous.window;
            globalThis.sessionStorage = previous.sessionStorage;
            globalThis.setTimeout = previous.setTimeout;
            globalThis.clearTimeout = previous.clearTimeout;
            Date.now = previous.dateNow;
        }
    };
}

function messages(page) {
    return page.container.children.map(toast => toast.querySelector('.flash-toast__body').innerHTML);
}

test('les Flash serveur restent empilés et expirent cinq secondes après leur apparition', async () => {
    const storage = new Storage();
    let page;
    try {
        page = await loadPage(storage, 1000, ['Première erreur']);
        page.restore();
        page = await loadPage(storage, 2000, ['Deuxième erreur']);
        page.restore();
        page = await loadPage(storage, 3000, ['Troisième erreur']);

        assert.deepEqual(messages(page), ['Première erreur', 'Deuxième erreur', 'Troisième erreur']);
        assert.deepEqual(storage.pending().map(flash => flash.expiresAt), [6000, 7000, 8000]);

        page.clock.advanceTo(5999);
        assert.equal(page.container.children.length, 3);
        page.clock.advanceTo(6150);
        assert.deepEqual(messages(page), ['Deuxième erreur', 'Troisième erreur']);
        page.clock.advanceTo(7150);
        assert.deepEqual(messages(page), ['Troisième erreur']);
        page.clock.advanceTo(8150);
        assert.deepEqual(messages(page), []);
        assert.deepEqual(storage.pending(), []);
    } finally {
        page?.restore();
    }
});

test('une fermeture manuelle, un changement de page et une expiration empêchent la restauration', async () => {
    const storage = new Storage();
    let page;
    try {
        page = await loadPage(storage, 1000, ['Erreur à fermer']);
        page.container.children[0].querySelector('.flash-toast__close').click();
        assert.deepEqual(storage.pending(), []);
        page.clock.advanceTo(1150);
        assert.deepEqual(messages(page), []);

        page.restore();
        page = await loadPage(storage, 2000);
        assert.deepEqual(messages(page), []);

        page.restore();
        page = await loadPage(storage, 3000, ['Erreur expirée']);
        page.restore();
        page = await loadPage(storage, 8000);
        assert.deepEqual(messages(page), []);

        page.restore();
        page = await loadPage(storage, 9000, ['Erreur de formulaire']);
        page.restore();
        page = await loadPage(storage, 10000, [], '/menus');
        assert.deepEqual(messages(page), []);
    } finally {
        page?.restore();
    }
});

test('les notifications JavaScript partagent le rendu et acceptent une durée explicite', async () => {
    const storage = new Storage();
    let page;
    try {
        page = await loadPage(storage, 0);
        page.manager.error('Première erreur');
        page.clock.advanceTo(1000);
        page.manager.warning('Deuxième erreur');
        page.manager.show('<strong>Durée personnalisée</strong>', 'primary', 8000);
        page.manager.info('Message permanent', 0);

        assert.deepEqual(messages(page), [
            'Première erreur',
            'Deuxième erreur',
            '<strong>Durée personnalisée</strong>',
            'Message permanent'
        ]);
        assert.equal(page.container.children[0].getAttribute('data-flash-type'), 'danger');
        assert.equal(page.container.children[1].getAttribute('data-flash-type'), 'warning');
        assert.deepEqual(storage.pending(), []);

        page.clock.advanceTo(5150);
        assert.deepEqual(messages(page), ['Deuxième erreur', '<strong>Durée personnalisée</strong>', 'Message permanent']);
        page.clock.advanceTo(6150);
        assert.deepEqual(messages(page), ['<strong>Durée personnalisée</strong>', 'Message permanent']);
        page.clock.advanceTo(9150);
        assert.deepEqual(messages(page), ['Message permanent']);
        page.container.children[0].querySelector('.flash-toast__close').click();
        page.clock.advanceTo(9300);
        assert.deepEqual(messages(page), []);
    } finally {
        page?.restore();
    }
});

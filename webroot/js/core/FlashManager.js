const DEFAULT_DURATION_MS = 5000;
const DISMISS_ANIMATION_MS = 150;
const STORAGE_KEY = 'gdaetf2.flash-toasts';
const TYPES = new Set(['primary', 'secondary', 'success', 'danger', 'warning', 'info', 'light', 'dark']);

/** Gère les toasts rendus par CakePHP et les notifications JavaScript. */
export class FlashManager {
    static #initialized = false;
    static #pendingServer = [];

    static #getContainer() {
        let container = document.getElementById('flash-container');
        if (!container) {
            container = document.createElement('div');
            container.id = 'flash-container';
            container.className = 'flash-container';
            document.body.appendChild(container);
        }

        return container;
    }

    static #variant(type) {
        const variant = type === 'error' ? 'danger' : type;

        return TYPES.has(variant) ? variant : 'primary';
    }

    static #createToast(message, type) {
        const variant = this.#variant(type);
        const toast = document.createElement('div');
        toast.className = `toast flash-toast flash-toast--${variant} show`;
        toast.setAttribute('data-flash-type', variant);
        toast.setAttribute('role', variant === 'danger' || variant === 'warning' ? 'alert' : 'status');
        toast.setAttribute('aria-live', variant === 'danger' || variant === 'warning' ? 'assertive' : 'polite');
        toast.setAttribute('aria-atomic', 'true');

        const body = document.createElement('div');
        body.className = 'flash-toast__body';
        body.innerHTML = message;

        const close = document.createElement('button');
        close.type = 'button';
        close.className = 'flash-toast__close';
        close.setAttribute('aria-label', 'Fermer');
        close.textContent = '×';

        toast.append(body, close);

        return toast;
    }

    static #readPending(pathname, now) {
        try {
            const stored = JSON.parse(sessionStorage.getItem(STORAGE_KEY) || '[]');
            if (!Array.isArray(stored)) return [];

            return stored.filter(flash =>
                flash &&
                typeof flash.id === 'string' &&
                typeof flash.bodyHtml === 'string' &&
                TYPES.has(flash.type) &&
                flash.path === pathname &&
                Number.isFinite(flash.expiresAt) &&
                flash.expiresAt > now
            );
        } catch {
            return [];
        }
    }

    static #savePending() {
        try {
            if (this.#pendingServer.length === 0) {
                sessionStorage.removeItem(STORAGE_KEY);
            } else {
                sessionStorage.setItem(STORAGE_KEY, JSON.stringify(this.#pendingServer));
            }
        } catch {
            // Le toast reste visible si le stockage de session est indisponible.
        }
    }

    static #activate(toast, duration, id = null) {
        let dismissed = false;
        let timerId;

        const dismiss = () => {
            if (dismissed) return;
            dismissed = true;
            if (timerId !== undefined) clearTimeout(timerId);

            if (id !== null) {
                this.#pendingServer = this.#pendingServer.filter(flash => flash.id !== id);
                this.#savePending();
            }

            toast.classList.add('flash-toast--hiding');
            setTimeout(() => toast.remove(), DISMISS_ANIMATION_MS);
        };

        toast.querySelector('.flash-toast__close')?.addEventListener('click', dismiss);
        if (duration > 0) timerId = setTimeout(dismiss, duration);
    }

    /** Initialise les Flash CakePHP et rétablit ceux qui n'ont pas expiré. */
    static init() {
        if (this.#initialized) return;
        this.#initialized = true;

        const container = this.#getContainer();
        const pathname = window.location.pathname;
        const now = Date.now();
        const current = [...container.querySelectorAll('.flash-toast[data-flash-source="server"]')]
            .map((toast, index) => ({
                toast,
                flash: {
                    id: `${now}-${index}-${Math.random().toString(36).slice(2)}`,
                    bodyHtml: toast.querySelector('.flash-toast__body')?.innerHTML || '',
                    type: this.#variant(toast.getAttribute('data-flash-type')),
                    path: pathname,
                    expiresAt: now + DEFAULT_DURATION_MS
                }
            }));
        const restored = this.#readPending(pathname, now)
            .map(flash => ({ toast: this.#createToast(flash.bodyHtml, flash.type), flash }));

        container.prepend(...restored.map(({ toast }) => toast));
        const toasts = [...restored, ...current];
        this.#pendingServer = toasts.map(({ flash }) => flash);
        this.#savePending();

        for (const { toast, flash } of toasts) {
            this.#activate(toast, Math.max(1, flash.expiresAt - Date.now()), flash.id);
        }
    }

    /**
     * Affiche un toast dynamique. Le message peut contenir du HTML de confiance.
     * @param {string} message Contenu du message.
     * @param {string} type Variante visuelle.
     * @param {number} duration Durée en millisecondes (0 = permanent).
     */
    static show(message, type = 'success', duration = DEFAULT_DURATION_MS) {
        const toast = this.#createToast(message, type);
        this.#getContainer().appendChild(toast);
        this.#activate(toast, duration);
    }

    static success(message, duration = DEFAULT_DURATION_MS) { this.show(message, 'success', duration); }
    static error(message, duration = DEFAULT_DURATION_MS) { this.show(message, 'danger', duration); }
    static warning(message, duration = DEFAULT_DURATION_MS) { this.show(message, 'warning', duration); }
    static info(message, duration = DEFAULT_DURATION_MS) { this.show(message, 'info', duration); }
}

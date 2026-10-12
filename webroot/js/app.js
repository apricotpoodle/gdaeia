import { FlashManager } from './core/FlashManager.js';
import { installSessionAwareFetch } from './core/SessionManager.js';

installSessionAwareFetch();

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => FlashManager.init());
} else {
    FlashManager.init();
}

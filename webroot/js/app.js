import { FlashManager } from './core/FlashManager.js';

if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', () => FlashManager.init());
} else {
    FlashManager.init();
}

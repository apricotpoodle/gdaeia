import { FlashManager } from '../../core/FlashManager.js';
import { initializeCircuitRulesPage } from './circuit-rules-page.js';

initializeCircuitRulesPage().catch((error) => FlashManager.error(error.message));

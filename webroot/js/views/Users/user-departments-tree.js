/**
 * @file webroot/js/views/Users/user-departments-tree.js
 * @description Point d'entrée ES6 pour la vue Utilisateurs.
 */
import { TreeSelectAdapter } from '../../core/TreeSelectAdapter.js';

document.addEventListener('DOMContentLoaded', () => {
    TreeSelectAdapter.autoInit();
});

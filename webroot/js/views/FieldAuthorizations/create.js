import { initializeFieldAuthorizationForm } from './form.js';

document.addEventListener('DOMContentLoaded', () => {
    initializeFieldAuthorizationForm('/api/field-authorizations/add.json');
});

import { initializeFieldAuthorizationForm } from './form.js';

document.addEventListener('DOMContentLoaded', () => {
    const form = document.getElementById('field-authorization-form');
    const id = form?.dataset.id;
    if (id) initializeFieldAuthorizationForm(`/api/field-authorizations/edit/${id}.json`);
});

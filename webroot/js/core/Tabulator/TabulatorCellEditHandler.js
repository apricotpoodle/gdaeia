import { FlashManager } from '../FlashManager.js';
import { TabulatorMutationClient } from './TabulatorMutationClient.js';

/**
 * Orchestrateur générique d'une mutation inline suivie d'une mise à jour de ligne.
 * @class TabulatorCellEditHandler
 */
export class TabulatorCellEditHandler {
    /**
     * @param {Object} options Configuration de la stratégie.
     * @param {string[]} [options.editableFields] Champs autorisés.
     * @param {Function} options.requestFactory Construit la requête depuis la cellule.
     * @param {Function} [options.responseMapper] Extrait la ligne depuis la réponse.
     * @param {TabulatorMutationClient} [options.client] Adaptateur HTTP.
     * @param {Function} [options.onSuccess] Callback de succès.
     * @param {Function} [options.onError] Callback d'erreur.
     */
    constructor({
        editableFields = [],
        requestFactory,
        responseMapper = payload => payload.data,
        client = new TabulatorMutationClient(),
        onSuccess = payload => FlashManager.success(payload.message || 'Modification enregistrée.'),
        onError = error => FlashManager.error(error instanceof Error ? error.message : 'Modification impossible.'),
    }) {
        if (typeof requestFactory !== 'function') {
            throw new TypeError('Le constructeur de requête Tabulator est obligatoire.');
        }

        this.editableFields = new Set(editableFields);
        this.requestFactory = requestFactory;
        this.responseMapper = responseMapper;
        this.client = client;
        this.onSuccess = onSuccess;
        this.onError = onError;
        this.pending = new WeakMap();
    }

    /**
     * Persiste la cellule et applique la ligne canonique retournée par l'API.
     * @param {Object} cell Composant cellule Tabulator.
     * @returns {Promise<void>}
     */
    async handle(cell) {
        if (this.editableFields.size > 0 && !this.editableFields.has(cell.getField())) return;

        let request;
        try {
            request = this.requestFactory(cell);
        } catch (error) {
            cell.restoreOldValue();
            this.onError(error, cell);
            return;
        }

        const previous = this.pending.get(cell) ?? Promise.resolve();
        const operation = previous.catch(() => {}).then(async () => {
            try {
                const payload = await this.client.send(request);
                const rowData = this.responseMapper(payload, cell);
                if (rowData && typeof rowData === 'object') {
                    await cell.getRow().update(rowData);
                }
                this.onSuccess(payload, cell);
            } catch (error) {
                cell.restoreOldValue();
                this.onError(error, cell);
            }
        });
        this.pending.set(cell, operation);

        try {
            await operation;
        } finally {
            if (this.pending.get(cell) === operation) {
                this.pending.delete(cell);
            }
        }
    }
}

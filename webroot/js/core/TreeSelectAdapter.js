/**
 * @file webroot/js/core/TreeSelectAdapter.js
 * @description Adaptateur métier pour les sélections hiérarchiques multiples CakePHP.
 * Délègue le cycle de vie de TreeselectJS à TreeselectWrapper.
 */

import TreeselectWrapper from './TreeSelectJS/TreeselectWrapper.js';

export class TreeSelectAdapter {
    /**
     * @param {HTMLElement} container Élément DOM conteneur (.treeselect-target)
     */
    constructor(container) {
        this.container = container;

        this.fieldName = container.dataset.fieldName;
        this.foreignKey = container.dataset.foreignKey || 'department_id';
        this.hiddenContainerId = container.dataset.hiddenContainer;
        this.dataScriptId = container.dataset.dataScript;
        this.apiUrl = container.dataset.apiUrl;
        this.isReadOnly = container.dataset.readonly === 'true';
        this.placeholder = container.dataset.placeholder || 'Sélectionner...';

        this.hiddenInputsContainer = document.getElementById(this.hiddenContainerId);
        this.options = [];
        this.initialValue = [];
        this.previousValues = [];
        this.isUpdating = false;
    }

    async init() {
        if (!this.container || !this.hiddenInputsContainer || !this.fieldName) {
            console.warn('[TreeSelectAdapter] Initialisation annulée : Attributs data-* manquants.', this.container);
            return;
        }

        await this.loadData();

        try {
            this.previousValues = Array.isArray(this.initialValue)
                ? this.initialValue.map(Number).filter(v => !isNaN(v))
                : (this.initialValue ? [Number(this.initialValue)] : []);

            this.treeselect = new TreeselectWrapper({
                parentContainer: this.container,
                value: this.previousValues,
                options: this.options,
                isSingleSelect: false,
                showTags: true,
                clearable: !this.isReadOnly,
                searchable: true,
                placeholder: this.placeholder,
                disabled: this.isReadOnly,
                showCount: true,
                openLevel: 1,
                grouped: true,
                isGroupedValue: false,
                isIndependentNodes: true,
                onChange: (value) => this.handleSelection(value)
            });

            this.syncHiddenInputs(this.previousValues);

        } catch (err) {
            console.error('[TreeSelectAdapter] Échec du montage de TreeselectJS :', err);
        }
    }

    async loadData() {
        if (this.dataScriptId) {
            const dataScript = document.getElementById(this.dataScriptId);
            if (dataScript && dataScript.textContent) {
                try {
                    const localData = JSON.parse(dataScript.textContent);
                    this.options = localData.options || [];
                    this.initialValue = localData.value || [];
                } catch (e) {
                    console.warn('[TreeSelectAdapter] Erreur lecture JSON local :', e);
                }
            }
        }

        if (this.options.length === 0 && this.apiUrl) {
            try {
                const response = await fetch(this.apiUrl, {
                    headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
                });
                if (response.ok) {
                    const apiData = await response.json();
                    this.options = apiData.departments || apiData.options || [];
                }
            } catch (error) {
                console.error('[TreeSelectAdapter] Erreur API :', error);
            }
        }
    }

    handleSelection(value) {
        if (this.isUpdating) return;

        const rawDetail = Array.isArray(value) ? value : [value];
        let currentValues = rawDetail.map(Number).filter(v => !isNaN(v));

        const added = currentValues.filter(v => !this.previousValues.includes(v));
        const removed = this.previousValues.filter(v => !currentValues.includes(v));

        let finalSet = new Set(currentValues);

        added.forEach(id => {
            const node = this.findNode(id, this.options);
            if (node) {
                const descendants = this.getDescendantIds(node);
                descendants.forEach(dId => finalSet.add(dId));
            }
        });

        removed.forEach(id => {
            const node = this.findNode(id, this.options);
            if (node) {
                const descendants = this.getDescendantIds(node);
                descendants.forEach(dId => finalSet.delete(dId));
            }
        });

        const newSelection = Array.from(finalSet);

        if (newSelection.length !== currentValues.length) {
            this.isUpdating = true;
            this.treeselect.value = newSelection;
            this.isUpdating = false;
        }

        this.previousValues = newSelection;
        this.syncHiddenInputs(newSelection);
    }

    syncHiddenInputs(selectedValues) {
        this.hiddenInputsContainer.innerHTML = '';
        let index = 0;

        selectedValues.forEach((id) => {
            if (id !== null && id !== undefined && id !== '') {
                const input = document.createElement('input');
                input.type = 'hidden';
                input.name = `${this.fieldName}[${index}][${this.foreignKey}]`;
                input.value = String(id);
                this.hiddenInputsContainer.appendChild(input);
                index++;
            }
        });

        if (index === 0) {
            const emptyInput = document.createElement('input');
            emptyInput.type = 'hidden';
            emptyInput.name = this.fieldName;
            emptyInput.value = '';
            this.hiddenInputsContainer.appendChild(emptyInput);
        }
    }

    findNode(id, nodes) {
        const numId = Number(id);
        for (const node of nodes) {
            if (Number(node.value) === numId) return node;
            if (node.children && node.children.length > 0) {
                const found = this.findNode(numId, node.children);
                if (found) return found;
            }
        }
        return null;
    }

    getDescendantIds(node) {
        let ids = [];
        if (node.children && Array.isArray(node.children)) {
            node.children.forEach(child => {
                if (child.value !== undefined && child.value !== null) {
                    ids.push(Number(child.value));
                }
                ids = ids.concat(this.getDescendantIds(child));
            });
        }
        return ids;
    }

    static autoInit() {
        document.querySelectorAll('.treeselect-target').forEach(container => {
            const adapter = new TreeSelectAdapter(container);
            adapter.init();
        });
    }
}

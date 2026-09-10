/**
 * @file webroot/js/core/TreeSelectAdapter.js
 * @description Adaptateur générique et agnostique pour TreeselectJS.
 * Compatible avec les relations ORM CakePHP (HasMany / BelongsToMany).
 */

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

        if (this.container.dataset.treeselectInit === "true") {
            return;
        }
        this.container.dataset.treeselectInit = "true";

        await this.loadData();

        try {
            const TreeselectClass = await this.loadTreeselectClass();

            this.previousValues = Array.isArray(this.initialValue)
                ? this.initialValue.map(Number).filter(v => !isNaN(v))
                : (this.initialValue ? [Number(this.initialValue)] : []);

            this.treeselect = new TreeselectClass({
                parentHtmlContainer: this.container,
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
                isIndependentNodes: true
            });

            this.syncHiddenInputs(this.previousValues);
            this.treeselect.srcElement.addEventListener('input', (e) => this.handleInput(e));

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

    handleInput(e) {
        if (this.isUpdating) return;

        const rawDetail = Array.isArray(e.detail) ? e.detail : [e.detail];
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
            this.treeselect.updateValue(newSelection);
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

    loadTreeselectClass() {
        return new Promise((resolve, reject) => {
            let ClassObj = window.Treeselect || (window.default ? window.default.Treeselect : null);
            if (ClassObj) return resolve(ClassObj);

            const script = document.createElement('script');
            script.src = '/js/vendor/treeselect/treeselectjs.umd.js';
            script.onload = () => {
                ClassObj = window.Treeselect || (window.default ? window.default.Treeselect : null);
                if (ClassObj) resolve(ClassObj);
                else reject(new Error('Impossible d\'instancier Treeselect.'));
            };
            script.onerror = () => reject(new Error('Échec du chargement de treeselectjs.umd.js'));
            document.head.appendChild(script);
        });
    }

    static autoInit() {
        document.querySelectorAll('.treeselect-target').forEach(container => {
            const adapter = new TreeSelectAdapter(container);
            adapter.init();
        });
    }
}

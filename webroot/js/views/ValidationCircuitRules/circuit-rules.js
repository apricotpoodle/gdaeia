import { TabulatorMutationClient } from '../../core/Tabulator/TabulatorMutationClient.js';
import TreeselectWrapper from '../../core/TreeSelectJS/TreeselectWrapper.js';

/** Sélection explicite limitée aux nœuds présents dans l’arbre autorisé. */
export class DepartmentSelection {
    /** @param {Array<Object>} tree Arbre des départements fourni par l’API. */
    constructor(tree) {
        this.nodes = new Map();
        this.values = [];
        const visit = (nodes) => nodes.forEach((node) => {
            this.nodes.set(Number(node.id), node);
            visit(node.children || []);
        });
        visit(tree);
    }

    /** @param {Array<number|string>} values Identifiants explicites, sans expansion. @returns {number[]} */
    replace(values) {
        this.values = [...new Set(values.map(Number).filter((id) => this.nodes.has(id)))];
        return [...this.values];
    }

    /** @param {Array<number|string>} values Sélection après le geste utilisateur. @returns {number[]} */
    change(values) {
        const current = [...new Set(values.map(Number).filter((id) => this.nodes.has(id)))];
        const selected = new Set(current);
        const descendants = (id, apply) => {
            for (const child of this.nodes.get(id)?.children || []) {
                apply(Number(child.id));
                descendants(Number(child.id), apply);
            }
        };
        current.filter((id) => !this.values.includes(id)).forEach((id) => {
            descendants(id, (childId) => selected.add(childId));
        });
        this.values.filter((id) => !current.includes(id)).forEach((id) => {
            descendants(id, (childId) => selected.delete(childId));
        });
        return this.replace([...selected]);
    }
}

/**
 * Monte le composant hiérarchique commun avec une sélection exhaustive et locale.
 * @param {HTMLElement} container Conteneur du sélecteur.
 * @param {Array<Object>} tree Arbre autorisé par l’API.
 * @param {Function} onChange Notification d’un geste utilisateur.
 * @param {typeof TreeselectWrapper} [Wrapper=TreeselectWrapper] Façade du composant.
 * @returns {Object} Sélection explicite et commandes de changement de mode.
 */
export function createDepartmentSelector(container, tree, onChange, Wrapper = TreeselectWrapper) {
    const selection = new DepartmentSelection(tree);
    const initialValue = [];
    const options = (nodes) => nodes.map((node) => ({
        name: node.name,
        value: Number(node.id),
        children: options(node.children || []),
    }));
    let locked = false;
    let updating = false;
    let widget;
    const synchronize = () => {
        // Le chargement de l’asset commun est asynchrone : conserver la valeur
        // initiale partagée jusqu’à ce que le widget soit réellement monté.
        initialValue.splice(0, initialValue.length, ...selection.values);
        if (widget.nativeInstance) {
            updating = true;
            try { widget.value = [...selection.values]; } finally { updating = false; }
        }
    };
    widget = new Wrapper({
        parentContainer: container,
        value: initialValue,
        options: options(tree),
        isSingleSelect: false,
        grouped: true,
        isGroupedValue: false,
        isIndependentNodes: true,
        alwaysOpen: true,
        staticList: true,
        placeholder: 'Rechercher un département…',
        onChange: (values) => {
            if (updating) return;
            if (!locked) selection.change(Array.isArray(values) ? values : []);
            synchronize();
            if (!locked) onChange([...selection.values]);
        },
    });
    return {
        get values() { return [...selection.values]; },
        replace(values) { selection.replace(values); synchronize(); },
        setLocked(value) {
            locked = value;
            // setDisabled() du wrapper remonte Treeselect et remplace son nœud
            // d’événements. inert conserve le composant et sa sélection exacte.
            container.inert = value;
            container.setAttribute?.('aria-disabled', String(value));
        },
    };
}

/** Client du domaine ; le transport commun conserve le contrat JSON et le CSRF. */
export class ValidationCircuitRulesClient {
    /** @param {Object} [options={}] Dépendances du transport HTTP commun. */
    constructor(options = {}) {
        this.fetchImpl = options.fetchImpl || fetch;
        this.transport = new TabulatorMutationClient(options);
        this.baseUrl = '/api/validation-circuit-rules';
    }

    /** @param {string} path Chemin du domaine. @returns {Promise<Object|Array>} Données de lecture. */
    async read(path) {
        const response = await this.fetchImpl(`${this.baseUrl}/${path}.json`, {
            headers: { Accept: 'application/json', 'X-Requested-With': 'XMLHttpRequest' },
        });
        const payload = await response.json().catch(() => null);
        if (!response.ok || !payload || payload.success === false) {
            throw new Error(payload?.message || 'Impossible de charger le paramétrage des circuits.');
        }
        return payload.data;
    }

    /** @returns {Promise<Array<Object>>} Arbre autorisé. */
    departments() { return this.read('departments-tree'); }

    /** @returns {Promise<Object>} Contrats et rôles disponibles. */
    options() { return this.read('options'); }

    /** @param {string} path Chemin API. @param {Object} [data] Corps JSON. @returns {Promise<Object>} */
    mutate(path, data) {
        return this.transport.send({
            url: `${this.baseUrl}/${path}.json`,
            headers: { 'Content-Type': 'application/json' },
            body: data === undefined ? undefined : JSON.stringify(data),
        });
    }

    /** @param {number|string} id Identifiant référentiel. @returns {number} Identifiant positif. */
    positiveId(id) {
        const value = Number(id);
        if (!Number.isInteger(value) || value < 1) {
            throw new Error('Sélectionnez une valeur dans chaque liste.');
        }
        return value;
    }

    /** @param {Array<number|string>} ids Départements explicites. @returns {number[]} */
    departmentIds(ids) {
        if (!Array.isArray(ids) || ids.length === 0) {
            throw new Error('Sélectionnez au moins un département.');
        }
        return [...new Set(ids.map((id) => this.positiveId(id)))];
    }

    /** @param {Object} data Valeurs du formulaire. @returns {Object} Corps validé. */
    ruleData(data) {
        return {
            department_ids: this.departmentIds(data.department_ids),
            contracttype_id: this.positiveId(data.contracttype_id),
            final_role_id: this.positiveId(data.final_role_id),
        };
    }

    /** @param {Object} data Valeurs explicites. @param {number|null} [id=null] Règle éditée. @returns {Promise<Object>} */
    async saveRule(data, id = null) {
        const values = this.ruleData(data);
        if (id === null) {
            return this.mutate('add', values);
        }
        if (values.department_ids.length !== 1) {
            throw new Error('Une modification ne peut porter que sur un département.');
        }
        return this.mutate(String(this.positiveId(id)), {
            department_id: values.department_ids[0],
            contracttype_id: values.contracttype_id,
            final_role_id: values.final_role_id,
        });
    }

    /** @param {Object} data Sélection explicite. @returns {Promise<Array<Object>>} Parcours et problèmes. */
    async preview(data) {
        const payload = await this.mutate('preview', this.ruleData(data));
        return payload.data;
    }

    /** @param {number} id Règle unique à retirer. @returns {Promise<Object>} */
    async deleteRule(id) { return this.mutate(`${this.positiveId(id)}/delete`); }

    /** @param {number[]} departmentIds Départements explicites. @param {number} roleId Rôle gestionnaire. @returns {Promise<Object>} */
    async assignManagers(departmentIds, roleId) {
        return this.mutate('managers/assign', {
            department_ids: this.departmentIds(departmentIds),
            role_id: this.positiveId(roleId),
        });
    }

    /** @param {number} id Habilitation unique à retirer. @returns {Promise<Object>} */
    async revokeManager(id) { return this.mutate(`managers/${this.positiveId(id)}/revoke`); }
}

/**
 * Remplit une liste de choix avec du texte, sans interpréter les libellés en HTML.
 * @param {HTMLSelectElement} select Liste à remplir.
 * @param {Array<{id:number,name:string}>} options Référentiel API.
 * @param {string} placeholder Invite de sélection.
 * @returns {void}
 */
export function fillSelectOptions(select, options, placeholder) {
    const create = (value, name) => {
        const option = select.ownerDocument.createElement('option');
        option.value = value;
        option.textContent = name;
        return option;
    };
    select.replaceChildren(create('', placeholder), ...options.map((option) => create(String(option.id), option.name)));
}

/**
 * Présente chaque département, ses rôles retenus et ses problèmes de configuration.
 * @param {HTMLElement} container Conteneur de l’aperçu.
 * @param {Array<Object>} departments Résultat de prévisualisation serveur.
 * @returns {void}
 */
export function renderCircuitPreview(container, departments) {
    const create = (tag, text = '', className = '') => {
        const node = container.ownerDocument.createElement(tag);
        node.textContent = text;
        node.className = className;
        return node;
    };
    container.replaceChildren();
    for (const department of departments) {
        const section = create('section', '', 'border rounded p-3 mb-2');
        section.append(create('h5', department.department_name, 'h6'));
        const steps = create('ul');
        for (const sequence of department.sequences) {
            steps.append(create('li', `Séquence ${sequence.sequence} — ${sequence.role_name}`));
        }
        section.append(steps);
        if (department.sequences.length === 0) {
            section.append(create('p', 'Aucune étape retenue.', 'text-muted'));
        }
        if (department.issues.length > 0) {
            const problems = create('ul', '', 'alert alert-warning mb-0');
            department.issues.forEach((issue) => problems.append(create('li', issue)));
            section.append(problems);
        } else {
            section.append(create('p', 'Circuit utilisable.', 'text-success mb-0'));
        }
        container.append(section);
    }
}

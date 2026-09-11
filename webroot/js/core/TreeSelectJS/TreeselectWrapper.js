/**
 * TreeselectWrapper - Un adaptateur et une façade GoF (Wrapper) pour TreeselectJS.
 * 
 * Ce composant encapsule l'instanciation et la configuration de la bibliothèque tierce
 * TreeselectJS, gérant automatiquement la liaison bidirectionnelle avec les formulaires
 * CakePHP et appliquant nos standards d'infrastructure (performance, thématisation, sécurité).
 * 
 * Conforme à l'ADR 0030 (ES6 Modules) et à l'ADR 0049 (Intégration TreeselectJS).
 * 
 * @module core/Components/TreeselectWrapper
 */

export default class TreeselectWrapper {
    /** @type {Promise<typeof window.Treeselect>|null} Chargement partagé de l'asset UMD local. */
    static #treeselectLoader = null;

    /**
     * @typedef {Object} WrapperConfig
     * @property {HTMLElement|string} parentContainer - Conteneur HTML ou sélecteur CSS d'ancrage pour le TreeselectJS.
     * @property {HTMLInputElement|string} [hiddenInput] - Input masqué ou sélecteur CSS pour la liaison avec CakePHP.
     * @property {Array<Object>} options - Données de l'arborescence [{name: String, value: String|Number, children: []}].
     * @property {Array<string|number>|string|number} [value] - Valeur(s) sélectionnée(s) à l'initialisation.
     * @property {boolean} [isSingleSelect=true] - Mode de sélection unique (dropdown classique).
     * @property {boolean} [isBoostedRendering=true] - Optimisation de rendu via IntersectionObserver pour les grands volumes.
     * @property {boolean} [clearable=true] - Afficher l'icône de nettoyage.
     * @property {boolean} [searchable=true] - Activer la recherche textuelle à la volée.
     * @property {boolean} [appendToBody=false] - Injecter le panneau de liste dans le body (utile si contraintes overflow).
     * @property {number} [openLevel=1] - Niveau d'ouverture initial de l'arborescence.
     * @property {boolean} [showCount=true] - Afficher le nombre d'éléments enfants à côté du libellé de groupe.
     * @property {boolean} [disabled=false] - Désactiver complètement l'interaction (ACL / Read-only).
     * @property {boolean} [grouped] - Regrouper visuellement les nœuds hiérarchiques.
     * @property {boolean} [isGroupedValue] - Traiter les groupes comme une valeur sélectionnable.
     * @property {boolean} [isIndependentNodes] - Laisser TreeselectJS gérer les nœuds indépendamment.
     * @property {function} [onChange] - Callback déclenché à chaque modification de valeur : (value) => void.
     */

    /** @type {Treeselect} Instance sous-jacente de la bibliothèque TreeselectJS */
    #instance = null;

    /** @type {HTMLElement} Conteneur DOM d'ancrage */
    #container = null;

    /** @type {HTMLInputElement|null} Élément d'entrée masqué pour la soumission de formulaire */
    #input = null;

    /** @type {WrapperConfig} Configuration fusionnée */
    #config = null;

    /** @type {boolean} Indique que cette instance est propriétaire du conteneur. */
    #ownsContainer = false;

    /**
     * Constructeur du wrapper.
     * 
     * @param {WrapperConfig} config - Configuration utilisateur.
     */
    constructor(config) {
        this.#validateAndResolveDOM(config);
        this.#config = this.#applyDefaults(config);

        if (this.#container.dataset.treeselectInit === 'true') {
            throw new Error('TreeselectWrapper : ce conteneur possède déjà une instance TreeselectJS.');
        }

        this.#container.dataset.treeselectInit = 'true';
        this.#ownsContainer = true;
        this.#init().catch((error) => {
            console.error('TreeselectWrapper : échec de l\'initialisation.', error);
        });
    }

    /**
     * Valide et résout les sélecteurs ou éléments du DOM.
     * 
     * @param {WrapperConfig} config 
     * @throws {Error} Si le conteneur principal est introuvable.
     */
    #validateAndResolveDOM(config) {
        // Résolution du conteneur parent
        if (typeof config.parentContainer === 'string') {
            this.#container = document.querySelector(config.parentContainer);
        } else {
            this.#container = config.parentContainer;
        }

        if (!this.#container) {
            throw new Error(`TreeselectWrapper : Le conteneur principal "${config.parentContainer}" est introuvable dans le DOM.`);
        }

        // Résolution de l'input masqué de liaison
        if (config.hiddenInput) {
            if (typeof config.hiddenInput === 'string') {
                this.#input = document.querySelector(config.hiddenInput);
            } else {
                this.#input = config.hiddenInput;
            }
            
            if (!this.#input) {
                console.warn(`TreeselectWrapper : L'élément input masqué "${config.hiddenInput}" n'a pas été trouvé.`);
            }
        }
    }

    /**
     * Applique les règles métiers et paramètres par défaut de l'ADR 0049.
     * 
     * @param {WrapperConfig} config 
     * @returns {WrapperConfig}
     */
    #applyDefaults(config) {
        const isSingleSelect = config.isSingleSelect !== false; // Sélection unique par défaut

        return {
            isSingleSelect: isSingleSelect,
            isBoostedRendering: true, // Toujours activé par défaut (Standard de performance ADR 0049)
            clearable: true,
            searchable: true,
            appendToBody: false,     // Confinement DOM par défaut
            openLevel: 1,
            showCount: true,
            disabled: false,
            // Mode dropdown propre pour le mode single select (pas de tags encombrants)
            showTags: !isSingleSelect, 
            ...config
        };
    }

    /**
     * Initialise et instancie la classe TreeselectJS.
     */
    async #init() {
        try {
            const Treeselect = await TreeselectWrapper.#loadTreeselect();

            // L'instance peut avoir été détruite pendant le chargement asynchrone.
            if (!this.#ownsContainer || !this.#container) {
                return;
            }

        // Préparation de la valeur initiale
        let initialValue = this.#config.value;
        
        // Si aucune valeur n'est fournie mais qu'un input masqué existe, on récupère sa valeur
        if ((initialValue === undefined || initialValue === null || initialValue === '') && this.#input) {
            const rawValue = this.#input.value;
            if (rawValue !== '') {
                initialValue = this.#config.isSingleSelect 
                    ? (isNaN(rawValue) ? rawValue : Number(rawValue))
                    : rawValue.split(',').map(v => isNaN(v) ? v.trim() : Number(v));
            }
        }

        // Nettoyage de la valeur pour correspondre aux attentes du composant
        if (initialValue === undefined || initialValue === null) {
            initialValue = this.#config.isSingleSelect ? '' : [];
        } else if (this.#config.isSingleSelect) {
            // Treeselect s'attend à une valeur simple (non-tableau) en mode isSingleSelect
            initialValue = Array.isArray(initialValue) ? initialValue[0] : initialValue;
        } else {
            // Mode multi-select : toujours un tableau
            initialValue = Array.isArray(initialValue) ? initialValue : [initialValue];
        }

        // Instanciation de TreeselectJS avec la Façade de configuration
        this.#instance = new Treeselect({
            parentHtmlContainer: this.#container,
            value: initialValue,
            options: this.#config.options,
            disabled: this.#config.disabled,
            isSingleSelect: this.#config.isSingleSelect,
            isBoostedRendering: this.#config.isBoostedRendering,
            clearable: this.#config.clearable,
            searchable: this.#config.searchable,
            appendToBody: this.#config.appendToBody,
            openLevel: this.#config.openLevel,
            showCount: this.#config.showCount,
            showTags: this.#config.showTags,
            grouped: this.#config.grouped,
            isGroupedValue: this.#config.isGroupedValue,
            isIndependentNodes: this.#config.isIndependentNodes,
            placeholder: this.#config.placeholder || "Sélectionnez une option..."
        });

        // Liaison de l'événement de changement (Adaptateur DOM)
        this.#instance.srcElement.addEventListener('input', (event) => {
            const newValue = event.detail; // Array d'ID ou ID simple
            this.#syncValueToInput(newValue);
            
            // Notification du callback utilisateur si présent
            if (typeof this.#config.onChange === 'function') {
                this.#config.onChange(newValue);
            }
        });

        // La valeur initiale reflète un état déjà connu : elle ne doit pas être
        // interprétée par les scripts dépendants comme une action utilisateur.
        this.#syncValueToInput(initialValue, false);
        } catch (error) {
            if (this.#ownsContainer) {
                delete this.#container.dataset.treeselectInit;
                this.#ownsContainer = false;
            }

            throw error;
        }
    }

    /**
     * Charge une seule fois la distribution UMD locale de TreeselectJS.
     * L'asset est volontairement chargé comme un script classique : cette
     * distribution expose sa classe via window.Treeselect, contrairement à
     * une distribution ES module qui n'est pas fournie dans le dépôt.
     *
     * @returns {Promise<typeof window.Treeselect>}
     */
    static #loadTreeselect() {
        if (window.Treeselect) {
            return Promise.resolve(window.Treeselect);
        }

        if (TreeselectWrapper.#treeselectLoader) {
            return TreeselectWrapper.#treeselectLoader;
        }

        TreeselectWrapper.#treeselectLoader = new Promise((resolve, reject) => {
            const script = document.createElement('script');
            script.src = '/js/vendor/treeselect/treeselectjs.umd.js';
            script.async = true;
            script.onload = () => {
                if (window.Treeselect) {
                    resolve(window.Treeselect);
                    return;
                }

                TreeselectWrapper.#treeselectLoader = null;
                reject(new Error('TreeselectWrapper : la bibliothèque TreeselectJS est indisponible après chargement.'));
            };
            script.onerror = () => {
                TreeselectWrapper.#treeselectLoader = null;
                reject(new Error('TreeselectWrapper : échec du chargement de treeselectjs.umd.js.'));
            };
            document.head.appendChild(script);
        });

        return TreeselectWrapper.#treeselectLoader;
    }

    /**
     * Traduit et écrit les valeurs internes de TreeselectJS dans l'input attendu par CakePHP.
     * 
     * @param {Array<string|number>|string|number} value
     * @param {boolean} [notify=true] Indique si les scripts dépendants doivent être notifiés.
     */
    #syncValueToInput(value, notify = true) {
        if (!this.#input) return;

        if (value === undefined || value === null) {
            this.#input.value = '';
        } else if (Array.isArray(value)) {
            // Mode multi-select : sérialisation sous forme de chaîne à virgules
            this.#input.value = value.join(',');
        } else {
            // Mode single-select
            this.#input.value = value.toString();
        }

        if (notify) {
            // Déclencher manuellement l'événement change sur l'input masqué
            // pour notifier les frameworks réactifs ou d'autres scripts de vue.
            this.#input.dispatchEvent(new Event('change', { bubbles: true }));
        }
    }

    /**
     * Récupère l'instance brute de la bibliothèque TreeselectJS.
     * Utile pour accéder directement aux méthodes natives (ex: destroy, focus).
     * 
     * @returns {Treeselect}
     */
    get nativeInstance() {
        return this.#instance;
    }

    /**
     * Récupère la ou les valeurs actuellement sélectionnées.
     * 
     * @returns {Array<string|number>|string|number}
     */
    get value() {
        return this.#instance.value;
    }

    /**
     * Met à jour par programme la sélection du Treeselect.
     * 
     * @param {Array<string|number>|string|number} newValue 
     */
    set value(newValue) {
        let cleanValue = newValue;
        if (this.#config.isSingleSelect) {
            cleanValue = Array.isArray(newValue) ? newValue[0] : newValue;
            this.#instance.updateValue(cleanValue);
        } else {
            cleanValue = Array.isArray(newValue) ? newValue : [newValue];
            this.#instance.updateValue(cleanValue);
        }
        this.#syncValueToInput(cleanValue);
    }

    /**
     * Modifie l'état d'activation du composant (ACL au niveau du champ).
     * 
     * @param {boolean} disabledStatus 
     */
    setDisabled(disabledStatus) {
        this.#instance.disabled = !!disabledStatus;
        this.#instance.mount(); // Re-render requis par TreeselectJS pour appliquer ce changement
    }

    /**
     * Détruit proprement le composant et nettoie le DOM.
     */
    destroy() {
        if (this.#instance) {
            this.#instance.destroy();
        }
        if (this.#ownsContainer && this.#container) {
            delete this.#container.dataset.treeselectInit;
        }
        this.#container = null;
        this.#input = null;
        this.#instance = null;
        this.#ownsContainer = false;
    }
}

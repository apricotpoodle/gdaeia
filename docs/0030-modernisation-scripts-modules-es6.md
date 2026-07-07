# ADR 0030 : Modernisation de l'infrastructure front-end via les modules ES6

Date : 07 Juillet 2026
Statut : Accepté
Dépendance : EcmaScript 6 (ES6+)

## Contexte
Jusqu'à présent, l'infrastructure JavaScript de l'application (cœur technique et orchestrateurs de vues) reposait sur l'empilement de scripts classiques au sein du scope global (`window`). Cette approche par injection de scripts dans le DOM (via `templates/layout/default.php`) pose trois limites majeures à mesure que l'application grandit :
1. **Risque de collision** : Pollution globale de l'objet `window` augmentant les risques d'écrasement accidentel de variables ou de fabriques.
2. **Fragilité de l'ordonnancement** : L'obligation de charger manuellement les dépendances dans un ordre strict sous peine de lever des exceptions `ReferenceError: X is not defined`.
3. **Flou sémantique pour l'IA** : L'absence de liaisons explicites (`import`/`export`) complique l'analyse d'impact et la génération de code par Gemini ou NotebookLM, qui doivent deviner les dépendances partagées en mémoire globale.

## Décision
Nous décidons de migrer l'intégralité de l'architecture JavaScript du front-end vers les **Modules ES6 natifs** (`type="module"`).

1. **Isolation et Encapsulation** : Retrait complet de l'exposition des classes sur l'objet global `window`. Chaque composant (Builder, Observer, Factory) devient un module hermétique.
2. **Contrat d'Importation Explicite** : Utilisation systématique des directives `export` pour exposer les cœurs techniques et `import` au sein des orchestrateurs de vues pour consommer les dépendances.
3. **Gouvernance du Chargement** : Le layout global se déleste du chargement des scripts *Core*. Les vues métiers chargent leur orchestrateur unique via le Helper CakePHP en spécifiant le type module : `$this->Html->script('views/X/index.js', ['type' => 'module', 'block' => 'scriptBottom'])`.

## Justification
Cette transition applique le principe de **Haute Cohésion et Faible Couplage** (SOLID). Le navigateur prend en charge la résolution de l'arbre des dépendances de manière native, garantissant des cycles d'exécution sécurisés et prévisibles. De plus, la présence des instructions `import` permet un ancrage contextuel parfait pour l'IA, qui identifie instantanément la source de vérité de chaque composant.

## Conséquences
- L'utilisation combinée d'un script classique et d'un script module sur une même chaîne d'exécution est proscrite en raison du cloisonnement des scopes.
- Le mot-clé `export` devient obligatoire sur toute nouvelle infrastructure transverse créée sous `webroot/js/core/`.

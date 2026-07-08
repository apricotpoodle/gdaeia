# ADR 0032 : Standardisation du flux de travail de développement (Git & Qualité du Code)

## 1. Contexte
Pour garantir la maintenabilité, la lisibilité et la robustesse de l'application sur le long terme, il est impératif d'adopter une méthodologie de développement unifiée. L'absence d'un cadre strict conduit inévitablement à une dette technique (répétition de code, typage lâche, historique Git chaotique) qui complique les revues de code et l'analyse statique (PHPStan).

Nous avons besoin de définir le cycle de vie exact d'une ligne de code, depuis sa conception jusqu'à son intégration dans la branche principale (`main`).

## 2. Décision
Nous adoptons un flux de travail itératif et standardisé basé sur les étapes suivantes, qui doivent être scrupuleusement respectées par tout contributeur (humain ou IA) :

### Étape 1 : Isolation (Branche Feature)
* Tout nouveau développement, correction de bug ou refactorisation doit s'effectuer dans une branche dédiée (ex: `feature/nom-de-la-fonctionnalite`, `fix/description-du-bug`), créée à partir de la branche `main` à jour.
* Le développement direct sur `main` est strictement interdit.

### Étape 2 : Ingénierie et Qualité du Code (Le "Standard de Fer")
La production de code doit respecter simultanément les principes suivants :
* **DRY (Don't Repeat Yourself)** : Mutualisation systématique des logiques communes.
* **KISS (Keep It Simple, Stupid)** : Privilégier la clarté et la simplicité algorithmique à la complexité prématurée.
* **Design Patterns** : Utilisation des patrons de conception adéquats (Factory, Builder, Observer, Singleton, etc.) justifiés par les ADR existants.
* **Typage et PHPStan** : Le code (PHP et JS) doit être rigoureusement commenté (PHPDoc/JSDoc) et typé pour satisfaire le niveau d'exigence maximal de PHPStan. Aucune tolérance pour les types implicites ou les propriétés magiques non documentées.

### Étape 3 : Itération et Validation (Commits Atomiques)
* Le développement se fait par cycles courts : coder, commenter, valider, commiter.
* Chaque étape logique fonctionnelle doit faire l'objet d'un message de commit adapté, clair et respectant la convention des commits sémantiques (ex: `feat:`, `fix:`, `refactor:`, `docs:`).
* Ce cycle (Étape 2 & 3) est répété itérativement jusqu'à ce que l'objectif fonctionnel de la branche soit totalement atteint.

### Étape 4 : Intégration (Fusion automatisée)
* Une fois la fonctionnalité finalisée et validée, la branche courante est fusionnée dans `main`.
* Cette opération d'intégration doit obligatoirement être réalisée via le script d'automatisation de fusion prévu à cet effet dans le projet, garantissant ainsi l'intégrité de l'historique et le respect des hooks de déploiement. script : `finish_feature`. L'IA fournira toujours le titre et/ou le contenu du pessage de commit général.

## 3. Conséquences

**Positives :**
* **Qualité prédictible** : Le code intégré dans `main` est toujours typé, testable et documenté.
* **Lisibilité de l'historique** : L'utilisation de commits atomiques sémantiques facilite le débogage (git bisect) et la génération de changelogs.
* **Zéro friction avec PHPStan** : L'obligation de rédiger les PHPDocs dès l'étape de codage évite le travail de "rattrapage" technique en fin de sprint.

**Négatives :**
* **Courbe d'apprentissage et vélocité** : La rigueur imposée (typage strict, réflexion sur les patterns avant de coder) peut ralentir la phase initiale de développement, bien que ce temps soit largement récupéré lors des phases de maintenance.

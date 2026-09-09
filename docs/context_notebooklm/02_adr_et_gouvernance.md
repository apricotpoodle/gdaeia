# Agrégation des Décisions d'Architecture (ADR) et Rôles des Modules

Ce document réunit l'ensemble des règles métier, des justifications architecturales (ADR) et la répartition des responsabilités par module.

=== FILE: docs/adr/ADR-ALL-CONSOLIDATED.md ===
=== FILE: docs/adr/0009-implementation-tabulator-users.md ===
# ADR 0009 : Implémentation du module Utilisateurs via API et Tabulator

**Date :** 03 Juillet 2026
**Statut :** Accepté

## Contexte
Mise en œuvre concrète de l'architecture découplée pour l'affichage des données. L'objectif est d'afficher la liste des utilisateurs de manière performante en déléguant le rendu front-end à Tabulator et le traitement métier (pagination/tri) au serveur, tout en imposant une documentation stricte (PHPDoc/JSDoc) sur l'ensemble du code produit.

## Décision
1. **Routage :** Création d'un préfixe de route `/api` forçant les extensions `.json` via `routes.php`.
2. **Service (`TabulatorAdapter.php`) :** Traduit les requêtes JSON de Tabulator en requêtes SQL pour l'ORM CakePHP et formate la réponse paginée.
3. **Séparation MVC (SoC) :** * `Controller/UsersController` : Ne charge aucune donnée, affiche uniquement la coquille HTML.
    * `Controller/Api/UsersController` : Contrôleur technique exposant strictement les données brutes au format JSON.
4. **Webroot :** Isoler le script JS d'instanciation dans `webroot/js/views/Users/index.js` en respectant la nomenclature miroir du backend.

## Justification
Cette approche (Separation of Concerns) garantit que notre API JSON pourra être réutilisée indépendamment (pour une application mobile ou un système externe), tout en gardant des contrôleurs Web extrêmement légers. Le niveau d'exigence sur le PHPDoc assure la maintenabilité du code par n'importe quel développeur.-e
=== END_FILE ===

=== FILE: docs/adr/0012-relations-multiples-orm.md ===
# ADR 0012 : Modélisation manuelle des relations bidirectionnelles multiples

**Date :** 03 Juillet 2026
**Statut :** Accepté

## Contexte
L'outil d'automatisation `bake` échoue systématiquement (`DatabaseException`) lorsqu'il rencontre deux tables liées par de multiples clés étrangères croisées (ex: `departments` possède plusieurs `cgr_codes`, et utilise un `cgr_code` par défaut). L'outil tente d'affecter le même alias de relation aux deux liens.

## Décision
Pour les tables présentant ce motif architectural complexe, la classe `Table` de l'ORM doit être écrite ou corrigée manuellement.
Des alias sémantiques uniques doivent être définis pour chaque relation (ex: `OwnedCgrCodes` et `DefaultCgrCode` dans `DepartmentsTable.php`).

### Point de vigilance : Conventions de nommage et Entités
Lors de la création manuelle d'une classe Table pour contourner le crash, il est impératif de respecter la convention de pluralisation de CakePHP (ex: le fichier DOIT se nommer `DepartmentsTable.php`).
De plus, la création manuelle de la Table n'entraîne pas la création de l'Entité associée. Il faut exécuter la commande générique `bin/cake bake model <NomAuPluriel>` (ex: `bin/cake bake model Departments`). L'outil détectera que la Table existe déjà (et la préservera), mais générera l'Entité et les Fixtures manquantes, évitant ainsi les erreurs d'analyse statique dans l'IDE.

## Justification
On n'altère pas un schéma de base de données parfaitement normalisé pour satisfaire les limites d'un outil de génération de code. La création manuelle de ces classes spécifiques permet à `bake` de les ignorer par la suite et de reprendre sereinement la génération du reste de l'ORM.-e
=== END_FILE ===

=== FILE: docs/adr/0007-configuration-git-ssh.md ===
# ADR 0007 : Gestion des accès Git (Multi-comptes SSH)

**Date :** 03 Juillet 2026
**Statut :** Accepté

## Contexte
Le développement nécessite parfois de pousser du code sur un dépôt appartenant à une organisation spécifique ou à un compte personnel distinct (ex: `apricotpoodle`), tout en utilisant une machine configurée avec une clé SSH professionnelle par défaut. Cela génère des erreurs `Permission denied (publickey)`.

## Décision
Nous utilisons les **Alias SSH** pour gérer de multiples identités de manière transparente (KISS & DRY).
1. Le fichier `~/.ssh/config` de la machine hôte doit définir un alias (ex: `Host github-apricot`) pointant vers la clé SSH adéquate avec la directive `IdentitiesOnly yes`.
2. L'URL distante du dépôt local (`git remote`) doit utiliser cet alias à la place du traditionnel `github.com` (ex: `git@github.com-perso:apricotpoodle/gdaeia.git`).

## Justification
Cette méthode évite d'avoir à manipuler manuellement le `ssh-agent` à chaque session de terminal ou de risquer des commits avec la mauvaise identité cryptographique.-e
=== END_FILE ===

=== FILE: docs/adr/0022-standardisation-colonne-actions-ui.md ===
# ADR 0022 : Standardisation de la colonne d'actions (DataGrids)

**Date :** 03 Juillet 2026
**Statut :** Accepté

## Contexte
Les tableaux de bord nécessitent fréquemment des boutons d'interaction par ligne (CRUD, Export, etc.) et des actions globales (Création, Réinitialisation de l'état). Répéter le balisage HTML (Bootstrap 5.3) et la logique de capture d'événements dans chaque fichier de vue constitue une violation du principe DRY (Don't Repeat Yourself) et complexifie la maintenance.

## Décision
Intégration d'une méthode abstraite `setWithActions(buttonsArray)` dans le composant `TabulatorBuilder`.
1. **En-tête** : Injection stricte d'un Dropdown Bootstrap contenant les actions globales (Créer / Reset).
2. **Cellules** : Injection d'un groupe de boutons configuré dynamiquement selon les clés demandées (ex: `view`, `edit`, `delete`, `viewpdf`, `impersonate`).
3. **Communication inter-composants** : La capture des clics est interceptée par la grille, qui retransmet l'intention au bus d'événements global (`TabulatorObserver`) sous le format d'événement `[sélecteur]:action:[nom_action]`.

## Justification
Cette approche supprime totalement la logique de manipulation du DOM dans les scripts de vues (Event-Driven Architecture). La grille émet un événement métier, et le script local de la vue décide de la manière d'y réagir (affichage d'une modale, redirection, etc.), garantissant un couplage lâche.
-e
=== END_FILE ===

=== FILE: docs/adr/0010-verification-integrite-orm.md ===
# ADR 0010 : Procédure de vérification de l'intégrité de l'ORM

**Date :** 03 Juillet 2026
**Statut :** Accepté

## Contexte
Suite à l'utilisation d'une boucle shell pour contourner les erreurs de la commande `bake model all` (voir ADR 0008) causées par des dépendances SQL circulaires, il est nécessaire de valider que les classes d'associations générées (`Table`) sont fonctionnelles au moment de l'exécution (Runtime).

## Décision
La vérification de l'intégrité de l'ORM s'effectue via la Console REPL interactive de CakePHP (`bin/cake console`).
*Note : Dans CakePHP 5, cet outil nécessite l'installation préalable du plugin de développement via `composer require --dev cakephp/repl` et son activation via `bin/cake plugin load Cake/Repl`.*

En invoquant le `TableLocator` et en interrogeant la méthode `associations()->keys()` sur les modèles interdépendants, on force le framework à compiler l'arbre des relations en mémoire. Si aucune `DatabaseException` n'est levée, le modèle est considéré comme intègre et utilisable dans les Contrôleurs.

## Justification
L'utilisation du REPL respecte le principe KISS : c'est un outil dédié au débogage qui permet un diagnostic immédiat et non destructif des liens mémoire de l'ORM, sans avoir à créer des scripts de test jetables.-e
=== END_FILE ===

=== FILE: docs/adr/0046-standardisation-crud-field-authorizations.md ===
# ADR 0046 : Standardisation du CRUD hybride (FieldAuthorizations)

**Date :** 14 Août 2026
**Statut :** Accepté

## Contexte
Suite au succès de l'architecture hybride déployée pour la gestion des utilisateurs, il est nécessaire de dupliquer ce fonctionnement pour la table `FieldAuthorizations`. L'objectif est de conserver des "Skinny Controllers" et de séparer les responsabilités entre l'affichage (Web) et la manipulation de données (API).

## Décision
1. **Contrôleur Web (`src/Controller/FieldAuthorizationsController.php`)** : Aura la responsabilité stricte de rendre les gabarits HTML (`index`, `add`, `edit`). Il gère également l'action `delete` de manière hybride (redirection standard ou payload JSON) pour satisfaire la grille Tabulator.
2. **Contrôleur API (`src/Controller/Api/FieldAuthorizationsController.php`)** : S'occupera exclusivement de consommer les requêtes XHR/Fetch (`add`, `edit`) en renvoyant un standard JSON stricte (`success`, `message`).
3. **Sécurité (Policy)** : Toutes les méthodes intègrent un verrou d'autorisation (`$this->Authorization->authorize()`) pointant vers `FieldAuthorizationPolicy`.

## Justification (DRY, SoC, KISS)
* **SoC (Separation of Concerns)** : Le rendu HTML n'est jamais mélangé avec la logique métier JSON.
* **KISS** : La gestion hybride du `delete` évite de dupliquer la logique de vérification d'intégrité de l'ORM.

## Liens
* [README Contrôleurs Web](../../src/Controller/README.md)
* [README Contrôleurs API](../../src/Controller/Api/README.md)-e
=== END_FILE ===

=== FILE: docs/adr/0039-chargement-progressif-scroll-infini.md ===
# ADR 0039 : Défilement Infini (Progressive Loading) au lieu de la Pagination

**Date :** 10 Juillet 2026
**Statut :** Accepté

## Contexte
La pagination classique (boutons Précédent/Suivant, numéros de page) interrompt le flux de lecture de l'utilisateur et surcharge visuellement l'interface. Tabulator propose un mode `progressiveLoad: "scroll"` permettant de charger les données de manière transparente à mesure que l'utilisateur fait défiler la grille vers le bas.

## Décision
1. Ajout de la méthode `.setContinuousScroll(size)` dans le `TabulatorBuilder`.
2. Activation par défaut de ce comportement dans le socle commun (`TabulatorFactory._createBaseGrid`).
3. La taille des lots (pages masquées) est fixée par défaut à 40 enregistrements, garantissant que l'ascenseur apparaisse immédiatement sur les grands écrans.

## Justification (KISS & UX)
L'expérience utilisateur devient similaire aux standards des réseaux sociaux et des SPA modernes. L'API backend (CakePHP) ne subit aucune modification : le `TabulatorAdapter` génère déjà le format compatible exigé par le Progressive Loading (`{ "data": [...], "last_page": X }`). Le couplage avec l'ADR 0038 (Confinement Flexbox) garantit un défilement fluide et exclusivement interne à la grille.
-e
=== END_FILE ===

=== FILE: docs/adr/0032-flux-de-travail-developpement.md ===
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
* Cette opération d'intégration doit obligatoirement être réalisée via le script d'automatisation de fusion prévu à cet effet dans le projet, garantissant ainsi l'intégrité de l'historique et le respect des hooks de déploiement. script : `finish_feature`. L'IA fournira toujours le titre et/ou le contenu du message de commit général.

## 3. Conséquences

**Positives :**
* **Qualité prédictible** : Le code intégré dans `main` est toujours typé, testable et documenté.
* **Lisibilité de l'historique** : L'utilisation de commits atomiques sémantiques facilite le débogage (git bisect) et la génération de changelogs.
* **Zéro friction avec PHPStan** : L'obligation de rédiger les PHPDocs dès l'étape de codage évite le travail de "rattrapage" technique en fin de sprint.

**Négatives :**
* **Courbe d'apprentissage et vélocité** : La rigueur imposée (typage strict, réflexion sur les patterns avant de coder) peut ralentir la phase initiale de développement, bien que ce temps soit largement récupéré lors des phases de maintenance.
-e
=== END_FILE ===

=== FILE: docs/adr/0048-decoupage-ihm-zones-applicationform.md ===
# ADR 0048 : Découpage de l'IHM Applicationform en 5 zones fonctionnelles

## Statut
Accepté

## Contexte
Le formulaire et la vue de l'entité `Applicationform` comportent de nombreux champs. Pour améliorer l'expérience utilisateur et garantir la confidentialité des données, l'IHM doit être structurée en 5 zones distinctes dont la visibilité/accessibilité dépend des rôles utilisateurs. De plus, la zone dédiée aux commentaires doit maximiser l'espace utile à l'écran.

## Décisions
1. **Zones fonctionnelles** : Définition de 5 zones au niveau du modèle/champ de l'entité :
   - `admin` (département, utilisateur, dates...)
   - `contrat` (type de contrat, motif de recrutement, catégorie pro, etc.)
   - `rémunération` (rémunération brute, fréquence/période)
   - `réservés` (champs d'administration RH ou validation interne)
   - `commentaires` (fil de discussion polymorphique)
2. **Gestion de l'espace pour les commentaires** :
   - Utilisation d'un panneau latéral repliable (Offcanvas/Drawer) ou d'un onglet dédié compact avec compteur pour réduire la surface occupée sur l'écran principal.
3. **Ancrage dans l'entité `Applicationform`** :
   - Centralisation des identifiants/clés des zones sous forme de constantes publiques dans l'entité `Applicationform`.

## Conséquences
- Lisibilité accrue et contrôle d'accès granulaire par zone/rôle.
- Interface épurée nécessitant moins de défilement vertical.
-e
=== END_FILE ===

=== FILE: docs/adr/0025-routage-dynamique-metadonnees-dropdown.md ===
# ADR 0025 : Routage Dynamique Polymorphique des Actions de Ligne Tabulator via les Métadonnées

**Date :** 03 Juillet 2026
**Statut :** Accepté

## Contexte
Avec l'expansion de l'application (visant plus de 30 boutons d'actions métiers différents, ex: `view`, `edit`, `viewpdf`, `impersonate`), l'architecture front-end doit être capable de mapper chaque clic de ligne vers l'URL ou l'API CakePHP correspondante (`/controller/action/id`) de manière entièrement générique. Écrire des blocs `switch` ou `if/else` monolithiques pour chaque action violerait les principes **DRY** et **KISS** et rendrait la maintenance impossible.

## Décision
1. **Routage par Métadonnées (Data Attributes)** : Injection des directives de routage (`data-action`, `data-target`, `data-is-event`) directement par la `ButtonFactory` au sein des balises HTML des boutons.
2. **Couplage par Registre de Configuration Centralisé** : Centralisation du dictionnaire de configuration de tous les boutons de l'application dans `ButtonFactory.js`.
3. **Paramétrage Fluide du Contrôleur** : Introduction de la méthode `.setController(string)` dans `TabulatorBuilder` pour dynamiquement contextualiser la racine de l'URL CakePHP selon l'entité de la table.
4. **Gestion des Tables sans Contrôleur Dédié (Tables Mixtes/Polymorphes)** : Si aucun contrôleur global n'est défini, le système lit l'attribut `data-controller` de la ligne de données. Cela permet à une seule table (ex: un tableau de bord global ou un flux d'activités) de rediriger vers des contrôleurs différents (ex: `/articles/view/12` sur la ligne 1, et `/users/view/4` sur la ligne 2).

## Spécification du dictionnaire de routage (ButtonFactory)
Chaque bouton déclaré doit respecter l'interface de configuration suivante :
- `icon` (string) : Classes FontAwesome de l'icône.
- `color` (string) : Variante de couleur Bootstrap (`primary`, `info`, `danger`, `warning`, etc.).
- `title` (string) : Texte du tooltip de survol.
- `target` (string, optionnel) : Cible de navigation (`_self` ou `_blank`). Par défaut `_self`.
- `isEvent` (boolean, optionnel) : Si `true`, empêche la redirection d'URL native et propage instantanément un événement global via le `TabulatorObserver` (idéal pour les confirmations de suppression ou l'ouverture de modales).

## Conséquences
- **Maintenance ultra-réduite** : L'ajout d'une action métier prend moins de 10 secondes (une simple ligne de déclaration dans le dictionnaire).
- **Zéro logique conditionnelle dans le Builder** : Le moteur de clic du `TabulatorBuilder` reste figé et immuable à environ 30 lignes de code, quel que soit le nombre d'actions futures.
- **Flexibilité totale** : Support natif du multi-contrôleur par ligne pour les tableaux de bord composites.
-e
=== END_FILE ===

=== FILE: docs/adr/0006-gestion-des-vues-dans-les-migrations.md ===
# ADR 0006 : Traitement des Vues SQL dans les Migrations Phinx

**Date :** 02 Juillet 2026
**Statut :** Accepté

## Contexte
L'utilitaire `bake migration_snapshot` identifie par défaut les Vues MySQL comme des tables physiques et génère du code de création de tables standards, détruisant la logique dynamique du workflow.

## Décision
Il est interdit de laisser Phinx gérer les Vues de manière automatisée.
Le fichier de migration initial a été altéré manuellement :
1. Les définitions automatisées des vues ont été purgées.
2. Les vues sont créées via du SQL brut dans la méthode `up()` avec `CREATE OR REPLACE VIEW`.
3. Les vues sont explicitement détruites dans la méthode `down()` via `DROP VIEW IF EXISTS`.

## Justification (KISS & DRY)
* **Conformité :** Garantit que l'environnement de base de données reconstruit à partir de zéro est 100 % identique à la base de production.
* **Performance :** La logique de calcul d'état du workflow reste centralisée dans le moteur MySQL (Vues), déchargeant l'application PHP.
-e
=== END_FILE ===

=== FILE: docs/adr/0040-mecanisme-usurpation-identite-impersonate.md ===
# ADR 0040 : Mécanisme d'usurpation d'identité (Impersonate)

**Date :** 10 Juillet 2026
**Statut :** Accepté

## Contexte
Pour faciliter le support utilisateur, les administrateurs doivent pouvoir reproduire les bugs en naviguant dans l'application avec les droits et le point de vue d'un utilisateur final, sans lui demander son mot de passe.

## Décision
1. **Sécurité (Policy)** : L'action est protégée par `UserPolicy::canImpersonate`, qui autorise uniquement un profil `issuperuser` à usurper un profil classique (interdiction de faire de l'usurpation croisée entre administrateurs).
2. **Gestion de Session** : Plutôt que de stocker un flag boolean, l'identifiant du compte Super Admin d'origine est stocké dans la clé de session `Auth.original_user_id` lors de la bascule.
3. **Bascule Native** : Nous utilisons la méthode `setIdentity($targetUser)` du composant `Authentication` de CakePHP 5 pour remplacer l'objet en mémoire, ce qui s'applique instantanément au middleware global sans rompre l'architecture.
4. **Retour à la normale** : L'API JSON (`MenusController.js`) lit la présence de `Auth.original_user_id` pour faire apparaître le bouton de déconnexion spécifique (`revertIdentity`), qui restaure la session du Super Admin et purge la clé.

## Justification
Cette approche est non destructive. Elle évite d'altérer la base de données (pas de table de log de session complexe) et repose entièrement sur le stockage serveur volatil (Session). Elle est totalement transparente pour les contrôleurs : du point de vue de CakePHP, l'administrateur devient véritablement le client final.
-e
=== END_FILE ===

=== FILE: docs/adr/0008-limitation-generation-orm.md ===
# ADR 0008 : Stratégie de génération ORM (Contournement CakePHP Bake)

**Date :** 03 Juillet 2026
**Statut :** Accepté

## Contexte
La commande `bin/cake bake model all` échoue avec une `DatabaseException` (association mismatch) lors de la rencontre de dépendances circulaires ou bidirectionnelles complexes (ex: `departments` et `cgr_codes`). L'outil tente d'affecter le même alias à des cibles différentes dans le même processus mémoire.

## Décision
La conception de la base de données étant intègre et métier-cohérente, nous n'altérons pas le schéma SQL pour satisfaire un outil de génération.
La génération automatique des modèles doit se faire séquentiellement via des appels individuels (ex: boucle shell `for table in ... ; do bin/cake bake model $table; done`) pour isoler le cache mémoire de chaque exécution.

### Exception pour les relations bidirectionnelles multiples
Même avec une exécution séquentielle, `bake` crashe définitivement s'il rencontre deux tables liées par de multiples clés étrangères (ex: `departments` et `cgr_codes`).
Dans ce cas précis, la seule solution fiable consiste à créer la classe `Table` manuellement (ex: `DepartmentsTable.php`) en attribuant des alias uniques et sémantiques aux relations (ex: `OwnedCgrCodes` et `DefaultCgrCode`). Une fois le fichier créé manuellement, la commande `bake` l'ignorera intelligemment et poursuivra la génération du reste de l'ORM.


## Justification
Cette approche respecte la réalité métier du schéma de données tout en palliant une limite technique temporaire de l'outil de génération en ligne de commande de CakePHP.-e
=== END_FILE ===

=== FILE: docs/adr/0035-trait-specialise-logs-email.md ===
# ADR 0035 : Simplification de l'écriture des Logs (EmailLoggerTrait)

**Date :** 09 Juillet 2026
**Statut :** Accepté

## Contexte

Suite à la mise en place du routage des logs de courriels (ADR 0034), l'appel répété à la méthode `$this->log('Msg', 'level', ['scope' => ['email']])` au sein des contrôleurs et des mailers s'est avéré trop verbeux. Cela dégrade la lisibilité du code métier et viole le principe DRY (répétition du tableau de configuration du scope).

## Décision
1. Création d'un **Trait PHP** `EmailLoggerTrait` dans `src/Log/`.
2. Ce Trait expose une méthode sémantique et percutante : `traceEmail(string $message, string $level = 'info')`.
3. Ce Trait agit comme une façade (Facade Pattern) masquant la complexité du tableau de paramétrage natif de CakePHP.

## Justification
L'utilisation d'un Trait est la solution architecturale la plus propre en PHP pour partager des comportements transversaux entre des classes qui n'ont pas la même ascendance (comme `AppController` et `AppMailer`). Le nom `traceEmail` est concis et explicite ses intentions.
-e
=== END_FILE ===

=== FILE: docs/adr/0002-connexion-base-de-donnees.md ===
# ADR 0002 : Gestion de la configuration et connexion MySQL

**Date :** 02 Juillet 2026
**Statut :** Accepté

## Contexte
L'application doit se connecter à une base de données MySQL externe (ex: `websrv31.glm.lan`). Nous devons garantir que les identifiants de connexion ne sont jamais versionnés dans le code source de l'application (`/app`) pour des raisons évidentes de sécurité.

## Décision
Nous appliquons le principe "Config in Environment" de la méthodologie 12-Factor App.
Les identifiants sont stockés dans le fichier `.env` à la racine de l'infrastructure (ignoré par Git). Docker Compose se charge d'injecter ces variables sous forme de variables d'environnement natives dans le conteneur PHP.

## Justification (Principes SOLID & KISS)
1. **Sécurité :** Le dépôt Git de l'application CakePHP ne contient aucun secret.
2. **KISS :** CakePHP 5 lit nativement la variable `DATABASE_URL` via la fonction `env()`. Aucune bibliothèque supplémentaire (comme `vlucas/phpdotenv`) n'est requise dans le projet CakePHP lui-même, car Docker fait le travail en amont.
3. **Portabilité :** Pour passer de l'environnement de développement à la production, il suffit de changer le fichier `.env` de l'infrastructure, sans toucher au code applicatif.-e
=== END_FILE ===

=== FILE: docs/adr/0038-confinement-hauteur-grilles-tabulator.md ===
# ADR 0038 : Confinement de la hauteur des grilles de données (Tabulator)

**Date :** 10 Juillet 2026
**Statut :** Accepté

## Contexte
Sur des écrans standards, l'affichage de grilles de 20 lignes engendre un débordement du flux DOM hors de la fenêtre d'affichage (Viewport). Cela fait apparaître la barre de défilement verticale principale du navigateur (`body`), causant la perte de visibilité des en-têtes de l'application (Navbar) et une mauvaise expérience utilisateur.

## Décision
1. Ajout de la méthode `.setHeight(height)` dans le `TabulatorBuilder`.
2. Utilisation systématique d'une hauteur calculée en CSS via `calc(100vh - Xpx)` (où X représente la somme des marges et du menu de navigation) dans les fabriques métiers (`TabulatorFactory`).

## Justification (KISS & UX)
En contraignant la hauteur de l'enveloppe de la grille, le navigateur ne défile plus. C'est Tabulator qui active intelligemment sa propre barre de défilement interne *uniquement si le contenu l'exige*. Les en-têtes de colonnes restent fixes et les boutons de navigation globaux sont toujours accessibles.
-e
=== END_FILE ===

=== FILE: docs/adr/0033-implementation-mailer-reinitialisation-mdp.md ===
# ADR 0033 : Architecture de la couche Mailer (Principe DRY et SoC)

**Date :** 09 Juillet 2026
**Statut :** Accepté

## Contexte
Le flux de récupération de mot de passe nécessite l'envoi de courriels transactionnels contenant des liens sécurisés (jetons éphémères). Insérer la logique de formatage (Sujet, Modèle, Variables) et de configuration SMTP directement au sein du `UsersController` provoquerait un couplage fort et une violation de la Séparation des Préoccupations (SoC).

## Décision
1. **Création d'un socle d'infrastructure (`AppMailer`)** : Une classe de base abstraite centralise la configuration absolue des courriels sortants (Expéditeur générique système, configuration duale HTML/Texte).
2. **Spécialisation par domaine (`UserMailer`)** : Les envois d'emails liés aux utilisateurs héritent de l'`AppMailer`. Ils portent la responsabilité exclusive d'assembler la vue, les variables (`setViewVars`) et le sujet, allégeant ainsi le contrôleur.
3. **Sécurité Anti-Énumération** : Dans le contrôleur, que le compte existe ou non, et que l'envoi SMTP réussisse ou échoue (capturé par un bloc `try/catch` silencieux qui trace uniquement dans les logs système), le message renvoyé à l'interface est toujours un message de succès générique.

## Justification
Cette séparation garantit un code hautement testable et maintenable. Toute modification de l'identité d'expédition de l'entreprise s'opère dans l'unique fichier `AppMailer`, se propageant instantanément à l'ensemble du système (KISS & DRY).

## Sécurité du flux de réinitialisation
- Les actions `forgotPassword` et `resetPassword` sont déclarées explicite dans `UsersController::beforeFilter()` avec `allowUnauthenticated()` et `skipAuthorization()` afin de permettre aux utilisateurs non authentifiés de réinitialiser leur mot de passe sans être redirigés vers l'IHM de login.-e
=== END_FILE ===

=== FILE: docs/adr/0030-modernisation-scripts-modules-es6.md ===
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
-e
=== END_FILE ===

=== FILE: docs/adr/0011-droits-home-www-data.md ===
# ADR 0011 : Droits sur le répertoire personnel de www-data

**Date :** 03 Juillet 2026
**Statut :** Accepté

## Contexte
L'exécution d'outils CLI interactifs (comme `bin/cake console` basé sur PsySH) ou d'outils globaux (Composer) au sein du conteneur lève des avertissements de type `Notice: Writing to directory /var/www/.config/psysh is not allowed`.

## Décision
Le `Dockerfile` est modifié pour attribuer la propriété de l'intégralité du répertoire `/var/www` (le dossier personnel par défaut sous Debian) à l'utilisateur `www-data` remappé, et non plus uniquement au sous-dossier applicatif `/var/www/html`.

## Justification
Cette modification permet aux utilitaires CLI exécutés sous l'identité `www-data` d'écrire leurs fichiers de configuration et d'historique (fichiers dot) sans entrave, garantissant un environnement de développement silencieux et pleinement fonctionnel.-e
=== END_FILE ===

=== FILE: docs/adr/0028-authentification-evolutive-et-impersonate.md ===
# ADR 0028 : Infrastructure d'Authentification Évolutive et Habilitations de Session

**Statut :** Proposé

## Contexte
L'application requiert la mise en place d'un système d'authentification et d'autorisation robuste, capable de gérer des accès par couple identifiant/mot de passe dans un premier temps, tout en garantissant une extension transparente vers un fournisseur d'identité tiers (Google OAuth 2.0) à moyen terme. De plus, une fonctionnalité d'usurpation d'identité (`impersonate`) est requise pour le support technique de niveau Super Administrateur.

## Décisions
1. **Couple Authentication / Authorization** : Adoption des deux plugins officiels de CakePHP pour découpler l'identification (Middleware) du contrôle d'accès métier (Policies).
2. **Persistance Évolutive (Google Ready)** : Schématisation de la table `users` intégrant nativement un champ `google_id` nullable. L'authentification future par Google réutilisera le même flux d'identification en ajoutant simplement un `GoogleAuthenticator` au gestionnaire sans altérer l'arborescence des droits.
3. **Cinématique de Confiance** : Verrouillage des comptes à l'inscription via un drapeau `is_verified` soumis à la validation d'un jeton à usage unique transmis par canal SMTP (Mailer).
4. **Encapsulation de l'Impersonate** : Isolation de l'état de session d'origine du Super Administrateur lors du switch d'identité, adossée à un contrôle strict par la Policy `UsersPolicy::canImpersonate`.

## Conséquences
* L'ensemble des contrôleurs de l'application exigera par défaut une identité valide, sauf dérogation explicite (`$this->Authentication->addUnauthenticatedActions()`).
-e
=== END_FILE ===

=== FILE: docs/adr/0044-filtre-recherche-fulltext-search-plugin.md ===
# ADR 0044 : Recherche FULLTEXT MySQL via Callback FriendsOfCake/Search

**Date :** 14 Août 2026
**Statut :** Accepté

## Contexte
La recherche globale sur les demandes de recrutement (`applicationforms`) nécessite d'interroger simultanément plusieurs colonnes textuelles (`jobtitle`, `applicantname`, `qualification`, `reasonforreplacement`) avec un niveau de performance maximal sur un volume important de données.

## Décision
1. Création d'un index `FULLTEXT` composite (`ft_applicationforms_global`) via une migration Phinx sur les colonnes cibles.
2. Déclaratif du Behavior `Search.Search` dans `ApplicationformsTable`.
3. Implémentation du callback `q` dans `searchManager()` pour formater dynamiquement la requête en syntaxe booléenne MySQL (`+terme*`) et appliquer la clause `MATCH() AGAINST(... IN BOOLEAN MODE)`.

## Justification
* **Performance** : Exploite l'index `FULLTEXT` natif d'InnoDB/MySQL au lieu de requêtes `LIKE %...%` coûteuses qui provoquent des balayages complets de table (*full table scans*).
* **SOLID / Fat Model** : Toute la logique de formatage booléen et d'appel à l'index est isolée dans la Table, rendant le contrôleur et l'API agnostiques.-e
=== END_FILE ===

=== FILE: docs/adr/0042-acl-granulaire-niveau-champ-formulaire.md ===
# ADR 0042 : ACL Granulaire au Niveau du Champ de Formulaire (Field-Level Security)

**Date :** 10 Juillet 2026
**Statut :** Accepté

## Contexte
La création et la modification d'entités (ex: `Users`) requièrent une granularité de sécurité supérieure à la simple validation de route. Certains profils d'opérateurs peuvent créer un utilisateur sans pour autant avoir le droit d'élever ses privilèges (modifier `issuperuser` ou altérer le `role_id`).

## Décision
1. **Couche Données (`field_authorizations`)** : Utilisation de la table dédiée pour mapper les couples `[role_id, resource, field]` vers un niveau d'accès (`EDIT`, `VIEW`, `NONE`).
2. **Double Validation (Backend + Frontend)** :
   * **Front-End (`create.js`)** : Interroge l'API pour récupérer son schéma et brider dynamiquement le DOM (masquage `d-none` ou passage en lecture seule `disabled`).
   * **Back-End (`FieldAuthorizationService`)** : Réceptionne le POST et purge immédiatement (`unset`) toute clé de données ne disposant pas du droit `EDIT` avant de la passer au `patchEntity`.

## Justification (Principes SOLID)
Cette approche respecte la **Défense en Profondeur**. Le bridage front-end offre une excellente expérience utilisateur, tandis que le filtrage strict back-end empêche toute tentative de falsification de requêtes (Mass-Assignment / Parameter Tampering) par un utilisateur malveillant manipulant la console réseau.
-e
=== END_FILE ===

=== FILE: docs/adr/0003-configuration-ide-vscodium.md ===
# ADR 0003 : Configuration de l'IDE (VSCodium) avec Docker

**Date :** 02 Juillet 2026
**Statut :** Accepté

## Contexte
Le code source est édité localement via VSCodium, mais PHP et ses outils (PHPCS, PHPStan) ne sont installés qu'à l'intérieur du conteneur Docker (KISS). Cela génère des conflits, de faux positifs et des lenteurs si l'IDE tente de valider le code localement (notamment en scannant le dossier `vendor/`).

## Décision
1. **LSP (Language Server Protocol) :** Nous utilisons `PHP Intelephense` pour l'autocomplétion, car il gère intelligemment le dossier `vendor/` sans émettre de faux positifs de validation. Les extensions trop agressives comme `php-resolver` sont proscrites.
2. **Exclusion spatiale :** Le fichier `.vscode/settings.json` est configuré pour exclure formellement `vendor/`, `tmp/` et `logs/` de l'indexation, de la recherche et de l'écoute des modifications (`files.watcherExclude`).
3. **Qualité du code :** La validation de la syntaxe et des normes (PHP CodeSniffer, PHPStan) se fait *exclusivement* via les commandes du `Makefile` (`make cs.check`, `make stan`), c'est-à-dire à l'intérieur du conteneur.

## Justification
Cette approche garantit une *Developer Experience* (DX) fluide. L'IDE reste rapide et silencieux sur la machine hôte, tout en s'assurant que la validation du code reste stricte et reproductible pour n'importe quel développeur grâce à l'isolation Docker.-e
=== END_FILE ===

=== FILE: docs/adr/0004-api-tabulator-et-design-patterns.md ===
# ADR 0004 : Architecture de l'API Tabulator et Patrons de Conception GoF

**Date :** 02 Juillet 2026
**Statut :** Accepté

## Contexte
L'application doit afficher des tables de données complexes via Tabulator. Pour garantir la performance face à de gros volumes de données, le traitement (pagination, tri, filtres) doit être délégué au serveur MySQL via CakePHP. De plus, les assets front-end sous `webroot` doivent être organisés de manière propre et modulaire.

## Décisions
1. **Stratégie de chargement :** Utilisation exclusive du mode *Remote* de Tabulator (requêtes AJAX successives lors des changements de page/tri/filtres).
2. **Patron de conception GoF - L'Adaptateur (Adapter) :** Nous créons un composant de type *Adapter* pour faire la passerelle entre le format des requêtes/réponses de Tabulator et le fonctionnement de l'ORM de CakePHP.
3. **Structure de `webroot` :** Organisation stricte et modulaire des fichiers JavaScript et CSS pour séparer la configuration globale de Tabulator de la logique spécifique de chaque page.

## Justification
* **SOLID (SRP) :** Le contrôleur CakePHP ne sait pas comment Tabulator formate ses requêtes. C'est l'Adaptateur qui a cette unique responsabilité.
* **DRY :** L'Adaptateur est générique. Il peut être réutilisé pour n'importe quelle table (Utilisateurs, Commandes, Produits) sans dupliquer de code.
* **KISS :** Le navigateur ne télécharge que les données visibles à l'écran (ex: 20 lignes), préservant la mémoire et la fluidité de l'interface.-e
=== END_FILE ===

=== FILE: docs/adr/README.md ===
# Documentation et Décisions Architecturales (ADR)

Ce dossier contient les *Architecture Decision Records* (ADR).
À chaque choix technique structurant (framework, base de données, infrastructure), un document numéroté est créé ici pour expliquer le contexte, la décision et ses conséquences.

## Index des décisions

| Référence | Sujet Technique / Décision | Statut |
| :--- | :--- | :--- |
| [ADR 0001](./0001-choix-infrastructure-docker.md) | Choix de l'infrastructure Docker | **Accepté** |
| [ADR 0002](./0002-connexion-base-de-donnees.md) | Gestion de la configuration et connexion MySQL | **Accepté** |
| [ADR 0003](./0003-configuration-ide-vscodium.md) | Configuration de l'IDE (VSCodium) avec Docker | **Accepté** |
| [ADR 0004](./0004-api-tabulator-et-design-patterns.md) | Architecture de l'API Tabulator et Patrons de Conception GoF | **Accepté** |
| [ADR 0005](./0005-architecture-base-de-donnees.md) | Architecture de la base de données MySQL | **Accepté** |
| [ADR 0006](./0006-gestion-des-vues-dans-les-migrations.md) | Gestion des vues dans les migrations | **Accepté** |
| [ADR 0007](./0007-configuration-git-ssh.md) | Configuration git ssh | **Accepté** |
| [ADR 0008](./0008-limitation-generation-orm.md) | Limitation génération ORM | **Accepté** |
| [ADR 0009](./0009-implementation-tabulator-users.md) | Implémentation tabulator users | **Accepté** |
| [ADR 0010](./0010-verification-integrite-orm.md) | Vérification intégrité ORM | **Accepté** |
| [ADR 0011](./0011-droits-home-www-data.md) | Droits home www data | **Accepté** |
| [ADR 0012](./0012-relations-multiples-orm.md) | Relations multiples ORM | **Accepté** |
| [ADR 0013](./0013-architecture-frontend-js.md) | Architecture frontend js | **Accepté** |
| [ADR 0014](./0014-standardisation-fonctionnalites-tabulator.md) | Standardisation des fonctionnalités de base Tabulator | **Accepté** |
| [ADR 0015](./0015-mapping-champs-tri-orm.md) | Mapping dynamique des champs de tri entre JSON et ORM | **Accepté** |
| [ADR 0016](./0016-coherence-psr4-autoloading.md) | Organisation des Services de Données et Cohérence PSR-4 | **Accepté** |
| [ADR 0017](./0017-collision-parametres-pagination.md) | Collision des paramètres de requête (Tabulator/CakePHP) | **Accepté** |
| [ADR 0018](./0018-mapping-filtres-tabulator-orm.md) | Mapping filtres tabulator orm | **Accepté** |
| [ADR 0019](./0019-i18n-et-ergonomie-des-grilles.md) | I18n et ergonomie des grilles | **Accepté** |
| [ADR 0020](./0020-automatisation-formatage-ide.md) | Automatisation du Formatage de Code via l'IDE | **Accepté** |
| [ADR 0021](./0021-arborescence-documentation-applicative.md) | Arborescence documentation applicative | **Accepté** |
| [ADR 0022](./0022-standardisation-colonne-actions-ui.md) | Standardisation colonne actions ui | **Accepté** |
| [ADR 0023](./0023-factory-boutons-actions-ui.md) | Factory boutons actions ui | **Accepté** |
| [ADR 0024](./0024-contournement-conflit-positionnement-dropdown.md) | Résolution du conflit de positionnement des dropdowns | **Accepté** |
| [ADR 0025](./0025-routage-dynamique-metadonnees-dropdown.md) | Routage dynamique métadonnées dropdown | **Accepté** |
| [ADR 0026](./0026-controle-acces-visuel-grid-rights.md) | Contrôle accès visuel grid rights | **Accepté** |
| [ADR 0027](./0027-gestion-messages-flash-dynamiques.md) | Gestion messages flash dynamiques | **Accepté** |
| [ADR 0028](./0028-authentification-evolutive-et-impersonate.md) | Authentification évolutive et impersonate | **Accepté** |
| [ADR 0029](./0029-workflow-git-feature-branch.md) | Workflow git feature branch | **Accepté** |
| [ADR 0030](./0030-modernisation-scripts-modules-es6.md) | Modernisation de l'infrastructure front-end via les modules ES6 | **Accepté** |
| [ADR 0031](./0031-exemption-securite-debugkit.md) | Stratégie d'exemption de sécurité pour les outils de développement | **Accepté** |
| [ADR 0032](./0032-flux-de-travail-developpement.md) | Standardisation du flux de travail de développement | **Accepté** |
| [ADR 0033](./0033-implementation-mailer-reinitialisation-mdp.md) | Architecture de la couche Mailer | **Accepté** |
| [ADR 0034](./0034-specialisation-logs-courriels.md) | Spécialisation et Routage des Logs de Courriels | **Accepté** |
| [ADR 0035](./0035-trait-specialise-logs-email.md) | Simplification de l'écriture des Logs (EmailLoggerTrait) | **Accepté** |
| [ADR 0036](./0036-encapsulation-expedition-email-safesend.md) | Encapsulation de l'expédition SMTP (safeSend) | **Accepté** |
| [ADR 0037](./0037-hebergement-local-dependances-frontend.md) | Hébergement Local des Dépendances Front-end | **Accepté** |
| [ADR 0038](./0038-confinement-hauteur-grilles-tabulator.md) | Confinement de la hauteur des grilles de données | **Accepté** |
| [ADR 0039](./0039-chargement-progressif-scroll-infini.md) | Défilement Infini (Progressive Loading) au lieu de la Pagination | **Accepté** |
| [ADR 0040](./0040-mecanisme-usurpation-identite-impersonate.md) | Mécanisme d'usurpation d'identité (Impersonate) | **Accepté** |
| [ADR 0041](./0041-segregation-donnees-model-custom-finders.md) | Ségrégation des Données (RLS) Centralisée via Custom Finders | **Accepté** |
| [ADR 0042](./0042-acl-granulaire-niveau-champ-formulaire.md) | ACL Granulaire au Niveau du Champ de Formulaire | **Accepté** |
| [ADR 0043](./0043-commande-test-envoi-email.md) | Commande CLI de test d'envoi de courriels et serveur Mailpit | **Accepté** |
| [ADR 0044](./0044-filtre-recherche-fulltext-search-plugin.md) | Recherche FULLTEXT MySQL via Callback FriendsOfCake/Search | **Accepté** |
| [ADR 0045](./0045-gestion-crud-field-authorizations.md) | Administration CRUD de la sécurité des champs | **Accepté** |
| [ADR 0046](./0046-standardisation-crud-field-authorizations.md | Standardisation crid authorization des champs | **Accepté** |
| [ADR 0046](./0047-normalisation-vues-identity-assets-js.md) | Administration CRUD IHM applicationform | **Accepté** |-e
=== END_FILE ===

=== FILE: docs/adr/0031-exemption-securite-debugkit.md ===
# ADR 0031 : Stratégie d'exemption de sécurité pour les outils de développement (DebugKit)

## 1. Contexte
L'application utilise les middlewares CakePHP `Authentication` et `Authorization` avec une politique de type "Fail-Closed" (`requireAuthorizationCheck` activé). Cela garantit que toute route dépourvue d'une vérification explicite de droits est automatiquement bloquée par sécurité (Erreur 500 - `AuthorizationRequiredException`).

Cependant, le plugin de développement `DebugKit` possède ses propres contrôleurs (ex: `ToolbarController`). Ces contrôleurs n'héritent pas du `AppController` de l'application et n'implémentent aucune logique de vérification d'autorisation (Policies). Par conséquent, l'affichage de la barre d'outils est systématiquement bloqué par les middlewares de l'application hôte.

Tenter de résoudre ce problème en modifiant `AppController::beforeFilter` est inefficace, car les requêtes vers les plugins n'empruntent pas ce cycle de vie.

## 2. Décision
Nous avons décidé d'implémenter une **dérogation de sécurité globale et centralisée**, gérée par un écouteur d'événements (Event Listener) situé au niveau de l'amorçage de l'application (`config/bootstrap.php`), plutôt que de polluer les contrôleurs métiers.

Nous utilisons l'événement `Controller.startup` pour intercepter toutes les requêtes de manière agnostique. Si la requête cible explicitement le plugin `DebugKit` :
1. Le gestionnaire d'événements injecte dynamiquement le composant `Authorization` sur le contrôleur du plugin.
2. Il force l'instruction `$controller->Authorization->skipAuthorization()` pour bypasser légitimement le middleware.
3. Il ouvre l'accès non-authentifié si le composant `Authentication` est présent.

## 3. Conséquences

**Positives :**
* **Pureté du code métier** : Le fichier `src/Controller/AppController.php` reste propre et ne contient aucune logique conditionnelle liée à l'environnement de développement ou à des outils externes.
* **Robustesse de sécurité** : La dérogation est hermétique, strictement limitée par la condition `$request->getParam('plugin') === 'DebugKit'`. Elle n'expose aucune route de l'application.
* **Compatibilité PHPStan** : L'utilisation d'annotations ciblées (`@phpstan-ignore-next-line`) pour les composants chargés dynamiquement maintient le niveau d'exigence maximal de l'analyse statique.

**Négatives :**
* L'ajout d'un écouteur global sur `Controller.startup` ajoute une micro-pénalité de performance (négligeable) à chaque requête. (À noter : DebugKit étant désactivé en production, ce code pourrait être encapsulé dans une vérification d'environnement `if (Configure::read('debug'))` pour une optimisation absolue).
-e
=== END_FILE ===

=== FILE: docs/adr/0047-normalisation-vues-identity-assets-js.md ===
# ADR 0047 - Centralisation de l'identité dans AppView et gestion des scripts de vue

* **Status :** Accepted
* **Date :** 2026-08-20

---

## Context

L'accès à l'identité de l'utilisateur connecté dans la couche de présentation nécessitait jusqu'à présent de récupérer manuellement l'attribut de requête (`$this->request->getAttribute('identity')`) dans chaque template CTP/PHP. Cette répétition alourdissait les vues et posait des soucis de typage statique (Intelephense / PHPStan) lors de l'évaluation des autorisations via `$identity->can()`.

Par ailleurs, l'insertion ponctuelle de blocs JavaScript inline (`<script>`) au sein des templates de vue posait des problèmes majeurs :
1. **Sécurité :** Violation des politiques *Content Security Policy* (CSP) strictes interdisant `unsafe-inline`.
2. **Performances :** Absence de mise en cache HTTP des scripts d'interaction.
3. **Maintenabilité :** Dispersion de la logique comportementale et rupture de la séparation des responsabilités (*Separation of Concerns*).

---

## Décision

### 1. Exposition globale et typage de l'identité dans `AppView`
L'instance de l'identité connectée est automatiquement injectée dans le contexte global de rendu via la méthode `initialize()` de `AppView` :

```php
// src/View/AppView.php
public function initialize(): void
{
    parent::initialize();
    $this->set('identity',$this->getRequest()->getAttribute('identity'));
}
```

- **Typage IHM / DocBlock** : Pour garantir la résolution des méthodes de contrôle d'accès (`can()`) par l'analyse statique et les IDE, les templates de vue doivent annoter l'identité avec l'interface du décorateur d'autorisation :

```PHP
/** @var \Authorization\IdentityInterface|null $identity */
```

- **Règle** : Les templates de vue ne doivent plus réinterroger la requête. La variable `$identity` est disponible nativement dans toutes les vues (`templates/**/*.php`).

### 2. Isolation des assets JavaScript dans webroot/js/views/{model}/

- **Interdiction stricte des scripts inline** : Les balises `<script>` sont proscrites des templates .`php`.

- **Arborescence dédiée par modèle** : Les scripts comportementaux de vue doivent être enregistrés sous le dossier du modèle concerné :
`webroot/js/views/{model}/{script}.js` (ex: `webroot/js/views/applicationforms/comments-handler.js`).

- **Chargement via bloc CakePHP** : L'injection dans la mise en page s'effectue exclusivement via le helper HTML :

```PHP
<?= $this->Html->script('views/applicationforms/comments-handler', ['block' => true]) ?>
```

### 3. Normalisation de la disposition UI des vues CRUD

Les vues de détail (`view.php`) et d'édition (`edit.php`) adoptent une structure standardisée à deux colonnes :

- Un volet latéral (`aside.side-nav`) regroupant la navigation contextuelle et les actions de modification/suppression protégées par `$identity?->can()`.

- Une zone principale (`div.content`) réservée aux données métier et aux composants associés (ex: fil de discussion).

## Consequences

### Positive

- **Analyse statique sans avertissement** : Le typage via `\Authorization\IdentityInterface` résout les fausses erreurs IDE (ex: `P1013 Undefined method 'can'`).

- **Conformité CSP** : Élimination totale des vulnérabilités liées aux scripts inline.

- **Mise en cache optimale** : Exploitation native du cache HTTP pour les assets sous `webroot/js/views/{model}/`.

- **Ergonomie homogène** : Harmonisation visuelle de l'ensemble des modules CRUD.

### Négative

- Nécessite de migrer le code JS inline existant vers des fichiers statiques dédiés sous `webroot/js/views/{model}/` lors du refactoring des vues legacy.
-e
=== END_FILE ===

=== FILE: docs/adr/0021-arborescence-documentation-applicative.md ===
# ADR 0021 : Emplacement du répertoire de documentation dans un contexte de Boilerplate

**Date :** 03 Juillet 2026
**Statut :** Accepté

## Contexte
La racine du projet (contenant les configurations Docker et d'infrastructure globale) est isolée et gérée comme un dépôt "boilerplate" distinct. Seul le sous-répertoire `/app` constitue le dépôt Git actif de l'application métier. Les documents d'architecture (ADR) doivent impérativement être versionnés avec l'application qu'ils décrivent.

## Décision
Le répertoire `/docs` est positionné de manière définitive à la racine du dépôt applicatif, soit dans `app/docs/`.

## Justification
* **Gouvernance et Versioning :** Les décisions d'architecture évoluant au même rythme que les fonctionnalités métier, elles doivent résider dans le même historique Git que le code source (`/app`).
* **Séparation des cycles de vie :** Évite de polluer le dépôt du boilerplate d'infrastructure avec des documentations purement fonctionnelles ou applicatives.
* **Sécurité :** Situé en dehors de `app/webroot/`, le dossier `app/docs/` bénéficie de la protection native du framework et ne peut en aucun cas être exposé publiquement sur le réseau Web.
-e
=== END_FILE ===

=== FILE: docs/adr/0045-gestion-crud-field-authorizations.md ===
# ADR 0045 : Administration CRUD de la sécurité des champs (FieldAuthorizations)

**Date :** 14 Août 2026
**Statut :** Accepté

## Contexte
L'application intègre une sécurité au niveau des champs (Field-Level ACL) pilotée par la table `field_authorizations`. Les super-administrateurs ont besoin d'une interface pour configurer dynamiquement ces permissions (qui peut voir ou éditer quel champ pour quelle ressource).

## Décision
1. **Routage API Exclusif** : Suivant l'ADR 0009, le contrôleur Web `FieldAuthorizationsController` ne servira que la coquille HTML. Les opérations d'ajout, modification et suppression sont déléguées à `Api\FieldAuthorizationsController`.
2. **Skinny Controller** : Les méthodes de l'API (`add`, `edit`, `delete`) s'appuient sur l'ORM natif pour limiter la logique métier dans le contrôleur. Les vérifications de sécurité s'appuient strictement sur `FieldAuthorizationPolicy` via le composant d'autorisation.
3. **Réponses JSON Standardisées** : Toutes les opérations de mutation (POST/PUT/DELETE) retourneront un payload standardisé `{ "success": bool, "message": string, "errors": array|null }` manipulable facilement par l'adaptateur Ajax de Tabulator.

## Justification (KISS & SoC)
Déléguer la persistance à une API JSON maintient le couplage lâche exigé par Tabulator. Les opérations de persistance s'appuient nativement sur l'entité CakePHP, déléguant la validation des données au modèle `FieldAuthorizationsTable` de manière centralisée (DRY).-e
=== END_FILE ===

=== FILE: docs/adr/0015-mapping-champs-tri-orm.md ===
# ADR 0015 : Mapping dynamique des champs de tri entre JSON et ORM

**Date :** 03 Juillet 2026
**Statut :** Accepté

## Contexte
Le composant front-end Tabulator envoie des requêtes de tri basées sur le nommage de l'objet JSON reçu (ex: `field: "id"` ou `field: "role.name"`). L'injection directe de ces champs dans la clause `ORDER BY` de CakePHP génère des erreurs d'ambiguïté SQL (`Ambiguous column error`) lors des jointures (`contain`), car MySQL ne sait pas à quelle table appartient l'ID.

## Décision
Le `TabulatorAdapter` a été enrichi d'un mécanisme d'inflexion (via `\Cake\Utility\Inflector`).
1. Tout champ simple (sans point) est automatiquement préfixé par l'alias de la table principale (ex: `Users.id`).
2. Tout champ imbriqué (avec un point) est converti de la convention JSON (singulier) vers la convention ORM CakePHP (Pluriel/CamelCase) (ex: `role.name` devient `Roles.name`).

## Justification
Cette solution est 100% DRY. Elle dispense le développeur front-end de devoir spécifier des champs SQL dans la configuration Javascript (séparation des préoccupations). L'API traduit elle-même le dialecte JSON en dialecte SQL de manière sécurisée.-e
=== END_FILE ===

=== FILE: docs/adr/0029-workflow-git-feature-branch.md ===
# 🔄 Workflow Git : Le Feature Branch Flow

Ce document décrit le flux de travail standard de l'application (basé sur le GitHub/GitLab Flow).
Toute nouvelle fonctionnalité, correction de bug ou expérimentation doit suivre ce cycle de vie strict afin de garantir la stabilité de la branche `main`.

---

## 🛠️ Étape 1 : Préparer et Créer la Branche

Ne développez **jamais** directement sur `main`. Mettez à jour votre poste et isolez votre travail.

**Règle de nommage :** `feature/nom-de-la-feature`, `fix/nom-du-bug`, `refactor/nom-du-module`.

### Via CLI (Standard)
```bash
git checkout main
git pull origin main
git fetch -p
git checkout -b feature/ma-nouvelle-fonctionnalite
```

### Via Lazygit
1. Allez sur le panneau Branches (touche 3).

2. Sélectionnez main, appuyez sur Espace pour vous y rendre, puis p pour tirer les nouveautés.

3. Appuyez sur n (new) pour créer une nouvelle branche et saisissez feature/ma-nouvelle-fonctionnalite.

## 💻 Étape 2 : Développer et Commiter (Le Quotidien)
Développez votre code et faites des commits atomiques (petits et logiques) avec des messages respectant la norme Conventional Commits (ex: feat(module): description).
### Documentation

- Renseigner tout fichier README.md local et tout fichier ADR.
    - Créer des fichiers README.md Locaux aux bons endroits.
    - Toujours préciser le lien à rajouter dans le README.md des ADR.
    - Toujours bien renseigner les fichiers ADR et les fichiers README.md locaux concernés par le travail en cours.

### Developpement

- Renseigner tout fichier README.md local et tout fichier ADR.
    - Créer des fichiers README.md Locaux aux bons endroits.
    - Toujours préciser le lien à rajouter dans le README.md des ADR.
    - Toujours bien renseigner les fichiers ADR et les fichiers README.md locaux concernés par le travail en cours.
- Toujours favoriser le code DRY, SOLID, voire  KISS et les patrons de conception (Style GoF) et toute bonne pratique industrielle de développement..
    - Suggérer toute bonne pratique et poser des questions, en cas de doute, afin d'éliminer toute hallucination.

- Au niveau du code CakePHP, privilegier les "fat Models" et les "skinny Controllers".

### Commit

Les messages de commit seront fournis dans leur entiereté, donc titre compris, au format bloc de code prêt à être copié collé.

#### Via CLI
```Bash
git add src/MonFichier.php
git commit -m "feat(module): ajout de la nouvelle fonction"
```
#### Via Lazygit
1. Panneau Files (touche 1).

2. Appuyez sur Espace sur les fichiers modifiés pour les indexer (Stage).

3. Appuyez sur c (commit) et rédigez votre message.

## 🚀 Étape 3 : Clôturer et Ouvrir la Pull Request (PR)
Une fois le développement terminé, il faut envoyer le code sur le serveur distant et demander sa fusion via une Pull Request (PR) pour déclencher la relecture et l'Intégration Continue (CI).

### L'approche Magique (Script Automatisé finish_feature)
Nous utilisons un outil maison qui gère le Push et la création de la PR en une seule commande.

#### Scénario A : En équipe (Créer la PR pour relecture)

```Bash
finish_feature
```
(Le script pousse la branche, ouvre votre éditeur pour rédiger le corps de la PR, la soumet sur GitHub/GitLab et vous rend la main).

#### Scénario B : En solo (Mode Expéditif "Auto-Merge")

```Bash
finish_feature -t "feat(module): ajout de la nouvelle fonction" --auto-merge
```

(Le script pousse, crée la PR avec ce titre, demande à GitHub de la fusionner automatiquement, rapatrie le main mis à jour et nettoie votre PC).

### L'approche Classique (CLI)
```Bash
git push -u origin feature/ma-nouvelle-fonctionnalite
gh pr create --fill
```

### L'approche Lazygit
1. Panneau Branches, appuyez sur P (Maj+p) pour pousser la branche locale.

2. Appuyez sur o (open) pour ouvrir la page GitHub/GitLab dans votre navigateur web et y créer la PR manuellement.

## 🧹 Étape 4 : Le Nettoyage Local (Fetch Prune)
Si vous avez utilisé `finish_feature --auto-merge`, cette étape est déjà faite pour vous.

Une fois la PR validée et fusionnée sur l'interface web, votre branche locale ne sert plus à rien et la branche distante n'existe plus. Il faut nettoyer votre environnement.

### Via CLI
```Bash
git checkout main
git pull origin main
git fetch -p             # Met à jour les références et supprime les branches distantes obsolètes
git branch -d feature/ma-nouvelle-fonctionnalite # Supprime la branche locale
```

### Via Lazygit
1. Espace sur main (pour changer de branche).

2. Appuyez sur p pour tirer le code fusionné.

3. Appuyez sur f pour rafraîchir et purger (Fetch Prune).

4. Sélectionnez votre ancienne branche de travail, et appuyez sur d (delete) pour la supprimer de votre PC.
-e
=== END_FILE ===

=== FILE: docs/adr/0018-mapping-filtres-tabulator-orm.md ===
# ADR 0018 : Mapping des opérateurs de filtrage Tabulator vers ORM CakePHP

**Date :** 03 Juillet 2026
**Statut :** Accepté

## Contexte
En activant le mode `filterMode: remote` dans Tabulator, les saisies utilisateurs dans les en-têtes génèrent des requêtes HTTP contenant un tableau `filters`. Ce tableau précise le champ, la valeur, et l'opérateur souhaité (ex: `like`, `=`, `<`). L'API back-end ignorait jusqu'à présent ces paramètres.

## Décision
Le `TabulatorAdapter` a été enrichi pour intercepter le tableau `filters` et construire dynamiquement la clause `WHERE` de la requête CakePHP.
1. Utilisation de la méthode `resolveOrmField()` pour sécuriser les noms de colonnes et éviter les ambiguïtés (cf ADR 0015).
2. Création d'un bloc `switch` traduisant l'opérateur textuel de Tabulator (ex: `like`) en véritable clause SQL sécurisée par PDO (ex: `champ LIKE %valeur%`).

## Justification
Cette évolution centralise la logique de recherche dans l'adaptateur global. Les contrôleurs restent vides de toute logique de traitement des entrées utilisateur, garantissant une architecture pérenne (SOLID). La logique de résolution d'ambiguïté a été factorisée pour respecter le principe DRY.-e
=== END_FILE ===

=== FILE: docs/adr/0027-gestion-messages-flash-dynamiques.md ===
# ADR 0027 : Gestion Dynamique des Messages Flash et Suppressions Asynchrones (Ajax)

**Date :** 05 Juillet 2026
**Statut :** Accepté

## Contexte
Pour moderniser l'expérience utilisateur (approche SPA - Single Page Application) et éviter les rechargements de page intempestifs lors des actions de suppression depuis les grilles Tabulator, les requêtes matérielles (`window.location.href`) doivent être remplacées par des appels asynchrones (`fetch`).
Cette transition soulève deux défis majeurs :
1. **Sécurité :** CakePHP exige la présence d'un jeton CSRF valide pour toute altération de données (POST/DELETE).
2. **Retour Utilisateur (UX) :** L'application doit pouvoir notifier l'utilisateur du succès ou de l'échec de l'opération (y compris en affichant les messages d'erreur métiers générés par CakePHP) sans nécessiter de rechargement de la page pour afficher les composants Flash natifs du framework.

## Décision
1. **Création du `FlashManager`** : Implémentation d'une classe utilitaire statique front-end (`webroot/js/core/FlashManager.js`) générant à la volée des alertes Bootstrap 5 flottantes. Ces alertes s'auto-détruisent via un minuteur asynchrone (DOM Garbage Collection) pour ne pas polluer l'arbre DOM.
2. **Injection du Jeton CSRF** : Le layout principal de l'application (`templates/layout/default.php`) expose désormais le jeton de sécurité natif de CakePHP via une balise `<meta name="csrfToken">`.
3. **Appels API standardisés** : Les actions destructrices de l'orchestrateur de vue (ex: `delete` dans `index.js`) utilisent l'API `fetch` en injectant l'en-tête `X-CSRF-Token` et `Accept: application/json`.
4. **Interception des Erreurs JSON** : Si le serveur CakePHP renvoie un code 4xx/5xx, le payload JSON est intercepté côté client pour extraire la clé `message` et l'afficher de manière native via le `FlashManager`.

## Justification
* **Performances** : La suppression combinée d'un enregistrement en base de données et de la ligne front-end (`usersTable.deleteRow(id)`) est instantanée, économisant un aller-retour complet de rendu de page complet par le serveur.
* **Propreté du DOM (KISS)** : Ne nécessitant aucun conteneur HTML codé en dur, le `FlashManager` est entièrement autonome et peut être invoqué depuis n'importe quel module JavaScript de l'application.

## Conséquences
* Toute future requête Ajax (POST, PUT, DELETE) devra obligatoirement récupérer la balise `<meta name="csrfToken">` pour s'authentifier auprès de CakePHP.
* L'utilisation des redirections de contrôleurs (`$this->redirect()`) doit être proscrite sur les méthodes ciblées par des appels Ajax. Le serveur doit répondre avec un code HTTP 200 (ou 204) et idéalement un payload JSON.
-e
=== END_FILE ===

=== FILE: docs/adr/0013-architecture-frontend-js.md ===
# ADR 0013 : Patrons de conception pour le front-end JavaScript (Tabulator)

**Date :** 03 Juillet 2026
**Statut :** Accepté

## Contexte
L'instanciation de composants complexes comme Tabulator.js directement dans les scripts de vues engendre une forte duplication de code (violation du principe DRY) et un couplage fort rendant les mises à jour de l'API difficiles.

## Décision
1. Mise en place d'un dossier `webroot/js/core/Tabulator/` centralisant l'architecture front-end.
2. Implémentation du patron **Builder** pour abstraire la configuration native de Tabulator.
3. Implémentation du patron **Factory** pour centraliser la déclaration des grilles métiers.
4. Implémentation du patron **Observer** pour gérer la communication inter-composants sur des vues complexes.
5. Obligation d'utiliser la `JSDoc` et un `README.md` local pour documenter ce cœur technique.-e
=== END_FILE ===

=== FILE: docs/adr/0005-modele-de-donnees.md ===
# ADR 0005 : Architecture de la Base de Données

**Date :** 02 Juillet 2026
**Statut :** Accepté
**Moteur :** MySQL (InnoDB, utf8mb4)

## Contexte et Modèle Conceptuel
La base de données soutient une application de gestion de formulaires (DAE) intégrant un moteur de workflow (validation séquentielle) et une ségrégation des données par département et par rôle.

L'architecture est divisée en 5 grands domaines fonctionnels :

### 1. Cœur Métier (Les Demandes)
C'est le centre de gravité de l'application.
* `applicationforms` : Table principale stockant les demandes d'emploi/recrutement.
* `validations` & `applicationvalidationsteps` : Stockage des visas posés sur une demande.

### 2. Moteur de Workflow (Configuration)
Définit les règles de validation avant même qu'une demande ne soit créée.
* `validationsequences` : Définit l'ordre de validation (séquence) requis pour chaque département et rôle.
* `validationstatuses` : Dictionnaire des états (Brouillon, En attente, Validé, Rejeté...).

### 3. Gestion des Accès et de l'Organisation (ACL)
* `users`, `roles`, `departments` : La trinité classique de la gestion d'organisation.
* `user_departments` & `role_menus` : Tables de liaison (Many-to-Many) pour délimiter les périmètres de visibilité.
* `field_authorizations` : (Très spécifique) Permet une granularité de sécurité au niveau du champ de formulaire selon le rôle.

### 4. Référentiels et Nomenclatures
Tables satellites de configuration (souvent préfixées ou utilisées pour les listes déroulantes).
* Exemples : `contracttypes`, `hiringreasons`, `professionalcategories`, `budgetfeatures`, `cgr_codes`, `cgr_strategies`.

### 5. Vues SQL (Optimisation)
Pour éviter de surcharger l'ORM CakePHP avec des jointures complexes lors des calculs d'état, la logique est déportée dans MySQL via des vues :
* `applicationformstatuses` : Calcule en temps réel si une demande est en cours, acceptée ou rejetée.
* `currentvalidationroles` : Identifie à qui le tour de valider.
* `validation_visas` : Aplatit l'historique des validations pour l'affichage (chronologie).-e
=== END_FILE ===

=== FILE: docs/adr/0026-controle-acces-visuel-grid-rights.md ===
# ADR 0026 : Contrôle d'Accès Visuel et Structurel Unifié des Grilles via 'grid_rights'

**Date :** 04 Juillet 2026
**Statut :** Accepté

## Contexte
Avec la mise en place de la `ButtonFactory` (ADR 0023) et de la standardisation des fonctionnalités (ADR 0014), nos grilles applicatives gèrent plus de 30 actions métiers. Pour des impératifs stricts de sécurité d'entreprise, l'affichage des composants d'interface (verrouillage des boutons d'actions par ligne et masquage des colonnes entières contenant des données sensibles comme `issuperuser`) doit être piloté exclusivement par le serveur (Back-End).

Cependant, les cycles de traitement asynchrones et l'hydratation distante (Remote) de Tabulator provoquent des anomalies de synchronisation graphique : l'écouteur `dataProcessed` se déclenche trop tôt, avant la stabilisation géométrique du DOM, ce qui empêche l'application des méthodes structurelles comme `hideColumn()`. De plus, les rétentions agressives de mémoire tampon (cache) du navigateur paralysent le déploiement des scripts d'infrastructure JavaScript en local et en production.

## Décision
1. **Contrat d'Échange Centralisé (AppEntity)** : Toutes les entités CakePHP destinées à être rendue dans une grille héritent d'une classe parente abstraite `AppEntity`. Celle-ci injecte au payload JSON une propriété virtuelle unique et protégée nommée `grid_rights`.
2. **Architecture du Payload Sécurisé** : La clé `grid_rights` sépare hermétiquement les périmètres d'exécution graphiques :
   - `actions` (Dictionnaire de booléens par ligne) : Gère l'activation ou la désactivation des boutons CRUD individuels.
   - `columns` (Dictionnaire de booléens par table) : Détermine si une colonne doit être structurellement présente ou masquée du DOM.
3. **Interception Synchrone Immuable (TabulatorBuilder)** : La logique de traitement des droits de colonnes est retirée des fabriques de grilles métiers et s'intègre de manière obligatoire au cœur de la méthode `build()` du `TabulatorBuilder` via le callback natif `ajaxResponse`.
4. **Contournement Macro-Task du Timing DOM** : L'exécution des boucles d'ordres `tableInstance.hideColumn()` au sein de `ajaxResponse` est encapsulée dans un micro-différé asynchrone (`setTimeout` de 10ms) afin de forcer l'ajustement géométrique juste après la création physique des nœuds DOM par Tabulator.
5. **Cache-Busting Applicatif Permanent** : Modification de la configuration des assets de CakePHP (`config/app.php`) pour forcer le timestamping des fichiers statiques.

## Spécification de la structure de données (grid_rights)
Le payload JSON transmis au composant front-end respecte le format d'imbrication strict suivant :
```json
"grid_rights": {
    "actions": {
        "view": true,
        "edit": false,
        "delete": false
    },
    "columns": {
        "email": true,
        "issuperuser": false
    }
}
```

## Justification
    1. Respect des Principes DRY et KISS : Centraliser l'écoute des droits dans le cœur du TabulatorBuilder évite de dupliquer et de copier-coller les écouteurs d'événements (dataLoaded ou renderComplete) dans les 30+ fabriques de grilles de l'application.

    2. Expérience Utilisateur Stable (Zéro Clignotement) : Le recours au callback ajaxResponse couplé au différé de 10ms offre la garantie mathématique que la table possède ses données avant d'altérer sa structure, éliminant ainsi les décalages de colonnes visuels et les clignotements d'en-têtes à l'écran.

    3. Pérennité du Versioning des Déploiements : Forcer la configuration Asset.timestamp à la valeur 'force' ordonne à CakePHP de générer un paramètre de requête (ex: ?v=1783176245) basé uniquement sur la date de modification réelle du fichier sur le disque. Le navigateur conserve le cache de manière performante, mais le brise instantanément à chaque mise à jour de fichier d'infrastructure.

## Conséquences
    - Sécurité garantie par le serveur : L'interface applique aveuglément les directives calculées par les Policies et le modèle PHP ; modifier le DOM localement ne permet pas de contourner les droits.

    - Allègement drastique des Factories métiers : La déclaration des tables spécifiques (ex: createUsersTable) se focalise uniquement sur les données métiers brutes, l'infrastructure prenant en charge l'évaluation structurelle automatique de sécurité.

    - Maintenance simplifiée : Toute modification de droits applicatifs sur un profil s'effectue dans le modèle PHP sans nécessiter de refonte ou de mise à jour des scripts front-end.


## ⚠️ Piège Architectural : L'écrasement par la Sérialisation JSON (Propriétés Virtuelles)

### Le Problème (Le Fantôme de l'Entité)
Lors du développement initial, il est tentant de déclarer la propriété `grid_rights` dans le tableau `$_virtual` de l'entité de base (`AppEntity.php`) avec un accesseur statique `_getGridRights()` renvoyant des permissions bouchonnées (Mocks).

Cependant, cette approche détruit la logique dynamique générée par l'adaptateur. En effet :
1. Le `TabulatorAdapter` calcule les vrais droits via la `UserPolicy` et les attache à l'entité en mémoire (`$entity->grid_rights = [...]`).
2. Lors du rendu, la méthode `$this->set()` déclenche le moteur de sérialisation JSON de CakePHP.
3. Le moteur détecte la propriété dans `$_virtual`, invoque automatiquement `_getGridRights()`, et **écrase brutalement** les droits dynamiques calculés par l'adaptateur avec les valeurs figées de l'entité mère.

### La Solution (Responsabilité Unique)
Les entités du modèle (Data Objects) doivent rester strictement agnostiques des logiques de présentation UI.
- Ne **jamais** déclarer `grid_rights` dans `$_virtual`.
- Ne **jamais** coder de méthode `_getGridRights()` dans le modèle.
- Laisser l'adaptateur (`TabulatorAdapter`) injecter dynamiquement la propriété `$entity->grid_rights`. Le sérialiseur JSON natif de CakePHP inclura parfaitement cette nouvelle propriété publique ajoutée à la volée, garantissant que les droits reflètent la réalité de la `Policy`.
-e
=== END_FILE ===

=== FILE: docs/adr/0049-integrationtreeselectjs_pour_structures_hierarchiques_departments.md ===
# ADR 0049 : Intégration de TreeselectJS pour les structures hiérarchiques (Départements)

**Date :** 31 Août 2026
**Statut :** Proposé
**Dépendances :**
*   [ADR 0009 : Implémentation du module Utilisateurs via API et Tabulator](./0009-implementation-tabulator-users.md) [2]
*   [ADR 0012 : Modélisation manuelle des relations bidirectionnelles multiples](./0012-relations-multiples-orm.md) [3]
*   [ADR 0030 : Modernisation de l'infrastructure front-end via les modules ES6](./0030-modernisation-scripts-modules-es6.md) [4]
*   [ADR 0037 : Hébergement Local des Dépendances Front-end](./0037-hebergement-local-dependances-frontend.md) [5]
*   [ADR 0047 : Centralisation de l'identité dans AppView et gestion des scripts de vue](./0047-normalisation-vues-identity-assets-js.md) [6]

---

## 1. Contexte
L'application gère une structure organisationnelle complexe où la table `departments` présente des liaisons d'héritage hiérarchique (arborescence parents-enfants) ainsi que des relations bidirectionnelles multiples croisées avec d'autres référentiels (tels que les codes CGR), ce qui nécessite des corrections manuelles au niveau de l'ORM [7, 8].

Pour l'attribution d'une demande de recrutement (`applicationforms`) ou l'affectation d'un utilisateur à un périmètre de départements [9, 10], l'interface utilisateur (UI) requiert un sélecteur de données ergonomique capable d'afficher cette hiérarchie. Les balises HTML standards `<select>` s'avèrent inadaptées pour représenter des relations parents-enfants profondes et provoquent des surcharges visuelles.

Nous avons retenu la bibliothèque **TreeselectJS** [11]. L'enjeu est de définir un cadre d'intégration robuste qui respecte :
1.  Notre politique de **Séparation des Préoccupations (SoC)** (le contrôleur Web livre le squelette HTML, l'API fournit les nœuds hiérarchisés) [12].
2.  L'isolation stricte des fichiers JavaScript de vue, interdisant tout script inline [13].
3.  L'hébergement local des dépendances pour garantir la souveraineté des données, l'indépendance vis-à-vis des CDN et la conformité RGPD [14, 15].
4.  Nos standards de performance (KISS) et d'analyse statique [16].

---

## 2. Décisions

### A. Hébergement local et Importation ES6
*   Conformément à l'ADR 0037, les fichiers sources de la bibliothèque `TreeselectJS` seront téléchargés et hébergés localement sous `webroot/assets/treeselectjs/` [14].
*   Conformément à l'ADR 0030, la bibliothèque sera importée exclusivement sous forme de module ES6 (`type="module"`) au sein de nos scripts d'orchestration [17].

### B. Format d'échange API (Contrat de données)
*   L'API CakePHP exposera les données hiérarchiques des départements via une route JSON dédiée `/api/departments/tree.json`, forçant l'extension `.json` [12].
*   Le payload renvoyé par l'API adoptera la structure récursive attendue par `TreeselectJS` [18, 19] :
    *   `name` (String, obligatoire) : Libellé du département [19].
    *   `value` (String ou Number, obligatoire et strictement unique dans l'arbre) [19, 20].
    *   `children` (Array d'objets du même type, obligatoire) [18, 19].
    *   `disabled` (Boolean, optionnel) [19].
*   L'arborescence sera construite en exploitant le comportement natif `TreeBehavior` de l'ORM de CakePHP via la méthode de recherche `find('threaded')`.

### C. Encapsulation par un Wrapper Front-End
Pour maximiser la réutilisation (DRY) et simplifier l'utilisation du composant par l'équipe, nous créons un module d'infrastructure réutilisable nommé `TreeselectWrapper` sous `webroot/js/core/Components/TreeselectWrapper.js` :
1.  **Directives Système par Défaut** :
    *   `isBoostedRendering: true` : Toujours activé pour optimiser le rendu et prévenir les ralentissements sur les arbres volumineux via l'IntersectionObserver [18, 20].
    *   `clearable: true` et `searchable: true` : Activés par défaut pour fluidifier l'expérience de recherche [21].
    *   `appendToBody: false` : Désactivé par défaut pour éviter que la liste ne soit injectée hors du conteneur parent, sauf cas d'intégration complexe (par exemple dans une grille Tabulator) [22, 23].
2.  **Liaison bidirectionnelle au DOM (Formulaires CakePHP)** :
    *   Le template de vue `.php` définit un conteneur d'ancrage HTML vide (ex: `<div id="dept-tree"></div>`) et un champ masqué `<input type="hidden">` portant le nom du champ attendu par l'ORM CakePHP lors du POST (ex: `name="department_id"`) [18].
    *   Le `TreeselectWrapper` écoute l'événement d'émission `input` de `TreeselectJS` [24] et met automatiquement à jour la valeur de l'élément `<input>` masqué associé [18].

### D. Personnalisation Visuelle (Theming)
*   Aucune injection de style inline ou de règles CSS directes n'est tolérée sur le composant.
*   L'alignement graphique avec notre charte (Bootstrap 5.3) s'effectue en surchargeant les propriétés personnalisées CSS de la bibliothèque (`--treeselectjs-*`) directement sur la pseudo-classe `:root` ou l'élément `body` dans notre feuille de style `custom-theme.css` [23].

### E. Sécurité et Habilitations (Défense en Profondeur)
*   **Contrôle visuel (Front-end)** : Si un utilisateur n'a pas les droits d'écriture sur le champ de destination (vérifié via le schéma d'ACL de champs), le `TreeselectWrapper` sera instancié avec l'option `disabled: true` [18, 25].
*   **Validation stricte (Back-end)** : Le `FieldAuthorizationService` filtrera les requêtes POST entrantes pour valider que le département sélectionné fait partie du périmètre autorisé pour l'utilisateur connecté avant tout `patchEntity` [25, 26].

---

## 3. Justifications
*   **Haute Cohésion & Faible Couplage (SOLID)** : Le contrôleur Web se décharge de toute logique d'arbre [12]. L'API distribue des données JSON standardisées, et le Wrapper JS centralise la complexité d'affichage du widget tierce [12, 18].
*   **Souveraineté et Performance** : L'utilisation d'assets hébergés localement élimine la dépendance vis-à-vis de serveurs CDN tiers (fin des risques de pannes de type Supply Chain ou de fuites d'IP des utilisateurs) [15].
*   **Simplicité de Maintenance (KISS)** : L'utilisation du Wrapper évite d'éparpiller des instanciations complexes de `new Treeselect()` dans chaque orchestrateur de vue.

---

## 4. Conséquences

### Positives :
*   **Expérience utilisateur (UX) supérieure** : Recherche à la volée, repliement fluide des branches et indicateur du nombre d'enfants [21, 22].
*   **Conformité Sécurité** : Pas de scripts inline [13], conformité RGPD [15], et respect du principe de défense en profondeur [27].
*   **Analyse Statique Stable** : Le code du wrapper et des orchestrateurs de vue respecte les directives ES6+ et bénéficie de commentaires JSDoc rigoureux pour l'IDE et l'analyse statique [16, 17].

### Négatives :
*   **Double Liaison** : Obligation d'associer un élément d'ancrage DOM à un champ masqué (`<input type="hidden">`) pour propager la valeur sélectionnée lors de la soumission standard de formulaires non-AJAX [18].

---

## 5. Exemple d'implémentation type

### Backend (API CakePHP)
```php
<?php
// src/Controller/Api/DepartmentsController.php
namespace App\Controller\Api;

use App\Controller\AppController;

class DepartmentsController extends AppController
{
    public function tree()
    {
        $this->request->allowMethod(['get']);

        // Utilisation du TreeBehavior de CakePHP pour construire l'arborescence
        $departments = this->Departments->find('threaded')
            ->select(['id', 'parent_id', 'name'])
            ->toArray();

        // Transformation récursive au format TreeselectJS
        $formattedTree = (this->formatForTreeselect($departments);

        $this->set([
            'success' => true,
            'data' => $formattedTree,
            '_serialize' => ['success', 'data']
        ]);
    }

    private function formatForTreeselect(array $nodes): array
    {
        $result = [];
        foreach ($nodes as $node) {
            $result[] = [
                'value' => $node->id,
                'name' => $node->name,
                'children' => !empty($node->children) ? this->formatForTreeselect($node->children) : []
            ];
        }
        return $result;
    }
}
```

### Frontend (Orchestrateur de Vue)
```js
// webroot/js/views/Users/edit.js
import Treeselect from '../../assets/treeselectjs/treeselectjs.mjs'; // Module ES6 local [14, 17]

document.addEventListener('DOMContentLoaded', () => {
    const container = document.getElementById('dept-tree-container');
    const inputHidden = document.getElementById('department-id');

    if (container && inputHidden) {
        fetch('/api/departments/tree.json', {
            headers: { 'Accept': 'application/json' }
        })
        .then(response => response.json())
        .then(payload => {
            // Instanciation de TreeselectJS
            const treeselect = new Treeselect({
                parentHtmlContainer: container,
                value: inputHidden.value ? [parseInt(inputHidden.value)] : [],
                options: payload.data,
                isSingleSelect: true, // Mode dropdown simple [18, 20]
                showTags: false,       // Rendu comme un menu déroulant classique [18, 20]
                isBoostedRendering: true, // Optimisation pour les grands arbres [18, 20]
                placeholder: "Sélectionnez un département..."
            });

            // Synchronisation de la sélection vers l'input masqué pour CakePHP
            treeselect.srcElement.addEventListener('input', (e) => {
                inputHidden.value = e.detail; // Récupère la valeur sélectionnée [24]
            });
        });
    }
});
```
-e
=== END_FILE ===

=== FILE: docs/adr/0036-encapsulation-expedition-email-safesend.md ===
# ADR 0036 : Encapsulation de l'expédition SMTP (safeSend)

**Date :** 09 Juillet 2026
**Statut :** Accepté

## Contexte
La gestion des exceptions SMTP (déconnexion du serveur, identifiants invalides, timeout) entraînait une duplication des blocs `try/catch` dans les contrôleurs. Bien que l'outil `EmailLoggerTrait` (ADR 0035) ait simplifié la journalisation, la responsabilité de gérer l'échec de l'envoi incombait toujours au contrôleur, ce qui viole le principe de Responsabilité Unique (SRP - Single Responsibility Principle).

## Décision
1. **Création de la méthode `safeSend()`** : Ajoutée directement dans la classe mère `AppMailer`. Cette méthode enveloppe la méthode native `send()` du framework CakePHP.
2. **Capture et Log** : `safeSend()` exécute le bloc `try/catch`, trace le succès ou l'échec via le `EmailLoggerTrait` (en invoquant la façade statique `Log::write()`), et étouffe l'exception de niveau application en retournant un simple booléen.
3. **Contrôleurs Allégés** : Les contrôleurs délèguent totalement la sécurisation du processus au Mailer. L'appel se réduit à une seule ligne : `$mailer->safeSend('action', [$data]);`.

## Justification
Cette approche exploite la puissance de l'Héritage Objet. En centralisant la gestion du flux de sortie (le "Catch") dans l'`AppMailer`, les autres mailers métiers (comme `UserMailer`) restent de simples formateurs de données (Templates/Variables). Les contrôleurs ne sont plus exposés aux objets `Throwable` issus du client SMTP, garantissant une bien meilleure résilience de l'interface utilisateur.
-e
=== END_FILE ===

=== FILE: docs/adr/0023-factory-boutons-actions-ui.md ===
# ADR 0023 : Découplage des éléments UI via les patrons Builder et Factory

**Date :** 03 Juillet 2026
**Statut :** Accepté

## Contexte
La génération dynamique de la colonne d'actions (cf. ADR 0022) nécessitait la manipulation de chaînes de caractères HTML complexes (classes CSS multiples, icônes, attributs de données). Coder ce balisage en dur crée un couplage fort, expose à des erreurs de syntaxe HTML et rend les évolutions graphiques fastidieuses.

## Décision
1. Création de la classe `ButtonBuilder` pour abstraire la mécanique de construction des balises HTML (gestion des attributs, concaténation des classes).
2. Création de la classe statique `ButtonFactory` agissant comme l'unique source de vérité (Single Source of Truth) pour la définition sémantique et visuelle des boutons d'action.
3. Le `TabulatorBuilder` sous-traite systématiquement le rendu de ses cellules d'action à cette Fabrique.

## Justification
Cette architecture obéit au principe de Responsabilité Unique (Single Responsibility) et au principe Ouvert/Fermé (Open/Closed Principle) des règles SOLID. Les designers ou intégrateurs peuvent modifier globalement l'interface (ex: migration Bootstrap 5 vers 6, changement de librairie d'icônes) dans la `ButtonFactory` sans jamais impacter la logique de traitement des données de Tabulator.
-e
=== END_FILE ===

=== FILE: docs/adr/0034-specialisation-logs-courriels.md ===
# ADR 0034 : Spécialisation et Routage des Logs de Courriels

**Date :** 09 Juillet 2026
**Statut :** Accepté

## Contexte
Les envois de courriels transactionnels (via le serveur SMTP) sont sensibles aux défaillances réseau ou de configuration. Actuellement, les erreurs ou succès liés aux envois d'e-mails sont noyés dans les fichiers généraux `error.log` et `debug.log`, ce qui complexifie le diagnostic en production (Séparation des Préoccupations non respectée).

## Décision
1. **Implémentation des Scopes de Log CakePHP** : Création d'un canal de log exclusif `email` dans `config/app.php`.
2. **Isolation** : Ce canal intercepte tous les niveaux de sévérité (de `info` à `error`) à condition que le contexte d'appel contienne l'étiquette `'scope' => ['email']`.
3. **Exclusivité** : Les canaux par défaut (`error` et `debug`) sont explicitement configurés avec `'scopes' => false` afin d'éviter que les messages de courriels ne soient dupliqués dans ces fichiers (principe DRY appliqué aux logs).

## Justification
Cette approche offre un fichier `logs/email.log` propre et dédié. En cas de réclamation d'un utilisateur affirmant "ne pas avoir reçu son e-mail", le support technique dispose d'un point d'entrée unique et chronologique pour retracer le cycle de vie du jeton et l'interaction avec le serveur SMTP, sans être pollué par l'activité HTTP de l'application.
-e
=== END_FILE ===

=== FILE: docs/adr/0024-contournement-conflit-positionnement-dropdown.md ===
# ADR 0024 : Résolution du conflit de positionnement des menus déroulants dans les en-têtes Tabulator

**Date :** 03 Juillet 2026
**Statut :** Accepté

## Contexte
L'en-tête de la table "withActions" requiert un menu déroulant d'administration globale. L'utilisation du mécanisme natif de Bootstrap 5.3 (`data-bs-toggle="dropdown"`) provoquait une défaillance visuelle majeure : le moteur de positionnement sous-jacent (Popper.js) calculait des coordonnées erronées en raison de l'encapsulation dynamique et des contraintes structurelles (`overflow: hidden`) appliquées par Tabulator sur ses colonnes.

## Décision
1. **Désactivation de Popper.js** : Retrait complet de la directive d'automatisation de Bootstrap sur le bouton de l'en-tête généré par la `ButtonFactory`.
2. **Prise en main programmatique** : Implémentation d'une logique d'affichage unitaire au sein du gestionnaire d'événements `headerClick` du `TabulatorBuilder`, pilotant directement la présence de la classe CSS `.show`.
3. **Sanctuarisation CSS** : Injection d'une directive d'affichage agressive (`overflow: visible !important`) dans `custom-theme.css` ciblant les 7 niveaux de calques structurels des en-têtes Tabulator.

## Justification
Cette solution applique scrupuleusement le principe **KISS** (Keep It Simple, Stupid). En éliminant la surcouche de calcul de Popper.js au profit d'un positionnement CSS relatif/absolu standardisé contrôlé par l'application, nous supprimons définitivement les effets de clignotement ou de disparition du menu lors du défilement ou du redimensionnement de la grille.
-e
=== END_FILE ===

=== FILE: docs/adr/0017-collision-parametres-pagination.md ===
# ADR 0017 : Collision des paramètres de requête entre Tabulator et CakePHP

**Date :** 03 Juillet 2026
**Statut :** Accepté

## Contexte
Par défaut, Tabulator envoie ses requêtes de tri via le paramètre d'URL `sort[]` (sous forme de tableau). Or, le composant `Paginator` natif de CakePHP intercepte automatiquement la clé `sort` en attendant une chaîne de caractères (String), causant une erreur fatale PHP `preg_match()`.

## Décision
1. **Front-end :** Utilisation de l'option `dataSendParams` dans le `TabulatorBuilder` pour renommer le paramètre d'envoi en `sorters` (et `filters` pour la recherche).
2. **Back-end :** Déclaration de `'sortableFields' => []` dans les options de la méthode `paginate()` des contrôleurs API pour désactiver totalement le tri natif automatique de CakePHP. Le tri est désormais de la responsabilité exclusive du `TabulatorAdapter`.

## Justification
Cette solution est non-intrusive. Elle permet de conserver l'usage de la méthode native `$this->paginate()` de CakePHP pour le calcul des pages, tout en évitant les collisions de mots-clés réservés sur les query strings.-e
=== END_FILE ===

=== FILE: docs/adr/0041-segregation-donnees-model-custom-finders.md ===
# ADR 0041 : Ségrégation des Données (RLS) Centralisée via Custom Finders Composites

**Date :** 10 Juillet 2026
**Statut :** Accepté

## Contexte
L'application impose un cloisonnement strict des données (Row-Level Security) : un opérateur ne doit avoir accès qu'aux enregistrements (utilisateurs, formulaires) liés à son propre périmètre de départements. Écrire des structures conditionnelles `if/else` ou des jointures manuelles dans les contrôleurs violerait le principe de Responsabilité Unique (SRP) et introduirait un risque élevé de fuite de données en cas d'oubli de duplication du code.

## Décision
Nous centralisons la gouvernance des habilitations d'accès aux lignes de données au sein de la couche Modèle (`ORM\Table`) en utilisant des **Custom Finders hautement typés et sémantiques** :

1. **`UserDepartmentsTable::findDepartmentsOf(user)`** : Responsabilité factuelle. Extrait la sous-requête des IDs de départements associés à une entité `User` spécifique.
2. **`UsersTable::findVisibleTo(user)`** : Responsabilité de gouvernance (Composite). Gère le privilège du `issuperuser` (Bail Early / Vision globale) ou applique la jointure interne (`innerJoinWith`) restrictive basée sur le premier finder pour les utilisateurs cloisonnés.

## Justification (SOLID & Fat Models)
Cette architecture découple entièrement la sécurité graphique et technique du contrôleur (qui se contente d'invoquer `find('visibleTo', user: $currentUser)`). Passer l'entité `$user` complète garantit un typage fort (PHPStan Ready) et une excellente évolutivité. Le recours à l'arbre de dépendance des finders évite toute fuite de variables scalaires ou d'entiers magiques, sanctuarisant la base de données.
-e
=== END_FILE ===

=== FILE: docs/adr/0019-i18n-et-ergonomie-des-grilles.md ===
# ADR 0019 : Internationalisation (i18n) et Ergonomie Transversale des Grilles de Données

**Date :** 03 Juillet 2026
**Statut :** Accepté

## Contexte
L'application s'adresse à un public francophone. Les grilles de données fournies nativement par Tabulator affichent des contrôles de navigation en langue anglaise. De plus, lors des phases de requêtage lourd sur l'API, l'absence de retour visuel altère l'expérience utilisateur (impression de blocage).

## Décision
Intégration systématique des directives d'UX dans le constructeur de base `TabulatorBuilder` :
1. **i18n** : Injection d'un dictionnaire de traduction `'fr-fr'` couvrant l'ensemble du système de pagination et des filtres.
2. **Indicateur d'activité** : Activation de l'option `ajaxLoader: true` couplée à un gabarit HTML personnalisé pour notifier visuellement les phases de synchronisation réseau.
3. **Traitement de la vacuité** : Déclaration de l'option `placeholder` affichant un message explicite en français en cas de retour d'un jeu de données nul.
4. **Isolation Graphique** : Les règles CSS de surcharge (loading, placeholder vide) sont strictement isolées dans `webroot/css/tabulator/custom-theme.css` pour préserver la modularité des composants et éviter la pollution de la feuille de style globale. Un guide de proximité `webroot/css/tabulator/README.md` encadre les futures contributions graphiques.

## Justification
L'intégration de ces fonctionnalités dans le constructeur abstrait respecte à 100% les principes DRY et KISS. L'ensemble de l'application bénéficie d'une charte graphique et fonctionnelle unifiée sans surcharge cognitive pour le développeur lors de la création d'un nouveau module.

-e
=== END_FILE ===

=== FILE: docs/adr/0001-choix-infrastructure-docker.md ===
# ADR 0001 : Choix de l'infrastructure Docker pour le développement

**Date :** 02 Juillet 2026
**Statut :** Accepté

## Contexte
Pour développer notre application CakePHP 5.3 ex nihilo, nous avons besoin d'un environnement de développement local reproductible, isolé et facile à partager, sans polluer la machine hôte.

## Décision
Nous avons décidé d'utiliser Docker avec **Docker Compose**.
L'image de base choisie pour l'application est `php:8.3-apache` construite sur mesure (pas d'image tierce boîte noire).

## Justification
1. **Contrôle et Sécurité :** Partir d'une image PHP officielle nous permet d'installer uniquement les extensions requises par CakePHP 5.3, respectant le principe KISS.
2. **Simplicité du Serveur Web :** L'utilisation d'Apache intégré simplifie le routage par défaut de CakePHP (utilisation des `.htaccess` natifs) comparativement à un couplage Nginx + PHP-FPM.
3. **Prévention des conflits de droits :** Le build intègre une redéfinition de l'UID/GID de `www-data` pour correspondre à l'utilisateur hôte, résolvant le problème classique des fichiers générés appartenant à `root`.

## Conséquences
* Le développement nécessite l'installation de Docker, Docker Compose et Make sur la machine hôte.
* Le code applicatif (`/app`) est totalement découplé de l'infrastructure (volume monté), ce qui permet de versionner les deux indépendamment si besoin.
-e
=== END_FILE ===

=== FILE: docs/adr/0043-commande-test-envoi-email.md ===
# ADR 0043 : Commande CLI de test d'envoi de courriels et serveur d'interception Mailpit

**Date :** 14 Août 2026
**Statut :** Accepté

## Contexte
Afin d'éprouver la chaîne d'expédition des courriels transactionnels (`UserMailer` et `AppMailer`) en environnement de développement, nous devons pouvoir tester l'envoi sans dépendre d'une action IHM Web et sans risquer d'expédier de véritables courriels sur Internet.

## Décision
1. **Serveur d'interception Dev** : Intégration du service `mailpit` dans `docker-compose.yml` (`axllent/mailpit`), exposant l'IHM Web sur le port `8025` et l'écoute SMTP sur le port `1025`.
2. **Configuration réseau & local** : Mise à jour du fichier `config/app_local.php` pour utiliser le driver `SmtpTransport` pointant vers le conteneur `mailpit:1025`.
3. **Outillage CLI** : Création de la commande `src/Command/TestEmailCommand.php` déclarée dans `src/Application.php` pour exécuter des tests unitaires d'envoi via `bin/cake test_email <email>`.
4. **Journalisation** : Utilisation du `EmailLoggerTrait` via `AppMailer` pour consigner le suivi d'expédition dans `logs/email.log`.

## Justification (Principes SOLID & 12-Factor App)
* **Isolation** : Aucun courriel ne quitte l'infrastructure Docker locale.
* **Developer Experience (DX)** : Visualisation immédiate des rendus HTML, texte brut et en-têtes dans l'interface Mailpit (`http://localhost:8025`).
* **Portabilité** : Le code applicatif (`UserMailer`, `TestEmailCommand`) reste agnostique de l'environnement ; le passage en production s'effectuera par simple bascule des variables d'environnement SMTP sans toucher au code.-e
=== END_FILE ===

=== FILE: docs/adr/0014-standardisation-fonctionnalites-tabulator.md ===
# ADR 0014 : Standardisation des fonctionnalités de base des grilles (Tri, Multi-tri et Filtrage)

**Date :** 03 Juillet 2026
**Statut :** Accepté

## Contexte
L'application comporte de nombreuses vues de données complexes (Utilisateurs, Demandes d'autorisation d'engagement, Services, etc.). Afin d'offrir une expérience utilisateur (UX) homogène et performante, chaque tableau doit systématiquement proposer le tri simple, le tri combiné (multi-colonnes) et le filtrage individuel par colonne, sans que cela n'engendre de lignes de codes répétitives.

## Décision
1. **Évolution du Monteur (`TabulatorBuilder`)** : Ajout de la méthode `setColumnDefaults(defaults)` mappée sur la configuration native de Tabulator pour appliquer des comportements transversaux descendants.
2. **Synchronisation Remote** : L'activation de `setRemotePagination()` force également l'état `filterMode: "remote"`. L'application des filtres front-end émettra une requête AJAX structurée vers le contrôleur API.
3. **Configuration par défaut obligatoire** : Toute table métier créée au sein du projet via la `TabulatorFactory` doit explicitement invoquer `setColumnDefaults` pour définir la politique par défaut de tri et de filtrage du module concerné.

## Justification
Cette approche garantit le respect strict des principes DRY et KISS. L'activation des fonctionnalités de recherche et d'organisation est découplée de la définition des colonnes. Les performances de rendu front-end sont préservées, car la captation des entrées de filtrage est soumise à validation (`LiveFilter: false`), évitant le déclenchement de requêtes HTTP à chaque caractère saisi.-e
=== END_FILE ===

=== FILE: docs/adr/0016-coherence-psr4-autoloading.md ===
# ADR 0016 : Organisation des Services de Données et Cohérence PSR-4

**Date :** 03 Juillet 2026
**Statut :** Accepté

## Contexte
Avec l'augmentation du nombre de composants front-end, les adaptateurs et services de manipulation des requêtes de grilles doivent être regroupés logiquement. L'introduction du sous-dossier `src/Service/DataGrid/` nécessite un alignement strict avec la norme d'autoloading PSR-4 pour éviter les erreurs `Class not found`.

## Décision
1. **Rangement** : Tous les adaptateurs de données pour les librairies de datagrid (Tabulator ou autres futurs outils) doivent résider dans le sous-namespace `App\Service\DataGrid`.
2. **Rigueur PSR-4** : Le nommage des fichiers physiques doit correspondre au caractère et à la casse près au nom de la classe PHP interne (ex: `TabulatorAdapter.php`).
3. **Consommation** : Les contrôleurs utilisant ces services doivent importer le FQCN complet (ex: `use App\Service\DataGrid\TabulatorAdapter`).

## Justification
Le respect strict de la norme PSR-4 élimine les comportements imprévisibles de l'autoloading de Composer entre les environnements de développement (parfois insensibles à la casse sur macOS/Windows) et les environnements de production (strictement sensibles à la casse sur Linux/Docker).-e
=== END_FILE ===

=== FILE: docs/adr/0020-automatisation-formatage-ide.md ===
# ADR 0020 : Standardisation et Automatisation du Formatage de Code via l'IDE

**Date :** 03 Juillet 2026
**Statut :** Accepté

## Contexte
Afin de maintenir une base de code lisible, homogène et conforme aux standards de l'industrie (PSR-12/PER-CS pour PHP, ES6+ pour JavaScript) entre tous les développeurs du projet, le formatage manuel ou l'oubli de formatage doit être éradiqué.

## Décision
Le fichier `.vscode/settings.json` est partagé au sein du dépôt pour automatiser le cycle de vie du code :
1. **`editor.formatOnSave: true`** : Devient obligatoire. Chaque écriture sur le disque déclenche le linter/formateur.
2. **Assignation des Formateurs** :
   * PHP : Délégué à `Intelephense` (aligné sur les standards PER/PSR).
   * Web (JS, CSS, JSON) : Délégué aux outils natifs de l'IDE.
3. **Hygiène du code** : Nettoyage automatique des espaces en fin de ligne et injection d'une ligne finale vide (`insertFinalNewline`).

## Justification
Cette approche élimine les revues de code (Pull Requests) polluées par des conflits d'indentation ou des retours à la ligne intempestifs. Le style est appliqué à la racine, garantissant la propreté du dépôt Git (KISS).
-e
=== END_FILE ===

=== FILE: docs/adr/0037-hebergement-local-dependances-frontend.md ===
# ADR 0037 : Hébergement Local des Dépendances Front-end (Abandon des CDN)

**Date :** 09 Juillet 2026
**Statut :** Accepté

## Contexte
L'application utilise des librairies JavaScript et CSS tierces (ex: Tabulator, Bootstrap, FontAwesome). Historiquement, le chargement via CDN (Content Delivery Network comme `unpkg` ou `cdnjs`) était privilégié pour économiser la bande passante et bénéficier du cache partagé des navigateurs.

## Décision
1. **Abandon des CDN** : À terme, l'utilisation de liens CDN externes sera proscrite dans le code source (Layouts, Helpers, Vues).
2. **Hébergement Local (Assets)** : Toutes les librairies tierces nécessaires au front-end doivent être téléchargées physiquement et stockées dans le répertoire `webroot/assets/` (pour éviter tout conflit Git avec le dossier `vendor` de Composer).
3. **Lazy Loading** : Ces ressources locales seront injectées dynamiquement via les Helpers CakePHP ou importées via les modules ES6, uniquement sur les pages qui en ont l'usage.

## Justification
* **Conflits Git/Composer** : L'utilisation du répertoire `assets` contourne proprement les règles `.gitignore` natives de l'écosystème PHP.
* **RGPD et Confidentialité** : Le chargement de ressources depuis un CDN expose l'adresse IP de nos utilisateurs à des services tiers sans leur consentement explicite. Le chargement local garantit que les données de navigation restent confinées à notre infrastructure.
* **Sécurité (Supply Chain)** : Héberger localement immunise l'application contre le piratage ou la corruption d'un service CDN externe.
* **Environnement de Développement** : Permet aux développeurs de travailler sur l'application (via Docker) en mode totalement hors-ligne.
* **Fin du Cache Partagé** : Les navigateurs modernes ayant partitionné leur cache par domaine, le gain de performance historique des CDN a disparu.
-e
=== END_FILE ===

=== END_FILE ===

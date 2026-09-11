
# Architecture Decision Records (ADR)

Ce répertoire centralise les décisions d'architecture (ADR) structurant l'application. Chaque choix technologique, règle métier transversale ou orientation de design système fait l'objet d'un document numéroté afin de conserver un historique clair, traçable et partagé de nos choix techniques.

---

## Index Général des Décisions d'Architecture (ADR 0001 à 0049)

| Référence | Sujet Technique / Décision | Statut | Fichier Source |
| :--- | :--- | :---: | :--- |
| **ADR 0001** | Choix de l'infrastructure Docker pour le développement | **Accepté** | [`0001-choix-infrastructure-docker.md`](./0001-choix-infrastructure-docker.md) |
| **ADR 0002** | Gestion de la configuration et connexion MySQL (12-Factor App) | **Accepté** | [`0002-connexion-base-de-donnees.md`](./0002-connexion-base-de-donnees.md) |
| **ADR 0003** | Configuration de l'IDE (VSCodium) avec l'isolation Docker | **Accepté** | [`0003-configuration-ide-vscodium.md`](./0003-configuration-ide-vscodium.md) |
| **ADR 0004** | Architecture de l'API Tabulator et Patrons de Conception GoF | **Accepté** | [`0004-api-tabulator-et-design-patterns.md`](./0004-api-tabulator-et-design-patterns.md) |
| **ADR 0005** | Modélisation de l'architecture de la Base de Données MySQL | **Accepté** | [`0005-modele-de-donnees.md`](./0005-modele-de-donnees.md) |
| **ADR 0006** | Traitement manuel des Vues SQL dans les Migrations Phinx | **Accepté** | [`0006-gestion-des-vues-dans-les-migrations.md`](./0006-gestion-des-vues-dans-les-migrations.md) |
| **ADR 0007** | Gestion des accès Git (Multi-comptes SSH par Alias) | **Accepté** | [`0007-configuration-git-ssh.md`](./0007-configuration-git-ssh.md) |
| **ADR 0008** | Stratégie de génération ORM (Contournement CakePHP Bake) | **Accepté** | [`0008-limitation-generation-orm.md`](./0008-limitation-generation-orm.md) |
| **ADR 0009** | Implémentation du module Utilisateurs via API et Tabulator | **Accepté** | [`0009-implementation-tabulator-users.md`](./0009-implementation-tabulator-users.md) |
| **ADR 0010** | Procédure de vérification de l'intégrité de l'ORM via le REPL | **Accepté** | [`0010-verification-integrite-orm.md`](./0010-verification-integrite-orm.md) |
| **ADR 0011** | Droits d'écriture sur le répertoire personnel de www-data | **Accepté** | [`0011-droits-home-www-data.md`](./0011-droits-home-www-data.md) |
| **ADR 0012** | Modélisation manuelle des relations bidirectionnelles multiples | **Accepté** | [`0012-relations-multiples-orm.md`](./0012-relations-multiples-orm.md) |
| **ADR 0013** | Patrons de conception pour le front-end JavaScript (Tabulator) | **Accepté** | [`0013-architecture-frontend-js.md`](./0013-architecture-frontend-js.md) |
| **ADR 0014** | Standardisation des fonctionnalités de base des grilles | **Accepté** | [`0014-standardisation-fonctionnalites-tabulator.md`](./0014-standardisation-fonctionnalites-tabulator.md) |
| **ADR 0015** | Mapping dynamique des champs de tri entre JSON et ORM | **Accepté** | [`0015-mapping-champs-tri-orm.md`](./0015-mapping-champs-tri-orm.md) |
| **ADR 0016** | Organisation des Services de Données et Cohérence PSR-4 | **Accepté** | [`0016-coherence-psr4-autoloading.md`](./0016-coherence-psr4-autoloading.md) |
| **ADR 0017** | Collision des paramètres de requête entre Tabulator et CakePHP | **Accepté** | [`0017-collision-parametres-pagination.md`](./0017-collision-parametres-pagination.md) |
| **ADR 0018** | Mapping des opérateurs de filtrage Tabulator vers ORM CakePHP | **Accepté** | [`0018-mapping-filtres-tabulator-orm.md`](./0018-mapping-filtres-tabulator-orm.md) |
| **ADR 0019** | Internationalisation (i18n) et Ergonomie Transversale des Grilles | **Accepté** | [`0019-i18n-et-ergonomie-des-grilles.md`](./0019-i18n-et-ergonomie-des-grilles.md) |
| **ADR 0020** | Standardisation et Automatisation du Formatage de Code via l'IDE | **Accepté** | [`0020-automatisation-formatage-ide.md`](./0020-automatisation-formatage-ide.md) |
| **ADR 0021** | Emplacement du répertoire de documentation (Contexte de Boilerplate) | **Accepté** | [`0021-arborescence-documentation-applicative.md`](./0021-arborescence-documentation-applicative.md) |
| **ADR 0022** | Standardisation de la colonne d'actions (DataGrids) | **Accepté** | [`0022-standardisation-colonne-actions-ui.md`](./0022-standardisation-colonne-actions-ui.md) |
| **ADR 0023** | Découplage des éléments UI via les patrons Builder et Factory | **Accepté** | [`0023-factory-boutons-actions-ui.md`](./0023-factory-boutons-actions-ui.md) |
| **ADR 0024** | Résolution du conflit de positionnement des dropdowns d'en-tête | **Accepté** | [`0024-contournement-conflit-positionnement-dropdown.md`](./0024-contournement-conflit-positionnement-dropdown.md) |
| **ADR 0025** | Routage Dynamique Polymorphique des Actions de Ligne Tabulator | **Accepté** | [`0025-routage-dynamique-metadonnees-dropdown.md`](./0025-routage-dynamique-metadonnees-dropdown.md) |
| **ADR 0026** | Contrôle d'Accès Visuel et Structurel Unifié des Grilles via grid_rights | **Accepté** | [`0026-controle-acces-visuel-grid-rights.md`](./0026-controle-acces-visuel-grid-rights.md) |
| **ADR 0027** | Gestion Dynamique des Messages Flash et Suppressions Asynchrones | **Accepté** | [`0027-gestion-messages-flash-dynamiques.md`](./0027-gestion-messages-flash-dynamiques.md) |
| **ADR 0028** | Infrastructure d'Authentification Évolutive (Google OAuth Ready) | **Proposé** | [`0028-authentification-evolutive-et-impersonate.md`](./0028-authentification-evolutive-et-impersonate.md) |
| **ADR 0029** | Workflow Git : Le Feature Branch Flow | **Accepté** | [`0029-workflow-git-feature-branch.md`](./0029-workflow-git-feature-branch.md) |
| **ADR 0030** | Modernisation de l'infrastructure front-end via les modules ES6 | **Accepté** | [`0030-modernisation-scripts-modules-es6.md`](./0030-modernisation-scripts-modules-es6.md) |
| **ADR 0031** | Stratégie d'exemption de sécurité pour les outils de dev (DebugKit) | **Accepté** | [`0031-exemption-securite-debugkit.md`](./0031-exemption-securite-debugkit.md) |
| **ADR 0032** | Standardisation du flux de travail de développement | **Accepté** | [`0032-flux-de-travail-developpement.md`](./0032-flux-de-travail-developpement.md) |
| **ADR 0033** | Architecture de la couche Mailer (Principe DRY et SoC) | **Accepté** | [`0033-implementation-mailer-reinitialisation-mdp.md`](./0033-implementation-mailer-reinitialisation-mdp.md) |
| **ADR 0034** | Spécialisation et Routage des Logs de Courriels | **Accepté** | [`0034-specialisation-logs-courriels.md`](./0034-specialisation-logs-courriels.md) |
| **ADR 0035** | Simplification de l'écriture des Logs (EmailLoggerTrait) | **Accepté** | [`0035-trait-specialise-logs-email.md`](./0035-trait-specialise-logs-email.md) |
| **ADR 0036** | Encapsulation de l'expédition SMTP (AppMailer::safeSend) | **Accepté** | [`0036-encapsulation-expedition-email-safesend.md`](./0036-encapsulation-expedition-email-safesend.md) |
| **ADR 0037** | Hébergement Local des Dépendances Front-end (Abandon des CDN) | **Accepté** | [`0037-hebergement-local-dependances-frontend.md`](./0037-hebergement-local-dependances-frontend.md) |
| **ADR 0038** | Confinement de la hauteur des grilles de données (Tabulator) | **Accepté** | [`0038-confinement-hauteur-grilles-tabulator.md`](./0038-confinement-hauteur-grilles-tabulator.md) |
| **ADR 0039** | Défilement Infini (Progressive Loading) au lieu de la Pagination | **Accepté** | [`0039-chargement-progressif-scroll-infini.md`](./0039-chargement-progressif-scroll-infini.md) |
| **ADR 0040** | Mécanisme d'usurpation d'identité sécurisé (Impersonate) | **Accepté** | [`0040-mecanisme-usurpation-identite-impersonate.md`](./0040-mecanisme-usurpation-identite-impersonate.md) |
| **ADR 0041** | Ségrégation des Données (RLS) Centralisée via Custom Finders | **Accepté** | [`0041-segregation-donnees-model-custom-finders.md`](./0041-segregation-donnees-model-custom-finders.md) |
| **ADR 0042** | ACL Granulaire au Niveau du Champ de Formulaire | **Accepté** | [`0042-acl-granulaire-niveau-champ-formulaire.md`](./0042-acl-granulaire-niveau-champ-formulaire.md) |
| **ADR 0043** | Commande CLI de test d'envoi de courriels et serveur Mailpit | **Accepté** | [`0043-commande-test-envoi-email.md`](./0043-commande-test-envoi-email.md) |
| **ADR 0044** | Recherche FULLTEXT MySQL via Callback FriendsOfCake/Search | **Accepté** | [`0044-filtre-recherche-fulltext-search-plugin.md`](./0044-filtre-recherche-fulltext-search-plugin.md) |
| **ADR 0045** | Administration CRUD de la sécurité des champs (FieldAuthorizations) | **Accepté** | [`0045-gestion-crud-field-authorizations.md`](./0045-gestion-crud-field-authorizations.md) |
| **ADR 0046** | Standardisation du CRUD hybride (FieldAuthorizations) | **Accepté** | [`0046-standardisation-crud-field-authorizations.md`](./0046-standardisation-crud-field-authorizations.md) |
| **ADR 0047** | Découpage de l'IHM Applicationform en 5 zones fonctionnelles | **Accepté** | [`0048-decoupage-ihm-zones-applicationform.md`](./0048-decoupage-ihm-zones-applicationform.md) |
| **ADR 0048** | Centralisation de l'identité dans AppView et gestion des scripts | **Accepté** | [`0047-normalisation-vues-identity-assets-js.md`](./0047-normalisation-vues-identity-assets-js.md) |
| **ADR 0049** | Intégration de TreeselectJS pour les structures hiérarchiques | **Accepté** | [`0049-integrationtreeselectjs_pour_structures_hierarchiques_departments.md`](./0049-integrationtreeselectjs_pour_structures_hierarchiques_departments.md) |
| **ADR 0050** | Sémantique des périmètres hiérarchiques utilisateur | **Proposé** | [`0050-semantique-perimetres-hierarchiques-utilisateur.md`](./0050-semantique-perimetres-hierarchiques-utilisateur.md) |
| **ADR 0051** | Français comme langue applicative par défaut | **Proposé** | [`0051-francais-langue-applicative-par-defaut.md`](./0051-francais-langue-applicative-par-defaut.md) |

---

## Guide de Gouvernance : Cycle de vie d’un ADR

Pour garantir la pérennité technique et l’intégrité de notre base de code, toute évolution d'envergure ou introduction de patron de conception doit s'adosser à un ADR.

### 1. Structure obligatoire d'un enregistrement
Chaque enregistrement doit respecter rigoureusement le gabarit suivant :
*   **Titre** : Numéro d'ADR incrémental + intitulé clair.
*   **Méta-données** : Date de rédaction, Statut actuel (*Proposé / Accepté / Rejeté / Obsolète*) et Dépendances (liens vers les autres ADR concernés).
*   **Contexte** : Description factuelle de la problématique et justifications des limites techniques rencontrées.
*   **Décisions** : Actions concrètes à mener (conventions de nommage, architecture logicielle, règles BDD et IHM).
*   **Justifications** : Justifications rationnelles s'appuyant sur les principes **SOLID**, **DRY** et **KISS**.
*   **Conséquences** : Impact direct (*Positif et Négatif*) sur le projet, l'équipe et les performances.

### 2. Flux de soumission d’un ADR (Workflow)
1.  **Création de la branche** : Travailler dans une branche isolée (`feature/adr-sujet` ou `fix/adr-correction`).
2.  **Numérotation** : Récupérer le dernier numéro de l'index ci-dessus et incrémenter de 1 (ex: `0050`).
3.  **Rédaction physique** : Créer le fichier Markdown nommé sous la forme `XXXX-titre-de-l-adr.md` sous le dossier `app/docs/adr/`.
4.  **Mise à jour de l'index** : Ajouter la nouvelle ligne correspondante dans ce fichier `README.md` avec le statut `Proposé`.
5.  **Revue & Validation** : Soumettre l'ADR à la validation de l'équipe (via Pull Request). Une fois fusionné dans `main`, le statut passe à `Accepté`.

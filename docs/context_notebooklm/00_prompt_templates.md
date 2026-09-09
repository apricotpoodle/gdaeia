
Rôle : Recueil centralisé des cinq modèles de prompts pré-configurés (cadrage, service, contrôleur, tests, commit) sous forme de fichier Markdown unique dans Git pour un accès rapide.

# Modèles de Prompts pour Gemini Web

## Template 1 — Cadrage Métier (NotebookLM)
Agis comme l'architecte de notre projet. Je dois développer la fonctionnalité suivante : [DÉCRIRE LA FONCTIONNALITÉ].
À partir des documents fournis (ADR, schéma SQL, documentation métier), fournis-moi une synthèse technique contenant :
1. Les règles de gestion strictes (ADR associées).
2. Les tables BDD impactées.
3. Les champs spécifiques à lire/mettre à jour.
4. Les contraintes de sécurité/intégrité (transactions, cascade).

## Template 2 — Logic Service (Gemini Raisonnement)
Agis comme un développeur expert PHP 8 et CakePHP 5. Je dois créer un Service métier appelé [NOM_SERVICE].
Contraintes issues de la doc :
[COLLER ICI LA SYNTHÈSE NOTEBOOKLM]
Rédige la classe complète avec typage strict et transactions ORM.

## Template 3 — Contrôleur / Route (Gemini Pro)
Voici le code du service métier : [COLLER CODE SERVICE].
Rédige l'action de contrôleur [NOM_ACTION] pour [NOM_CONTROLLER] sous CakePHP 5 (gestion POST/PUT, Flash messages et redirections).

## Template 4 — Tests Unitaires & Fixtures (Gemini Flash)
Voici la classe à tester : [COLLER CODE].
Rédige la classe de test PHPUnit (`TestCase`) avec fixtures, cas nominal et cas d'échec.

## Template 5 — Message de Commit Git (Gemini Flash)
Voici le `git diff` de mes modifications :
[COLLER GIT DIFF]
Rédige un message de commit au format Conventional Commits en français.

# Template 6 — Audit de Cohérence et Détection de Frictions (NotebookLM)

Agis comme un Tech Lead et Architecte Logiciel senior chargé d'évaluer la qualité de notre documentation technique.

Analyse l'intégralité des sources fournies dans ce notebook (ADR, Schéma BDD, Migrations, Documentation API, Standards) et réalise un audit de cohérence pour identifier :

1. **Contradictions de règles métier** : Existe-t-il des règles définies dans un ADR ou un README qui contredisent le comportement d'une migration BDD ou d'un statut ?
2. **Incohérences de nommage et typage** :
   - Des noms de tables/champs qui diffèrent entre les migrations et la documentation.
   - Des types de données incohérents (ex: un ID signé vs non-signé, des formats de date incompatibles, des longueurs de chaînes différentes).
3. **Frictions d'architecture** :
   - Des dépendances circulaires ou des couplages forts non justifiés.
   - Des règles de validation BDD non alignées avec les contrôles d'autorisation (Policies / FieldAuthorizations).
4. **Doublons ou lacunes** : Des règles redondantes exprimées de deux manières différentes, ou des zones d'ombre dans le workflow de validation.

Formate ton rapport sous la forme suivante :
- **Synthèse globale** : (Vert / Orange / Rouge + résumé en 2 phrases).
- **Tableau des frictions détectées** :
  | Type de friction | Fichiers / Sources concernés | Description du conflit | Impact potentiel | Correction recommandée |
- **Recommandations prioritaires** : Liste à puces des ajustements à effectuer dans les sources.

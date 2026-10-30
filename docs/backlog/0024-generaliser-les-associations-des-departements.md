# 0024 — Généraliser les associations des départements

**Statut :** Terminé

**Priorité :** Haute — chantier prioritaire

## Contexte

Le modèle actuel limite les départements à un responsable unique via
`departments.current_manager_id`. Les codes CGR sont également rattachés à un
seul département via `cgr_codes.department_id`, tandis que le département
porte plusieurs représentations d'un CGR par défaut (`cgr_code_id` et
`default_cgr`).

Ces relations directes empêchent de représenter les situations où plusieurs
responsables travaillent sur un même département ou lorsqu'un même code CGR
est utilisé par plusieurs départements. La notion de CGR par défaut doit être
supprimée : un département pourra n'avoir aucun code CGR, en avoir un seul ou
en avoir plusieurs.

## Objectif

Normaliser les relations entre départements, utilisateurs et codes CGR afin
de permettre des associations multiples, partageables et administrables
depuis la fiche département, sans introduire de responsable principal ni de
CGR principal.

## Décisions fonctionnelles

- Un département peut être associé à zéro, un ou plusieurs utilisateurs
  responsables ; aucune priorité n'est portée par l'association.
- Un code CGR appartient à un catalogue partagé et peut être associé à zéro,
  un ou plusieurs départements.
- L'unicité d'un code CGR est globale sur le couple `type` + `code`.
- Il n'existe plus de CGR par défaut, de CGR principal ou de valeur textuelle
  pré-calculée équivalente.
- Les droits de lecture, d'association, de dissociation et de modification
  restent évalués côté serveur par les Policies et les autorisations de
  champs existantes.

## Travail attendu

### Modèle de données et migration

- [x] Créer `department_managers` avec `department_id`, `user_id`, une clé
  primaire composite et les contraintes étrangères adaptées.
- [x] Créer `department_cgr_codes` avec `department_id`, `cgr_code_id`, une
  clé primaire composite et les contraintes étrangères adaptées.
- [x] Supprimer `departments.current_manager_id`, `departments.cgr_code_id`,
  `departments.default_cgr` et `cgr_codes.department_id`.
- [x] Remplacer l'index d'unicité des CGR par une unicité globale sur
  `cgr_codes.type` et `cgr_codes.code`.
- [x] Migrer chaque valeur non nulle de `current_manager_id` vers
  `department_managers`.
- [x] Migrer les rattachements de `cgr_codes.department_id` et de
  `departments.cgr_code_id` vers `department_cgr_codes`, en dédoublonnant les
  couples identiques.
- [x] Produire avant suppression un contrôle des valeurs non vides de
  `default_cgr`, puis documenter leur abandon conformément à la décision de
  supprimer toute notion de CGR par défaut.
- [x] Prévoir une migration MySQL reprenable et vérifier les contraintes,
  index et comportements de suppression sur une base MySQL de test.

### ORM et services métier

- [x] Remplacer les relations directes de `DepartmentsTable` et
  `CgrCodesTable` par les relations Many-to-Many nommées explicitement.
- [x] Mettre à jour les entités, constantes, propriétés PHPDoc, champs
  accessibles, validations, règles d'existence et catalogue d'intégrité.
- [x] Adapter `DepartmentManagementService` pour enregistrer les deux listes
  d'associations dans une transaction et gérer la suppression d'une liaison
  sans supprimer l'utilisateur, le département ou le code CGR.
- [x] Adapter `CgrResolverService`, les finders, les imports et tout service
  consommant le rattachement direct d'un CGR.
- [x] Supprimer les lectures, calculs et libellés liés à `default_cgr`.
- [x] Vérifier les associations compilées avec la procédure de l'ADR 0010.

### API et interface d'administration

- [x] Remplacer le champ de responsable unique de la fiche département par
  une sélection multiple des utilisateurs responsables.
- [x] Permettre depuis la fiche département d'associer et de dissocier
  plusieurs codes du catalogue CGR partagé.
- [x] Séparer dans l'API la gestion du catalogue global CGR de la gestion des
  associations entre un département et ses codes.
- [x] Adapter les réponses JSON, les routes imbriquées, les grilles
  Tabulator et les scripts de vue sans introduire de JavaScript inline.
- [x] Supprimer les colonnes, filtres, champs et textes « CGR par défaut ».
- [x] Conserver les contrôles d'autorisation sur la fiche, le catalogue et
  chaque mutation d'association.
- [x] Prévoir les états vides, les doublons, les codes inactifs et les
  erreurs de validation dans les écrans et les réponses API en français.

### Tests et documentation

- [x] Mettre à jour les fixtures et ajouter les tests d'intégration des deux
  tables de liaison et des contraintes d'unicité.
- [x] Tester la migration sur des données contenant un responsable unique,
  plusieurs rattachements CGR, des doublons et des valeurs `default_cgr`.
- [x] Ajouter les tests unitaires des services et les tests fonctionnels HTTP
  des associations, des autorisations et des erreurs de validation.
- [x] Ajouter ou adapter les tests d'écran et de grille concernés.
- [x] Ajouter un ADR applicatif proposé décrivant le modèle cible et mettre à
  jour les ADR 0005 et 0012 si nécessaire, sans modifier rétroactivement une
  décision acceptée pour masquer l'historique.
- [x] Mettre à jour les README locaux et la documentation du backlog.

## Critères d'acceptation

1. Les quatre colonnes supprimées ne figurent plus dans le schéma final.
2. Un département peut posséder zéro, un ou plusieurs responsables, sans
   responsable principal implicite.
3. Un code CGR peut être associé à plusieurs départements et un département
   peut posséder plusieurs codes CGR.
4. Un doublon d'association département/utilisateur ou
   département/code-CGR est impossible au niveau base et ORM.
5. Les codes CGR ayant le même `type` et `code` sont uniques dans le
   catalogue global.
6. Les associations existantes sont reprises automatiquement et sans
   doublon ; les valeurs `default_cgr` sont contrôlées puis abandonnées.
7. Aucun endpoint, service ou écran ne fait référence à un CGR par défaut.
8. Les associations sont modifiables depuis la fiche département et toutes
   les mutations sont protégées côté serveur.
9. Les erreurs API respectent le contrat `{success, message, errors}` et
   utilisent HTTP 422 lorsque la validation échoue.
10. Les tests de non-régression, d'intégration ORM, d'API et de workflow
    adaptés au périmètre sont verts.

## Vérifications attendues

- `make test.unit`
- `make test.integration`
- `make test.api`
- `make test.workflow` si une vue SQL ou un calcul de workflow dépend des
  relations modifiées
- `make test.style`
- `make cs.check`
- `make stan`
- `make test.all` avant livraison de l'évolution transversale
- vérification REPL des associations ORM conformément à l'ADR 0010

## Découpage en sous-tickets

1. ADR et migration du schéma avec reprise contrôlée des données.
2. Associations ORM, entités, validations et services métier.
3. API, Policies et autorisations des associations.
4. Refonte de la fiche département, du catalogue CGR et des grilles.
5. Tests, documentation et recette MySQL complète.

## Références

- [ADR 0005 — Modèle de données](../adr/0005-modele-de-donnees.md)
- [ADR 0010 — Vérification de l'intégrité ORM](../adr/0010-verification-integrite-orm.md)
- [ADR 0012 — Relations multiples ORM](../adr/0012-relations-multiples-orm.md)
- [ADR 0041 — Ségrégation des données par custom finders](../adr/0041-segregation-donnees-model-custom-finders.md)
- [ADR 0045 — Gestion des autorisations de champs](../adr/0045-gestion-crud-field-authorizations.md)
- [ADR 0064 — Administration de l'arbre des départements](../adr/0064-administration-arbre-departements.md)
- `src/Model/Table/DepartmentsTable.php`
- `src/Model/Table/CgrCodesTable.php`
- `src/Service/DepartmentManagementService.php`
- `templates/Departments/form.php`

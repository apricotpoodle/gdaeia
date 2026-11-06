# Module Modèles et Tables (`src/Model/Table`)

Ce répertoire contient la couche d'accès aux données (ORM CakePHP).

## Filtres et Recherches
* **`ApplicationformsTable`** : Intègre le Behavior `FriendsOfCake/Search` avec un filtre callback configuré en `MATCH() AGAINST() IN BOOLEAN MODE` sur l'index FULLTEXT multi-colonnes (`jobtitle`, `applicantname`, `qualification`, `reasonforreplacement`).
* **`WorkflowSettingsTable`** et **`ValidationCommentTemplatesTable`** : Portent respectivement les paramètres globaux du workflow, dont l'obligation de commenter par décision, et le catalogue administrable des commentaires prédéfinis. Le modèle sélectionné préremplit le champ libre ; seul le texte final du vote est conservé dans `validations.obs`.
* **`DepartmentsTable`** : Fournit le finder `activeChildren()` utilisé par
  l'arbre d'administration, porte les validations, unicités et règles
  d'intégrité du référentiel des départements, et expose les associations
  multiples `Managers` et `CgrCodes`.
* **`DepartmentManagersTable`** et **`DepartmentCgrCodesTable`** : Portent
  les clés composites et les règles d'existence des tables de liaison ; les
  associations ne suppriment jamais les utilisateurs ou codes référencés.
* **`CgrCodesTable`** : Gère le catalogue CGR global, unique sur `type` et
  `code`, associé aux départements par `department_cgr_codes`.

## Erreurs de validation

Les Tables restent la source des règles de validation et d'intégrité. Leurs messages métier, notamment les refus de doublons pour les autorisations de champs, les associations rôle-menu, les séquences de validation et les départements d'un utilisateur, sont rédigés en français. `ValidationErrorPresenter` présente ces erreurs sans les définir à la place des Tables.

## ADRs Associés
* [ADR 0044 : Recherche FULLTEXT MySQL via Callback avec Search](../../docs/adr/0044-filtre-recherche-fulltext-search-plugin.md)
* [ADR 0052 : Présentation unifiée des erreurs de validation Web et API](../../docs/adr/0052-presentation-unifiee-erreurs-validation-web-api.md)

## Habilitations locales et conservation des cycles

`Departments::findCircuitManageableTo()` exige pour un non-super-administrateur
un rôle actif habilité dans `validation_circuit_managers` et un rattachement
explicite dans `user_departments`. Aucun descendant ni privilège Admin implicite
n'est déduit. `ValidationCircuitRules::findVisibleTo()` réutilise ce périmètre.
Les désignations et les règles possèdent leurs contraintes d'unicité et de
références ; la validité opérationnelle est contrôlée par le service partagé.

`Applicationforms::isCircuitLocked()` et le contrôle `beforeSave` interdisent
un changement de département ou de contrat dès qu'un cycle existe, quel que
soit son état. Seule sa remise à zéro autorisée lève ce verrou.

# ADR 0064 — Administration de l'arbre des départements

- Date : 2026-10-15
- Statut : Accepté
- Dépendances : ADR 0004, ADR 0013, ADR 0026, ADR 0041 et ADR 0054

## Contexte

Le référentiel des départements est hiérarchique et porte une stratégie CGR,
des codes associés et des dépendances avec les utilisateurs et le workflow.
Une simple grille CRUD ne permettrait ni de charger l'arbre progressivement,
ni d'évaluer l'impact d'une désactivation ou d'un déplacement.

## Décision

- L'écran Web `/departments` est protégé par `DepartmentPolicy` ; son point
  d'extension permet d'élargir ultérieurement l'accès à des opérateurs ciblés.
- L'arbre est rendu par Tabulator avec chargement lazy des enfants via l'API.
- `DepartmentManagementService` centralise les cycles, impacts, dépendances
  bloquantes et transactions.
- La suppression est une désactivation logique (`deleted`). Les départements
  socle ne sont pas désactivables ; les codes CGR système ne sont pas
  modifiables ni désactivables.
- Les codes CGR sont exposés par des routes API imbriquées et leurs droits
  sont évalués par `CgrCodePolicy`.
- Les actions et droits de ligne utilisent les builders/factories Tabulator et
  `grid_rights`; aucun JavaScript métier n'est inline dans les templates.

## Justification

Cette séparation respecte « fat models, skinny controllers », évite de
dupliquer les règles d'impact entre Web et API et conserve les conventions
Tabulator et Policy déjà utilisées dans l'application.

## Conséquences

Les opérations dangereuses sont explicites et contrôlées avant mutation, au
prix de quelques appels API supplémentaires pour l'impact et le chargement des
enfants. Les autorisations restent centralisées et pourront évoluer sans
réécrire l'écran.

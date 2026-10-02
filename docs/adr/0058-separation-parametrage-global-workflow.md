# ADR 0058 : Séparation du paramétrage global du workflow

**Date :** 18 septembre 2026  
**Statut :** Accepté  
**Dépendances :** [ADR 0004](./0004-api-tabulator-et-design-patterns.md), [ADR 0022](./0022-standardisation-colonne-actions-ui.md), [ADR 0054](./0054-commandes-ui-autorisees-par-domaine.md), [ADR 0055](./0055-workflow-validation-applicationforms.md)

## Contexte

L’écran `/validationsequences` regroupe actuellement deux responsabilités :
la configuration des rôles par département et le paramétrage global du
workflow. Le délai global de validation et le catalogue de commentaires
prédéfinis ne dépendent pourtant d’aucun département. Leur présence dans cet
écran rend l’intention de chaque configuration moins lisible.

Le catalogue est en outre rendu directement par le script de la page, sans
grille Tabulator ni traitement distant, contrairement aux conventions des ADR
0004 et 0022.

## Décision

1. L’URL `/validationsequences` est limitée aux affectations département,
   rôle, ordre et délai propre à une séquence.
2. L’écran `/workflow-settings` devient le point d’entrée dédié au délai par
   défaut, aux commentaires prédéfinis et aux règles d’obligation de
   commentaire du workflow.
3. Son API est placée sous `/api/workflow-settings`. Elle expose le délai à
   `GET|POST /default-due-hours.json`, les paramètres booléens
   `validation.comment_required.accept` et
   `validation.comment_required.reject` via
   `GET|POST /comment-requirements.json`, ainsi qu’une grille distante à
   `GET /comment-templates.json` et les mutations POST de création,
   modification et suppression des commentaires.
4. Les routes précédemment exposées sous `/api/validationsequences` pour ces
   paramètres sont retirées. Elles ne sont consommées que par l’écran déplacé
   et aucun alias de compatibilité n’est conservé.
5. Le catalogue des commentaires utilise Tabulator avec pagination, tri et
   filtrage côté serveur via `TabulatorAdapter`. Les actions de grille
   réutilisent `ButtonFactory`, `TabulatorBuilder` et l’observateur existants.
6. L’écran, chaque lecture et chaque mutation restent protégés côté serveur
   par les Policies du domaine. Les commandes de navigation et les actions
   visibles dans les templates passent par une fabrique `UiAction` dédiée.
7. Les valeurs par défaut des obligations sont `false` pour une acceptation et
   `true` pour un refus. Elles sont contrôlées côté serveur au moment du vote.
   Aucune colonne n’est ajoutée à `validation_workflow_runs` : une modification
   du paramétrage s’applique aussi aux cycles en cours.
8. Un modèle sélectionné dans l’interface préremplit le champ libre de vote.
   Le validateur peut modifier ou remplacer le texte ; seul le commentaire
   final est enregistré dans `validations.obs`, sans identifiant de modèle.
9. L’état de validation expose les règles courantes et les modèles actifs au
   validateur ; l’API d’administration n’est pas utilisée pour alimenter
   l’écran de vote.

## Justification

Cette séparation donne à chaque écran une responsabilité unique et évite de
faire porter à la configuration locale des séquences des paramètres globaux.
La grille distante réutilise les abstractions existantes, conserve le tri et
le filtrage côté serveur et évite une implémentation JavaScript ad hoc.

Les modèles accélèrent la saisie sans devenir une dépendance de l’historique :
le vote conserve le texte effectivement soumis, indépendamment des
modifications ou suppressions ultérieures du catalogue.

La suppression directe des anciennes routes maintient une surface API claire :
aucun client du dépôt ne les consomme hors de la page qui est déplacée. Les
Policies restent le contrôle d’accès effectif ; la visibilité des boutons ne
constitue pas une autorisation.

## Conséquences

### Positives

* Les responsabilités des deux écrans et de leurs APIs sont explicites.
* Le catalogue gagne une pagination, un tri et un filtrage utilisables avec un
  volume de données important.
* Les conventions de grilles, de commandes d’interface et d’autorisation sont
  appliquées de façon homogène.
* L’obligation de commenter peut évoluer sans déploiement applicatif.
* Le commentaire final du vote possède une source de vérité unique.

### Négatives

* Les utilisateurs administrateurs doivent accéder à une nouvelle URL pour
  les paramètres globaux.
* Les consommateurs non référencés des anciennes routes devront adopter la
  nouvelle API.
* Un changement de paramétrage s’applique immédiatement aux cycles déjà en
  cours ; la règle historique n’est pas figée par cycle.

# 0001 — Valider le contrat de gérabilité et les racines

**Statut :** Terminé

**Dépendance :** aucune

## Objectif

Valider l'ADR 0073 et formaliser le calcul des racines explicites d'un
opérateur, y compris lorsqu'il possède plusieurs branches dans
`user_departments`.

## Travail attendu

- Décrire les cas opérateur local, utilisateur parent, branche indépendante,
  superuser et périmètre multi-branches.
- Confirmer que seules les associations présentes en base sont utilisées.
- Décider la représentation de la racine dans les APIs et les services sans
  modifier l'ADR 0050.
- Préparer le passage de l'ADR 0073 de `Proposé` à `Accepté` dans l'étape 5.2,
  après validation humaine du contrat.

## Contrat validé

Le périmètre explicite d'un opérateur non-superuser est représenté par une
liste dédoublonnée d'identifiants de départements. Cette liste est construite
exclusivement à partir des lignes présentes dans `user_departments` ; les
descendants absents de cette table ne sont jamais ajoutés implicitement. Les
services et les APIs échangent ces identifiants, puis la couche ORM charge les
départements actifs et leurs bornes `lft`/`rght` pour les comparaisons.

`findManageableTo()` retourne les utilisateurs visibles dont aucune association
explicite n'est un ancêtre strict d'une racine explicite de l'opérateur. Pour
deux départements `A` et `B`, `A` est un ancêtre strict de `B` si et seulement
si `A.lft < B.lft` et `A.rght > B.rght`. L'égalité sur l'une des bornes ne
constitue donc pas un ancêtre strict : une association sur la racine elle-même
ne suffit pas à rendre la cible non gérable.

La règle est évaluée pour chaque racine de l'opérateur et pour chaque
département explicitement associé à la cible. Elle ne suppose jamais qu'un
périmètre possède une racine unique. Une liste vide de racines pour un
opérateur non-superuser produit un résultat vide. Le superuser conserve le
court-circuit global prévu par les finders de visibilité et de gérabilité.

### Cas contractuels

| Cas | Résultat attendu |
| --- | --- |
| Opérateur local et cible dans son périmètre explicite | Visible et gérable, sous réserve des autres règles de Policy. |
| Cible associée à un parent strict d'une racine opérateur | Visible si elle partage une association visible, mais non gérable. |
| Cible d'une branche indépendante | Non visible et donc non gérable. |
| Opérateur associé à plusieurs branches | Chaque racine est comparée ; une branche bloquante suffit à refuser la gérabilité de la cible. |
| Associations partielles sous une branche | Seules les associations présentes sont comparées ; les descendants absents ne sont pas inférés. |
| Superuser | Accès global selon les règles superuser, sans racine calculée. |
| Opérateur non-superuser sans association | Aucun résultat de périmètre ; aucun accès implicite n'est accordé. |

Les tests d'intégration ORM du ticket 0002 devront couvrir les racines
uniques et multiples, les ancêtres stricts, le nœud racine lui-même, les
associations partielles, les branches indépendantes, le périmètre vide et le
court-circuit superuser. Les tests du finder du ticket 0003 vérifieront en
plus le dédoublonnage et la compatibilité avec la pagination distante.

## Critères d'acceptation

- Le contrat de `findManageableTo()` est non ambigu.
- La règle `lft`/`rght` des ancêtres stricts est documentée.
- Les cas multi-branches sont couverts par des exemples et des tests prévus.

## Décision de suivi

Le contrat est validé pour permettre l'étape 0002, qui implémentera la
résolution ORM des périmètres explicites. L'ADR 0073 reste `Proposé` et sera
soumis à acceptation dans l'étape 5.2, après validation humaine du contrat.


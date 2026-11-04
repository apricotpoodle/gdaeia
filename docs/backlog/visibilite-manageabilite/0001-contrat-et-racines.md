# 0001 — Valider le contrat de gérabilité et les racines

**Statut :** À planifier

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
- Faire passer l'ADR 0073 de `Proposé` à `Accepté` après validation humaine.

## Critères d'acceptation

- Le contrat de `findManageableTo()` est non ambigu.
- La règle `lft`/`rght` des ancêtres stricts est documentée.
- Les cas multi-branches sont couverts par des exemples et des tests prévus.


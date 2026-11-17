# 0034 — Finaliser l'archivage et l'accès aux archives

**Statut :** À planifier

**Priorité :** Haute — conservation et consultation

**Dépendance :** [0033 — Reprendre une DAE pendant son circuit](0033-reprendre-une-dae-pendant-son-circuit.md).

## Objectif

Mettre en œuvre l'archivage et la recherche des demandes archivées selon les
acteurs, délais et règles de conservation confirmés au 0030.

## Travail attendu

- [ ] Réutiliser et compléter l'endpoint API et les règles de Policy
  d'archivage déjà présents ; vérifier leurs rôles et périmètres contre la
  décision du 0030.
- [ ] Ajouter l'action d'archivage aux rôles confirmés, avec Policy serveur
  et transition d'état explicite.
- [ ] Préserver le contenu, les votes, les commentaires, les pièces jointes
  et les dates nécessaires à l'historique.
- [ ] Permettre la recherche et la consultation des demandes archivées aux
  seuls rôles et périmètres autorisés.
- [ ] Définir les interactions de l'archivage avec la duplication, la
  suppression logique et la conservation.
- [ ] Documenter les règles et la procédure d'exploitation.

## Critères d'acceptation

1. Les droits, conditions et délais correspondent aux décisions du 0030.
2. Une demande archivée est en lecture seule, consultable et recherchable
   dans le respect des Policies et des périmètres.
3. L'historique utile n'est pas effacé par l'archivage.
4. Les routes et mutations directes refusent les actions non autorisées.

## Vérifications

- `make test.unit`
- `make test.integration` si modèles, requêtes ou stockage changent
- `make test.api` pour droits, recherche et mutations
- `make test.style` si des tests sont modifiés
- `make cs.check` et `make stan` si du PHP est modifié

## Références

- [ADR 0069 — Habilitations par rôles et périmètres](../adr/0069-habilitations-par-roles-et-perimetres.md)
- [ADR 0055 — Workflow de validation des Applicationforms](../adr/0055-workflow-validation-applicationforms.md)
- [Cahier des charges GDAE V3](../meetings/cahier_des_charges_gdae_v3.md)

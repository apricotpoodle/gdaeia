# 0034 — Finaliser l'archivage et l'accès aux archives

**Statut :** À planifier

**Priorité :** Haute — conservation et consultation

**Dépendance :** [0033 — Reprendre une DAE pendant son circuit](0033-reprendre-une-dae-pendant-son-circuit.md).

## Objectif

Mettre en œuvre l'archivage réversible de tous les types de DAE, à l'unité ou
en masse, puis la recherche des demandes selon les rôles, périmètres, délais
et règles de conservation confirmés au 0030.

## Travail attendu

- [ ] Réutiliser et compléter l'endpoint API et les règles de Policy
  d'archivage déjà présents ; vérifier leurs rôles, périmètres et codes de
  contrats contre la décision du 0030.
- [ ] Ajouter les actions d'archivage et de restauration, à l'unité et en
  masse, aux superusers et aux utilisateurs de rôle ADMIN qui peuvent voir la
  DAE, avec Policy serveur et transitions d'état explicites.
- [ ] Utiliser les constantes publiques de `Contracttype` pour identifier les
  codes de contrat dans les règles d'archivage, sans codes écrits en dur.
- [ ] Préserver le contenu, les votes, les commentaires, les pièces jointes
  et les dates nécessaires à l'historique.
- [ ] Permettre la recherche et la consultation des demandes archivées aux
  seuls rôles et périmètres autorisés. La liste doit exclure les DAE archivées
  par défaut tout en permettant de rechercher parmi toutes les DAE autorisées.
- [ ] Définir les interactions de l'archivage avec la duplication, la
  suppression logique et la conservation.
- [ ] Documenter les règles et la procédure d'exploitation.

## Critères d'acceptation

1. Les droits, conditions et délais correspondent aux décisions du 0030.
2. Toute DAE peut être archivée ou restaurée à l'unité ou en masse, selon les
   droits et conditions décidés.
3. Une DAE archivée est en lecture seule ; sa restauration la rend de nouveau
   active, consultable et modifiable selon les droits habituels.
4. La liste exclut les DAE archivées par défaut et permet de les retrouver
   dans le respect des Policies et des périmètres.
5. L'historique utile n'est pas effacé par l'archivage.
6. Les routes et mutations directes refusent les actions non autorisées.

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

# 0034 — Finaliser l'archivage et l'accès aux archives

**Statut :** À planifier

**Priorité :** Haute — conservation et consultation

**Dépendance :** [0033 — Reprendre une DAE pendant son circuit](0033-reprendre-une-dae-pendant-son-circuit.md).

## Objectif

Mettre en œuvre l'archivage manuel et réversible de tous les types de DAE, à
l'unité ou en masse, en excluant les cycles actifs. La recherche et la
consultation conservent les droits habituels de lecture. Le plan ne prévoit
aucune purge automatique. Le délai d'éligibilité éventuel et la durée de
conservation restent à clarifier (Q-0030-03). Ces points doivent être
confirmés avant de commencer ce ticket.

## Travail attendu

- [ ] Réutiliser et compléter l'endpoint API et les règles de Policy
  d'archivage déjà présents. Vérifier les rôles, périmètres, codes de
  contrats et l'exclusion des DAE dont le cycle est actif.
- [ ] Ajouter les actions d'archivage et de restauration, à l'unité et en
  masse, aux superusers et aux utilisateurs de rôle ADMIN qui peuvent voir la
  DAE, avec Policy serveur et transitions d'état explicites.
- [ ] Utiliser les constantes publiques de `Contracttype` pour identifier les
  codes de contrat dans les règles d'archivage, sans codes écrits en dur.
- [ ] Préserver le contenu, les votes, les commentaires, les pièces jointes
  et les dates nécessaires à l'historique.
- [ ] Appliquer les droits habituels de lecture à la recherche et à la
  consultation des DAE archivées. La liste exclut les DAE archivées par
  défaut et permet de rechercher parmi toutes les DAE autorisées.
- [ ] Définir les interactions de l'archivage avec la duplication, la
  suppression logique et la conservation.
- [ ] Documenter les règles et la procédure d'exploitation.

## Critères d'acceptation

1. Les droits appliquent le cadrage du 0030 et les éventuels délais et
   durées de conservation s'appuient sur la réponse à Q-0030-03.
2. Une DAE de tout type peut être archivée manuellement ou restaurée à
   l'unité ou en masse selon les droits prévus. Un cycle actif empêche
   l'archivage.
3. Une DAE archivée est en lecture seule. Après restauration, elle figure
   à nouveau parmi les demandes non archivées et relève des droits habituels
   de consultation et de modification.
4. La liste exclut les DAE archivées par défaut et permet de les retrouver
   dans le respect des Policies et des périmètres.
5. L'historique est conservé et aucune purge automatique n'est déclenchée.
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
- [Questions métier ouvertes](../meetings/questions-ouvertes.md)

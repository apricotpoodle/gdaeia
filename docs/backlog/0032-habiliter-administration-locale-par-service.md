# 0032 — Habiliter l'administration des services par les rôles ADMIN

**Statut :** À planifier

**Priorité :** Haute — administration métier

**Dépendance :** [0031 — Aligner dates, jours travaillés et rémunération](0031-aligner-dates-jours-travailles-remuneration.md).

## Objectif

Permettre aux utilisateurs de rôle ADMIN d'administrer les responsables,
assistantes, services et CGR des services qu'ils voient.

Le CRUD de l'arbre départemental, le catalogue générique de permissions et les
associations multiples de responsables existent déjà dans les tickets
[0021](0021-implementer-les-habilitations-generiques.md) et
[0024](0024-generaliser-les-associations-des-departements.md). Ce ticket porte sur les affectations métier nécessaires aux DAE ; il ne
recrée pas ces fondations.

## Travail attendu

- [ ] Définir les opérations d'administration accessibles au rôle ADMIN et
  appliquer les périmètres de services visibles avec les custom finders
  existants.
- [ ] Gérer les affectations de responsables, assistantes, services et CGR
  nécessaires à la saisie des DAE, en respectant les associations multiples.
- [ ] Appliquer les mêmes droits aux pages Web, listes API et mutations
  directes ; les contrôles d'interface seuls ne suffisent pas.
- [ ] Garder les parcours du superadministrateur et les autres branches
  organisationnelles conformes aux Policies et à l'ADR 0069.
- [ ] Documenter l'administration locale dans le README du domaine.

## Critères d'acceptation

1. Un utilisateur de rôle ADMIN voit et modifie uniquement les données des
   services qu'il est autorisé à voir.
2. Une requête Web ou API fabriquée hors périmètre est refusée côté serveur.
3. Les responsables multiples restent pris en charge ; l'application
   n'utilise pas `applicationforms.manager_id` comme responsable unique.
4. L'administration des autres périmètres n'est pas élargie.

## Vérifications

- `make test.unit`
- `make test.integration` pour les associations et finders
- `make test.api` pour les Policies et mutations Web/API
- `make test.style` si des tests sont modifiés
- `make cs.check` et `make stan` si du PHP est modifié

## Références

- [ADR 0064 — Administration de l'arbre des départements](../adr/0064-administration-arbre-departements.md)
- [ADR 0069 — Habilitations par rôles et périmètres](../adr/0069-habilitations-par-roles-et-perimetres.md)
- [ADR 0072 — Associations multiples de départements et CGR](../adr/0072-associations-multiples-departements-cgr.md)
- [ADR 0074 — Articulation habilitations locales et circuits courts](../adr/0074-articulation-habilitations-locales-circuits-courts.md)

# 0032 — Habiliter l'administration des services par les rôles ADMIN

**Statut :** Terminé

**Priorité :** Haute — administration métier

**Dépendance :** [0031 — Aligner dates, jours travaillés et rémunération](0031-aligner-dates-jours-travailles-remuneration.md).

## Objectif

Permettre aux utilisateurs de rôle ADMIN de gérer les affectations de
responsables, d'assistantes et de codes CGR existants aux services qu'ils
voient. Les comptes, rôles, services et le catalogue CGR global sont hors
du périmètre de cette administration locale.

Le CRUD de l'arbre départemental, le catalogue générique de permissions et les
associations multiples de responsables existent déjà dans les tickets
[0021](0021-implementer-les-habilitations-generiques.md) et
[0024](0024-generaliser-les-associations-des-departements.md). Ce ticket porte sur les affectations métier nécessaires aux DAE ; il ne
recrée pas ces fondations.

## Travail attendu

- [x] Définir les opérations d'administration accessibles au rôle ADMIN et
  appliquer les périmètres de services visibles avec les custom finders
  existants.
- [x] Gérer les affectations d'utilisateurs et de CGR nécessaires à la saisie
  des DAE, en respectant les associations multiples du service.
- [x] Conserver la structure et les valeurs existantes de
  `applicationforms.manager_id` sans migration. Retirer tout usage
  applicatif du champ, y compris comme destinataire secondaire de courriel
  (Q-0030-06).
- [x] Appliquer les mêmes droits aux pages Web, listes API et mutations
  directes ; les contrôles d'interface seuls ne suffisent pas.
- [x] Garder les parcours du superadministrateur et les autres branches
  organisationnelles conformes aux Policies et à l'ADR 0069.
- [x] Documenter l'administration locale dans le README du domaine.

## Critères d'acceptation

1. Un utilisateur de rôle ADMIN gère uniquement les affectations
   d'utilisateurs et de CGR existants aux services qu'il est autorisé à voir.
2. Une requête Web ou API fabriquée hors périmètre est refusée côté serveur.
3. L'application détermine les responsables d'une DAE à partir des
   associations multiples du service. Aucun parcours n'utilise
   `applicationforms.manager_id`, y compris pour adresser une copie ou un
   courriel secondaire.
4. L'administration des autres périmètres n'est pas élargie.

## Vérifications

- `make test.unit`
- `make test.integration` pour les associations et finders
- `make test.api` pour les Policies et mutations Web/API
- `make test.style` si des tests sont modifiés
- `make cs.check` et `make stan` si du PHP est modifié

Résultats du ticket : `make test.unit`, `make test.style`, `make cs.check` et
`make stan` réussissent. Les scénarios concernés de `make test.api` réussissent,
mais la suite complète conserve des erreurs dans `PermissionsControllerTest` :
la base `daetf2_test` n'a pas la colonne `menus.required_permission_id`.
`make test.integration` conserve aussi trois erreurs liées à cette même
colonne ; les autres scénarios, dont la duplication et les associations de
départements, réussissent.

## Références

- [ADR 0064 — Administration de l'arbre des départements](../adr/0064-administration-arbre-departements.md)
- [ADR 0069 — Habilitations par rôles et périmètres](../adr/0069-habilitations-par-roles-et-perimetres.md)
- [ADR 0072 — Associations multiples de départements et CGR](../adr/0072-associations-multiples-departements-cgr.md)
- [ADR 0074 — Articulation habilitations locales et circuits courts](../adr/0074-articulation-habilitations-locales-circuits-courts.md)
- [Questions métier ouvertes](../meetings/questions-ouvertes.md)

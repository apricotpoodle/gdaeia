# 0021 — Implémenter les habilitations génériques

**Statut :** En cours — vertical départements implémenté

**Priorité :** Haute

## Contexte

L'accès à `/departments` est actuellement réservé au super-administrateur
par `DepartmentPolicy`, même lorsqu'un utilisateur possède le rôle
administrateur. Plusieurs Policies utilisent encore des rôles ou des
conditions codées en dur. L'ADR 0069 accepté centralise ces capacités
dans un catalogue de permissions et des associations de permissions aux
rôles.

Ce ticket met en œuvre cette évolution, en commençant par le besoin bloquant
d'administration des départements. Il est la première implémentation
spécialisée du socle défini par le ticket 0007, sans accorder de droit
implicite à un rôle.

## Objectif

Remplacer les autorisations d'administration codées en dur par des
permissions génériques, avec un contrôle serveur par Policy et un périmètre
explicite, afin de pouvoir autoriser un administrateur fonctionnel à gérer
les départements sans le transformer automatiquement en super-administrateur.

## Travail attendu

- [x] Faire accepter ou amender l'ADR 0069 avant la migration du modèle de
  données.
- [x] Créer les tables génériques `permissions`, `permission_scopes` et
  `role_permissions`, avec contraintes d'unicité et clés étrangères.
- [x] Définir les permissions de l'administration des départements, au
  minimum la consultation et la gestion, avec une nomenclature stable.
- [x] Implémenter la résolution des permissions et des périmètres `self`,
  `assigned` et `all`, avec refus par défaut.
- [x] Faire dépendre `DepartmentPolicy` de la permission dédiée et non plus
  uniquement de `issuperuser`.
- [x] Vérifier séparément l'accès au menu, l'accès direct à l'URL Web et
  l'accès aux endpoints API des départements.
- [x] Préparer une reprise explicite des droits existants, notamment pour le
  super-administrateur, sans attribuer de droits implicites au rôle 1.
- [x] Ajouter l'interface d'administration nécessaire au catalogue et aux
  associations, en respectant les Policies et le principe du moindre
  privilège.
- [x] Ajouter les tests unitaires, ORM, HTTP et API nécessaires.

La V1 attribue explicitement les permissions `departments.read` et
`departments.manage` au rôle administrateur sur le périmètre `assigned`.
Les périmètres `all` restent admissibles dans le catalogue mais ne sont pas
accordés par la reprise automatique. Le super-administrateur conserve son
contournement documenté. Le catalogue est consultable et administrable par
API et dispose d'un écran Web d'audit ; les attributions sont protégées par
`menus.manage`.

## Critères d'acceptation

1. Un utilisateur disposant explicitement de la permission d'administration
   des départements peut accéder à `/departments` et aux API autorisées.
2. Un administrateur sans cette permission reçoit un refus d'autorisation,
   même si le lien de menu est visible.
3. La visibilité d'un menu ne vaut jamais autorisation d'exécuter l'action.
4. L'absence de permission ou de périmètre vaut refus côté Web et API.
5. Le super-administrateur conserve le comportement prévu et documenté.
6. Les associations de permissions sont auditables et ne permettent pas de
   créer une référence orpheline ou un doublon.
7. Les tests couvrent au moins un accès autorisé, un refus par absence de
   permission, un refus hors périmètre et l'accès direct à l'URL/API.

## Vérifications

- `make test.unit`
- `make test.integration`
- `make test.api`
- `make test.style` si les tests sont modifiés
- `make test.workflow` si des vues ou migrations SQL sont modifiées
- `make cs.check`
- `make stan`

## Références

- [ADR 0069 — Habilitations par rôles et périmètres](../adr/0069-habilitations-par-roles-et-perimetres.md)
- [Ticket 0007 — Accès aux menus et permissions](0007-separer-acces-menus-et-permissions-administration.md)
- [ADR 0064 — Administration de l'arbre des départements](../adr/0064-administration-arbre-departements.md)
- Le ticket 0007 fournit le socle générique ; ce ticket en implémente le
  premier vertical sur les départements.
- `src/Policy/DepartmentPolicy.php`

# 0034 — Finaliser l'archivage et l'accès aux archives

**Statut :** Terminé

**Priorité :** Haute — conservation et consultation

**Dépendance :** [0033 — Reprendre une DAE pendant son circuit](0033-reprendre-une-dae-pendant-son-circuit.md).

## Objectif

Mettre en œuvre l'archivage manuel et réversible de tous les types de DAE, à
l'unité ou en masse, en excluant les cycles actifs. La recherche et la
consultation conservent les droits habituels de lecture. Le plan ne prévoit
aucune purge automatique. Le délai d'éligibilité éventuel et la durée de
conservation restent à clarifier (Q-0030-03) et ne sont pas automatisés.
Le cadrage manuel, réversible et sans purge a été confirmé avant sa mise en
œuvre.

## Travail attendu

- [x] Réutiliser et compléter l'endpoint API et les règles de Policy
  d'archivage déjà présents. Vérifier les rôles, périmètres, codes de
  contrats et l'exclusion des DAE dont le cycle est actif.
- [x] Ajouter les actions d'archivage et de restauration, à l'unité et en
  masse, aux superusers et aux utilisateurs de rôle ADMIN qui peuvent voir la
  DAE, avec Policy serveur et transitions d'état explicites.
- [x] Ne pas restreindre l'archivage par code de contrat : tous les types de
  DAE sont admissibles, donc aucune règle d'archivage ne compare ces codes.
- [x] Préserver le contenu, les votes, les commentaires, les pièces jointes
  et les dates nécessaires à l'historique.
- [x] Appliquer les droits habituels de lecture à la recherche et à la
  consultation des DAE archivées. La liste exclut les DAE archivées par
  défaut et permet de rechercher parmi toutes les DAE autorisées.
- [x] Définir les interactions de l'archivage avec la duplication, la
  suppression manuelle et la conservation.
- [x] Documenter les règles et la procédure d'exploitation.

## Critères d'acceptation

1. [x] Les droits appliquent le cadrage du 0030 et les éventuels délais et
   durées de conservation s'appuient sur la réponse à Q-0030-03.
2. [x] Une DAE de tout type peut être archivée manuellement ou restaurée à
   l'unité ou en masse selon les droits prévus. Un cycle actif empêche
   l'archivage.
3. [x] Une DAE archivée est en lecture seule. Après restauration, elle figure
   à nouveau parmi les demandes non archivées et relève des droits habituels
   de consultation et de modification.
4. [x] La liste exclut les DAE archivées par défaut et permet de les retrouver
   dans le respect des Policies et des périmètres.
5. [x] L'historique est conservé et aucune purge automatique n'est déclenchée.
6. [x] Les routes et mutations directes refusent les actions non autorisées.

## Règles mises en œuvre

- L'horodatage `applicationforms.archived` porte l'état d'archivage courant.
  L'archivage et la restauration ne changent pas les votes, commentaires,
  pièces jointes ou données métier.
- Les transitions groupées sont atomiques et chaque DAE passe sa Policy.
  Un cycle `en_attente` interdit l'archivage ; le démarrage d'un cycle et
  l'archivage se sérialisent sur la ligne de la DAE.
- La Policy conserve `applicationforms.archive:unscoped`, exige le rôle ADMIN
  ou superuser et le droit habituel de lecture sur l'objet. Tous les contrats
  sont admissibles. Un archivage ne donne pas de droit de lecture nouveau.
- La liste courante masque les archives. La case de recherche les inclut
  toutes dans le même périmètre ; `/applicationforms/archives` ne montre que
  les DAE archivées.
- Une DAE archivée n'est pas modifiable ni relançable. Sa duplication crée un
  brouillon non archivé. Le DELETE manuel reste distinct, avec sa Policy
  existante ; il supprime physiquement la DAE et son workflow.
- Aucun délai d'éligibilité, durée de conservation ni traitement de purge
  automatique n'est configuré. Ces règles restent à clarifier au métier.

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

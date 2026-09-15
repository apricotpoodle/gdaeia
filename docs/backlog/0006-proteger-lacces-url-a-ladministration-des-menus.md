# 0006 — Protéger l'accès URL à l'administration des menus

**Statut :** À planifier

**Priorité :** Haute

## Contexte

Le rôle applicatif « administrateur » (`Roles.id = 1`) ne doit pas, à lui seul, donner accès à l'administration des options de menu : liste, création, modification, suppression, déplacement et attribution aux rôles. Un utilisateur authentifié pourrait toutefois saisir directement une URL telle que `/menus`, malgré l'absence de lien de navigation visible.

La Policy actuelle assimile ce rôle à une autorisation d'administration des menus. Il faut expliciter la règle métier attendue et empêcher ce contournement côté serveur, sans se reposer sur le filtrage de l'IHM.

## Objectif

Refuser l'accès aux URL de gestion des menus pour un utilisateur non super-administrateur ayant `role_id = 1`, le rediriger vers son URL applicative par défaut et lui afficher un message Flash d'accès refusé.

## Stratégie de tests

Deux niveaux sont nécessaires :

* un **test unitaire** de `MenuPolicy`, pour exprimer que le seul rôle administrateur ne permet aucune action d'administration des menus ;
* des **tests fonctionnels HTTP** avec `IntegrationTestTrait`, car la redirection vers l'URL par défaut et le message Flash relèvent de la chaîne middleware/contrôleur, non de la Policy seule.

## Critères d'acceptation

- Un utilisateur authentifié ayant `role_id = 1` et `issuperuser = false` ne peut pas accéder à `/menus`.
- La même règle couvre les actions accessibles directement par URL : `add`, `edit`, `delete`, `move-up`, `move-down` et `role-access`, ainsi que leurs endpoints API.
- Chaque tentative reçoit une redirection vers l'URL par défaut applicable à cet utilisateur, jamais une réponse de contenu d'administration.
- Un message Flash en français informe l'utilisateur que l'accès est refusé.
- Les tests unitaires de `MenuPolicy` vérifient le refus de chaque autorisation concernée pour ce profil, tout en conservant le cas autorisé du super-administrateur.
- Les tests fonctionnels HTTP vérifient au minimum la redirection et le Flash pour `/menus`, puis le refus des autres routes représentatives.
- Les tests ne confondent pas le rôle stocké en base (`Roles.id = 1`) avec le drapeau `issuperuser`.
- La suite complète reste verte avec `make test.all`.

## Références

- [MenuPolicy](../../src/Policy/MenuPolicy.php)
- [Tests des Policies d'administration](../../tests/TestCase/Policy/AdministrationPoliciesTest.php)
- [Tests HTTP des menus](../../tests/TestCase/Controller/Api/MenusControllerTest.php)
- [ADR 0032 — Standardisation du flux de travail de développement](../adr/0032-flux-de-travail-developpement.md)
- [ADR 0053 — Stratégie de tests et MySQL dédié à l’intégration](../adr/0053-strategie-tests-unitaires-et-integration.md)

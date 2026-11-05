# 0006 — Sécuriser les changements de rôle

**Statut :** Terminé

**Dépendances :** 0003 et 0004

## Objectif

Permettre à un administrateur local de modifier le rôle d'un utilisateur
gérable, sans permettre une élévation globale.

## Règles

- Un administrateur local peut attribuer les rôles fonctionnels autorisés à
  un utilisateur gérable.
- Seul un superuser peut attribuer `ADMIN`.
- Seul un superuser peut modifier `issuperuser`.
- La liste de rôles affichée ne remplace pas la vérification serveur.

## Incompatibilité à traiter

`DepartmentPolicy::canManageDepartments()` considère actuellement tout
`ADMIN` comme global. Tant que cette Policy ne porte pas le périmètre,
l'attribution locale de `ADMIN` est interdite.

## Vérifications

- [x] Tests de Policy et d'API sur les rôles fonctionnels, `ADMIN` et
  `issuperuser`.
- [x] Vérification de l'autorisation du superuser.

## Réalisation

- [x] Autoriser les administrateurs locaux à attribuer les rôles fonctionnels
  des IDs 2 à 7 à une cible gérable.
- [x] Réserver l'attribution du rôle `ADMIN` et la modification de
  `issuperuser` aux superusers.
- [x] Appliquer le contrôle côté serveur dans les mutations Web et API.
- [x] Limiter les rôles proposés dans les formulaires selon l'opérateur.
- [x] Ajouter les tests unitaires du service et de la Policy ainsi que les
  tests HTTP/API des transitions autorisées et refusées.

Vérifications exécutées : `make test.unit` (103 tests), `make test.api`
(174 tests) et `make test.integration`.


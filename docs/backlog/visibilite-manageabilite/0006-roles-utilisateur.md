# 0006 — Sécuriser les changements de rôle

**Statut :** À planifier

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

- Tests de Policy et d'API sur les rôles fonctionnels, `ADMIN` et
  `issuperuser`.
- Vérification de l'autorisation du superuser.


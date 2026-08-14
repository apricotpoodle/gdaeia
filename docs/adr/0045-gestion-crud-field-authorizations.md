# ADR 0045 : Administration CRUD de la sécurité granulaire des champs (FieldAuthorizations)

**Date :** 14 Août 2026  
**Statut :** Accepté

## Contexte
La sécurité granulaire au niveau des champs (`Field-Level Security` / ADR 0042) nécessite une interface d'administration permettant aux Super Administrateurs de configurer dynamiquement les matrices d'accès `[Rôle, Ressource, Champ, Niveau d'Accès]` sans intervention en base de données.

## Décision
1. **Contrôleur Web (`FieldAuthorizationsController`)** : Rendu de la coquille HTML d'administration protégée par `FieldAuthorizationPolicy::canIndex`.
2. **API REST (`Api/FieldAuthorizationsController`)** :
   - `index()` : Alimentation de la grille Tabulator avec filtres d'en-tête par Rôle, Ressource et Access Level.
   - `getResourcesAndFields()` : Introspection ORM renvoyant dynamiquement les colonnes actives des tables cibles.
   - `add()`, `edit()`, `delete()` : Endpoints de mutation sécurisés par la Policy.
3. **IHM & JS ES6 (`webroot/js/views/FieldAuthorizations/index.js`)** : Interface réactive avec modale d'édition et synchronisation en temps réel de la grille via `TabulatorObserver`.

## Justification
Cette solution complète l'ADR 0042 en offrant une gestion 100 % autonome et sécurisée de la politique d'accès par champ, entièrement intégrée à notre architecture découplée (Tabulator + API + Policy).
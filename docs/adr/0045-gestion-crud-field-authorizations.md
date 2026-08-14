# ADR 0045 : Administration CRUD de la sécurité des champs (FieldAuthorizations)

**Date :** 14 Août 2026
**Statut :** Accepté

## Contexte
L'application intègre une sécurité au niveau des champs (Field-Level ACL) pilotée par la table `field_authorizations`. Les super-administrateurs ont besoin d'une interface pour configurer dynamiquement ces permissions (qui peut voir ou éditer quel champ pour quelle ressource).

## Décision
1. **Routage API Exclusif** : Suivant l'ADR 0009, le contrôleur Web `FieldAuthorizationsController` ne servira que la coquille HTML. Les opérations d'ajout, modification et suppression sont déléguées à `Api\FieldAuthorizationsController`.
2. **Skinny Controller** : Les méthodes de l'API (`add`, `edit`, `delete`) s'appuient sur l'ORM natif pour limiter la logique métier dans le contrôleur. Les vérifications de sécurité s'appuient strictement sur `FieldAuthorizationPolicy` via le composant d'autorisation.
3. **Réponses JSON Standardisées** : Toutes les opérations de mutation (POST/PUT/DELETE) retourneront un payload standardisé `{ "success": bool, "message": string, "errors": array|null }` manipulable facilement par l'adaptateur Ajax de Tabulator.

## Justification (KISS & SoC)
Déléguer la persistance à une API JSON maintient le couplage lâche exigé par Tabulator. Les opérations de persistance s'appuient nativement sur l'entité CakePHP, déléguant la validation des données au modèle `FieldAuthorizationsTable` de manière centralisée (DRY).
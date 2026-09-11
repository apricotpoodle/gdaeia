# 0002 — Mutualiser la présentation des erreurs de validation

**Statut :** À planifier
**Priorité :** Moyenne

## Contexte

Les contrôleurs Web et API présentent déjà les erreurs de validation en français avec le champ concerné. Leur implémentation reste transitoirement dupliquée.

## Objectif

Mettre en œuvre le service commun `ValidationErrorPresenter` décidé par l'ADR 0052, afin d'uniformiser la présentation des erreurs de validation dans l'ensemble de l'application.

## Critères d'acceptation

- Un unique service est utilisé par les contrôleurs Web et API concernés.
- Chaque message mentionne le libellé français du champ et le motif de l'erreur.
- Les réponses API de validation suivent un contrat homogène et retournent HTTP `422`.
- Des tests automatisés couvrent le service et les réponses API.

## Références

- [ADR 0052 — Présentation unifiée des erreurs de validation Web et API](../adr/0052-presentation-unifiee-erreurs-validation-web-api.md)

# 0014 — Détecter et tracer la perte de session

**Statut :** À planifier

**Priorité :** Haute

## Contexte

Une session peut expirer ou devenir invalide pendant que l'utilisateur
consulte l'application ou exécute une requête API. L'application doit
identifier cette situation de façon uniforme. Une coupure réseau temporaire
ou un timeout ne constitue pas une perte de session et ne doit pas déclencher
le parcours de reconnexion.

## Objectif

Détecter les sessions expirées ou invalides, exposer un refus d'authentification
standardisé et tracer l'événement sans enregistrer de secret.

## Critères d'acceptation

- Une session expirée ou invalide est signalée par HTTP `401` lorsqu'une
  requête protégée est traitée sans identité valide.
- Les erreurs réseau, timeouts et indisponibilités temporaires ne sont pas
  transformés en perte de session.
- Les réponses JSON d'authentification suivent le contrat API existant et
  contiennent un message en français lorsque le client doit réagir.
- L'événement de perte de session est traçable avec les informations
  nécessaires au diagnostic, sans mot de passe, cookie, jeton ni donnée
  sensible.
- Le comportement respecte le middleware d'authentification et les règles
  d'autorisation existantes.

## Références

- [Application](../../src/Application.php)
- [AppController](../../src/Controller/AppController.php)
- [ADR 0028 — Authentification évolutive](../adr/0028-authentification-evolutive-et-impersonate.md)
- [ADR 0040 — Mécanisme d'usurpation d'identité](../adr/0040-mecanisme-usurpation-identite-impersonate.md)


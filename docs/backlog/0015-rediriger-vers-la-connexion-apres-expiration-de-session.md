# 0015 — Rediriger vers la connexion après expiration de session

**Statut :** À planifier

**Priorité :** Haute

## Contexte

Lorsqu'une requête protégée reçoit une réponse `401`, l'utilisateur doit être
informé et pouvoir se réauthentifier. Une erreur réseau qui ne confirme pas
une session invalide ne doit pas interrompre son parcours par une redirection
vers l'écran de connexion.

## Objectif

Centraliser le traitement côté interface des réponses `401` afin d'afficher
l'écran de connexion avec un message explicite.

## Critères d'acceptation

- Toute réponse `401` reçue par un appel protégé déclenche l'affichage ou la
  redirection vers l'écran de connexion.
- Le message affiché est : « Votre session a expiré. Veuillez vous
  reconnecter. »
- Une erreur réseau, un timeout ou une réponse serveur `5xx` ne déclenche pas
  cette redirection.
- L'action d'écriture interrompue n'est jamais rejouée automatiquement.
- Le traitement est mutualisé dans le mécanisme JavaScript d'appel existant et
  n'est pas dupliqué dans chaque écran métier.

## Références

- [UsersController](../../src/Controller/UsersController.php)
- [Application](../../src/Application.php)
- [0008 — Sécuriser les URL de retour post-authentification](0008-securiser-les-url-de-retour-post-authentification.md)
- [ADR 0028 — Authentification évolutive](../adr/0028-authentification-evolutive-et-impersonate.md)


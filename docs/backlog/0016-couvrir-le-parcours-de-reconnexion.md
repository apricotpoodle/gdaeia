# 0016 — Couvrir le parcours de reconnexion

**Statut :** À planifier

**Priorité :** Haute

## Contexte

Après une expiration de session, l'utilisateur doit pouvoir se reconnecter
sans être renvoyé vers la page précédemment consultée. Le parcours attendu
est celui d'une première connexion réussie : la page par défaut de
l'application est affichée.

## Objectif

Garantir par des tests le parcours perte de session → connexion → page par
défaut, ainsi que la distinction entre perte de session et erreur réseau.

## Critères d'acceptation

- Après une reconnexion réussie, l'utilisateur est redirigé vers la même page
  par défaut que lors d'une première connexion réussie, actuellement
  `/users/index`.
- L'utilisateur n'est pas redirigé vers la page à l'origine de l'expiration,
  même si celle-ci contenait une URL de retour.
- Le parcours est couvert par des tests HTTP pour les pages Web et les appels
  API concernés.
- Un test vérifie qu'une erreur réseau ou un timeout ne déclenche pas le
  parcours de reconnexion.
- Un test vérifie que l'action interrompue n'est pas rejouée après la
  reconnexion.

## Références

- [UsersController](../../src/Controller/UsersController.php)
- [0014 — Détecter et tracer la perte de session](0014-detecter-et-tracer-la-perte-de-session.md)
- [0015 — Rediriger vers la connexion après expiration de session](0015-rediriger-vers-la-connexion-apres-expiration-de-session.md)
- [ADR 0028 — Authentification évolutive](../adr/0028-authentification-evolutive-et-impersonate.md)


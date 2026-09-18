# 0008 — Sécuriser les URL de retour post-authentification

**Statut :** Terminé

**Priorité :** Moyenne

## Contexte

Le middleware d'authentification mémorise l'URL demandée par un visiteur puis y redirige après une connexion réussie. Certaines URL correspondent à des opérations contextuelles dont la validité dépend de l'état de session. Par exemple, `/users/revert_identity` n'a de sens que lorsqu'une usurpation d'identité est active.

Le cas de `revertIdentity()` est désormais traité localement : hors usurpation, l'action redirige vers l'index des utilisateurs. Les autres actions contextuelles doivent néanmoins être recensées et protégées selon une règle cohérente.

## Objectif

Définir et appliquer une stratégie commune de repli vers la page par défaut autorisée lorsqu'une URL de retour mémorisée n'est plus applicable après authentification.

## Critères d'acceptation

- Toute action dépendante d'un état de session invalide après connexion redirige vers une route par défaut autorisée, sans erreur 404 ou 403 inutile.
- Les redirections externes ou non autorisées ne sont jamais suivies.
- Les parcours visiteur → connexion → URL contextuelle sont couverts par des tests HTTP.
- La stratégie respecte les ADR 0028 et 0040 concernant l'authentification et l'usurpation d'identité.

## Références

- [UsersController](../../src/Controller/UsersController.php)
- [ADR 0028 — Authentification évolutive](../adr/0028-authentification-evolutive-et-impersonate.md)
- [ADR 0040 — Mécanisme d'usurpation d'identité](../adr/0040-mecanisme-usurpation-identite-impersonate.md)

## Réalisation

La stratégie de repli a été mise en œuvre dans le commit `c00800e`
(`fix(auth): sécurise les retours post-authentification`), fusionné par
`e7820e0`. Elle remplace le retour vers `revert_identity` sans usurpation
active par `/users/index`, conserve les retours internes applicables et laisse
le composant d'authentification écarter les destinations externes. Les trois
parcours sont couverts par des tests HTTP.

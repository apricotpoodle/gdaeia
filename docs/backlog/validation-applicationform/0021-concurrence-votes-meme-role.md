# 0021 — Concurrence des votes d’un même rôle

**Statut :** À faire  
**Priorité :** Urgente  
**Dépendances :** 0014, 0016

## Contexte

Une étape peut être attribuée à plusieurs utilisateurs possédant le même
rôle. Ils reçoivent l’invitation en parallèle, mais un seul vote doit être
conservé pour ce rôle et cette étape, conformément à l’ADR 0055.

Il faut vérifier le comportement lorsqu’un second utilisateur ouvre son écran
après le vote de son collègue, ainsi que le comportement de deux soumissions
quasi simultanées.

## Travail attendu

1. Vérifier le comportement métier, ORM, API et interface lorsqu’un utilisateur
   du même rôle a déjà voté.
2. Garantir atomiquement qu’un seul vote est accepté, y compris lors de deux
   requêtes concurrentes. La contrainte existante `validations_step_un` et un
   verrouillage transactionnel adapté doivent être examinés avant toute
   évolution du schéma.
3. Renvoyer au second votant un message explicite, en français, par exemple :
   « Votre collègue a déjà voté pour cette étape de validation. »
4. Rafraîchir ou invalider l’écran de validation afin que le second utilisateur
   ne puisse plus présenter une action de vote disponible.
5. Ne plus afficher l’échéance (`due_at`) comme information active pour un rôle
   qui a déjà voté. Afficher à la place l’horodatage réel du vote
   (`validations.validated`, ou la source de vérité retenue après vérification).
6. Conserver le comportement de suppléance Admin après échéance et vérifier
   qu’il ne permet pas de doubler un vote déjà enregistré.

## Critères d’acceptation

1. Deux utilisateurs actifs du même rôle reçoivent chacun l’invitation de la
   séquence.
2. Le premier vote accepté ou refusé est conservé une seule fois.
3. Le second utilisateur qui ouvre ensuite l’écran voit que son rôle a déjà
   voté, avec l’horodatage du vote et sans échéance active.
4. Deux requêtes concurrentes ne créent jamais deux validations et une seule
   réponse est positive.
5. Le second appel reçoit une réponse métier homogène et exploitable par
   l’interface, sans erreur SQL brute.
6. Le comportement est identique pour une acceptation et pour un refus.
7. Les tests couvrent le service, la contrainte d’unicité, l’API et l’affichage
   de l’état de validation.

## Décision d’architecture

Si le verrouillage ou le contrat de réponse nécessite une règle nouvelle,
proposer un ADR avant une modification transversale. Ne pas modifier l’ADR
0055 rétroactivement.

## Vérifications

- make test.unit
- make test.integration
- make test.workflow
- make test.api
- make test.style
- make cs.check
- make stan

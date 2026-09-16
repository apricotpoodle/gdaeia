# 0018 — Annulation et réinitialisation administrative d’un cycle

**Statut :** À planifier  
**Dépendance :** 0017

## Objectif

Permettre à un Admin habilité ou à un Super Admin d’annuler un cycle de
validation actif et de supprimer toutes ses données de workflow afin que l’AF
revienne exactement à l’état « cycle non lancé » et puisse être relancée.

L’AF, ses champs métier et ses commentaires sans lien avec le workflow ne
doivent jamais être supprimés.

## Décision d’architecture préalable

L’ADR 0055 impose aujourd’hui une exécution immuable. Avant toute
implémentation, proposer un ADR 0056 qui précise l’exception d’administration :
réinitialisation autorisée, acteurs habilités, portée exacte de l’effacement,
traçabilité éventuelle hors des données effacées et notification des personnes
concernées. Ne pas modifier rétroactivement l’ADR 0055.

## Prompt Codex

Après acceptation de l’ADR 0056, implémente une commande « Annuler et
réinitialiser le cycle » sur la fiche d’une Applicationform.

Le bouton est rendu exclusivement lorsque l’AF possède un cycle actif et que
l’identité est soit Super Admin, soit Admin de rôle 1 autorisé à voir l’AF.
Il doit demander une confirmation explicite et expliquer que les votes,
étapes, relances et l’exécution du cycle seront définitivement supprimés.

Expose une mutation API POST protégée par CSRF et Policy. Place la règle métier
dans un service transactionnel : verrouiller l’exécution ciblée, supprimer les
validations liées à ses étapes, supprimer les étapes, puis supprimer
l’exécution. Toute erreur doit annuler intégralement la transaction. Après
succès, l’AF redevient lançable et la fusée est de nouveau visible selon les
droits existants.

Ne supprimer ni l’Applicationform, ni ses commentaires métier, ni ses pièces
jointes, ni les données d’autres cycles. Ajouter la notification et la trace
d’audit définies par l’ADR 0056.

## Critères d’acceptation

1. Le bouton est invisible sans cycle actif et pour tout utilisateur non
   habilité.
2. L’API refuse tout appel non autorisé, sans CSRF ou visant une AF sans cycle
   actif.
3. Après confirmation, aucune ligne liée au cycle ne subsiste dans les tables
   validation_workflow_runs, applicationvalidationsteps ou validations.
4. Une erreur intermédiaire ne laisse aucun effacement partiel.
5. L’AF peut être relancée et repart avec un nouveau cycle et de nouvelles
   étapes.
6. La trace et les e-mails respectent la décision de l’ADR 0056.

## Vérifications

- make test.unit
- make test.integration
- make test.workflow
- make test.api
- make cs.check
- make stan

# ADR 0076 — Limiter la remise à zéro avant la dernière séquence du cycle

**Date :** 25 novembre 2026

**Statut :** Proposé

**Dépendances :** [ADR 0055](./0055-workflow-validation-applicationforms.md),
[ADR 0056](./0056-remise-a-zero-cycle-validation.md),
[ADR 0057](./0057-point-entree-fiche-reinitialisation-cycle.md),
[ADR 0070](./0070-circuits-courts-locaux-par-contrat.md)

## Contexte

L'ADR 0056 autorise un administrateur habilité à remettre à zéro un cycle
existant et l'ADR 0057 expose cette opération sur la grille et la fiche. Le
plan GDAE V3 retient ce mécanisme suivi d'une relance et limite la remise à
zéro à la période qui précède la dernière séquence du cycle. La confirmation
métier sur la suffisance du mécanisme et ses effets reste ouverte dans le
[registre des questions](../meetings/questions-ouvertes.md), à Q-0030-02.

## Décision proposée

Amender la condition d'éligibilité de l'ADR 0056. Un administrateur habilité
peut remettre à zéro un cycle existant tant que celui-ci n'a pas atteint sa
dernière séquence configurée. Dès l'ouverture de cette séquence, la remise à
zéro est refusée, même si des votes sont encore attendus.

La dernière séquence est celle de l'instantané du cycle concerné, établi au
lancement. Pour un circuit court, il s'agit de la dernière séquence retenue
par la règle de fin locale. Les rôles parallèles d'une même séquence partagent
cette limite. Un cycle à séquence unique atteint cette limite dès son
lancement.

La Policy conditionne l'action de grille et de fiche, et l'API contrôle
l'éligibilité côté serveur. La mutation vérifie la limite au moment de la
remise à zéro pour qu'une progression concurrente vers la dernière séquence
ne puisse pas la contourner.

La remise à zéro réutilise la mutation POST protégée par CSRF et la
transaction définie par l'ADR 0056. La relance passe par l'action existante.

## Justification

La réutilisation de la remise à zéro et de la relance limite le changement
à une condition d'éligibilité (KISS). La grille et la fiche s'appuient sur
le même contrôle d'autorisation et la même mutation (DRY).

L'instantané réutilise la configuration effective du cycle. Il fournit
une référence commune aux circuits complets et courts, indépendante des
modifications ultérieures de paramétrage.

## Conséquences

### Positives

- Les administrateurs habilités disposent du parcours existant de reprise
  jusqu'à l'ouverture de la dernière séquence.
- La limite s'applique à la séquence finale effective de chaque cycle. La
  transaction conserve les données de la DAE et les validations historiques
  étrangères au cycle remis à zéro.

### Négatives

- La remise à zéro est indisponible dès la dernière séquence, y compris
  dès le lancement d'un cycle à séquence unique.
- La transaction efface l'exécution, les étapes, les votes et leurs
  commentaires. Le cycle supprimé n'est plus disponible comme piste d'audit
  applicative.

## Vérifications prévues

Le [ticket 0033](../backlog/0033-reprendre-une-dae-pendant-son-circuit.md)
prévoit de vérifier la remise à zéro avant la limite et son refus dès la
dernière séquence, y compris avec un circuit court, une séquence unique et
une progression concurrente.

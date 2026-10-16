# ADR 0067 : Intégrité des références de séquences du workflow

**Date :** 16 Octobre 2026  
**Statut :** Proposé  
**Dépendances :** ADR 0055 — Workflow de validation des Applicationforms

## Contexte

Les étapes de workflow enregistrent l'identifiant de la séquence de validation
utilisée lors de leur création. La suppression physique d'une séquence
référencée laisse alors une étape orpheline. Le chargement de l'étape suivante
peut échouer avec l'erreur Record not found in table validationsequences.

## Décisions

1. Une séquence de validation n'est plus supprimée physiquement lorsqu'elle
   est désaffectée. La colonne deleted est renseignée afin de conserver son
   identifiant pour les workflows existants.
2. La réparation des références orphelines est effectuée par la commande
   bin/cake validation repair-orphaned-sequences.
3. La commande fonctionne en diagnostic par défaut et n'applique les
   réalignements qu'avec --apply.
4. Un réalignement est autorisé uniquement lorsqu'une seule séquence active
   correspond au département de la DAE, au rôle et au numéro d'étape.
   Les cas absents ou ambigus sont signalés et restent inchangés.
5. La commande ne modifie ni l'état du workflow ni l'historique des votes.

## Conséquences

Les workflows en cours conservent une référence résoluble même lorsque le
paramétrage actif est désactivé. Les données historiques déjà orphelines
nécessitent une exécution contrôlée de la commande. Les correspondances
ambiguës doivent être traitées manuellement.

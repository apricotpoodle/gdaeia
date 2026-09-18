# ADR 0055 : Workflow de validation des Applicationforms

**Date :** 16 septembre 2026  
**Statut :** Accepté

## Décision

Une demande est lancée explicitement par son créateur ou un Admin visible. Le lancement copie les séquences et rôles du département dans une exécution immuable. Un unique utilisateur éligible vote au nom de chaque rôle. Tous les rôles d'une séquence doivent accepter pour ouvrir la suivante ; un refus motivé clôture immédiatement le cycle.

Les Admins de rôle `1` qui voient la demande peuvent suppléer un rôle seulement après son échéance. Les délais sont configurables globalement et par séquence. Les relances sont quotidiennes. Les modifications pendant le cycle sont tracées dans les commentaires et ne révoquent pas les votes ; le département est verrouillé.

## Conséquences

Les Policies restent le contrôle d'accès effectif ; les liens de courriel nécessitent une session authentifiée. Les états et visas sont calculés sur l'instantané du cycle, pas sur les séquences qui pourraient être modifiées ultérieurement.

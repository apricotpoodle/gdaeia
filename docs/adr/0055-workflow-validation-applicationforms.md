# ADR 0055 : Workflow de validation des Applicationforms

**Date :** 16 septembre 2026  
**Statut :** Accepté

## Décision

Une demande est lancée explicitement par son créateur ou un Admin visible. Le lancement copie les séquences et rôles du département dans une exécution immuable. Un unique utilisateur éligible vote au nom de chaque rôle. Tous les rôles d'une séquence doivent accepter pour ouvrir la suivante ; un refus motivé clôture immédiatement le cycle.

Le commentaire de vote est saisi dans un champ libre pouvant être prérempli par
un commentaire prédéfini correspondant à la décision. Le texte final reste
modifiable et constitue la seule valeur persistée dans `validations.obs`.
Les commentaires prédéfinis ne sont pas référencés dans le vote.

L'obligation de commenter est paramétrable globalement, sans modification du
code, via `workflow_settings`. Par défaut, le commentaire est facultatif lors
d'une acceptation et obligatoire lors d'un refus. Le paramétrage est relu au
moment du vote et s'applique donc également aux cycles en cours.

Les Admins de rôle `1` qui voient la demande peuvent suppléer un rôle seulement après son échéance. Les délais sont configurables globalement et par séquence. Les relances sont quotidiennes. Les modifications pendant le cycle sont tracées dans les commentaires et ne révoquent pas les votes ; le département est verrouillé.

Les commentaires généraux de la fiche restent séparés dans `comments` et ne
se confondent pas avec les commentaires de décision du workflow.

## Conséquences

Les Policies restent le contrôle d'accès effectif ; les liens de courriel nécessitent une session authentifiée. Les états et visas sont calculés sur l'instantané du cycle, pas sur les séquences qui pourraient être modifiées ultérieurement. `applicationvalidationsteps.comment` n'est pas une seconde source de vérité : il doit être déprécié puis supprimé au profit de `validations.obs`.

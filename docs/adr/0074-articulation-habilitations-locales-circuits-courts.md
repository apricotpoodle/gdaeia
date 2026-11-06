# ADR 0074 — Articulation des habilitations locales des circuits courts

**Date :** 6 novembre 2026
**Statut :** Proposé
**Dépendances :** ADR 0069, ADR 0070 et ADR 0050

## Contexte

L'ADR 0070 accepté décrit une habilitation de gestion par rôle et département,
distincte du rôle final de validation. Il évoque encore le modèle générique
de l'ADR 0069 comme proposé ; ce socle est désormais accepté et implémenté.
Ses attributions `role_permissions` ne portent pas de département spécifique.

Le ticket 0017 possède une première implémentation accordant des accès en
fonction de rôles codés en dur. Ces droits ne satisfont pas l'ADR 0070 et ne
constituent pas une désignation explicite de gestionnaires locaux.

## Proposition

Conserver le socle générique existant et implémenter le droit local déjà
décidé par l'ADR 0070 dans `validation_circuit_managers`. Chaque ligne désigne
un couple unique département–rôle. Seuls les super-administrateurs peuvent
ajouter ou retirer ces désignations.

Pour un utilisateur non super-administrateur, un département est administrable
si son rôle y est désigné **et** s'il lui est explicitement associé dans
`user_departments`. Aucun descendant n'est déduit. Le rôle final d'une règle
ne donne aucun droit de gestion ; le rôle Admin n'en donne pas davantage.
L'identité effective usurpée est la seule évaluée.

Les finders partagent cette définition avec les Policies. La migration crée
une table vide : aucune reprise automatique des anciens droits implicites,
aucun ajout de règle SEM, aucun rattachement utilisateur automatique.
Le lien de navigation utilise la même Policy que la page et les API.

Le contrôle de parcours est partagé entre aperçu, enregistrement et lancement.
Une règle invalide n'est pas enregistrée. Les évolutions ultérieures des
référentiels peuvent néanmoins l'invalider : le lancement revérifie toujours
son parcours. Un ajout groupé est atomique ; un doublon refuse tout le lot.

## Conséquences

Cette livraison ne généralise pas les attributions génériques par département
et ne modifie pas rétroactivement les ADR acceptés. Le super-administrateur
doit désigner les gestionnaires locaux avant leur premier accès. L'opérateur
saisit ensuite les règles des seuls services concernés, avec aperçu des rôles
parallèles et des défauts de configuration.

La conception a été validée avec l'opérateur pour l'implémentation du ticket
0017. Le statut de cet ADR reste `Proposé` jusqu'à une acceptation humaine
explicite de sa fiche.

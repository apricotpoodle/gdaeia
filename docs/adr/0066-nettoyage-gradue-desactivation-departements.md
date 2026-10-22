# ADR 0066 — Nettoyage gradué lors de la désactivation des départements

- Date : 2026-10-16
- Statut : Accepté
- Dépendances : ADR 0064, ADR 0054 et ADR 0065

## Contexte

Un département peut être utilisé par des utilisateurs, des séquences de
validation, des codes CGR, des références CGR par défaut et des formulaires.
Une désactivation silencieuse risquerait de supprimer des rattachements ou de
rompre le workflow sans que l'opérateur mesure les conséquences.

## Décision

- L'API expose d'abord un impact détaillé et la désactivation n'est accessible
  qu'au super-administrateur autorisé par la Policy.
- Le super-administrateur doit confirmer chaque catégorie de nettoyage active.
  L'opération est transactionnelle et retourne la liste des identifiants
  traités.
- Les formulaires actifs et les codes CGR système sont des blocages absolus.
  Les séquences et les codes CGR non système peuvent être désactivés
  logiquement ; les références CGR par défaut peuvent être supprimées.
- Les utilisateurs ne sont jamais désactivés : seuls leurs rattachements au
  périmètre supprimé sont détachés.
- Un compte rendu est envoyé au super-administrateur exécutant, aux
  responsables, aux administrateurs rattachés au périmètre et aux utilisateurs
  détachés. Le rapport complet est réservé aux responsables et à l'audit ;
  les utilisateurs détachés reçoivent un rapport limité. Des destinataires
  d'audit supplémentaires sont configurables par variable d'environnement.
- Un échec d'envoi est journalisé et signalé après la transaction, sans
  annuler la désactivation déjà effectuée.

## Justification

Cette confirmation graduée rend les conséquences visibles et limite le
privilège de suppression aux opérations explicitement assumées par le
super-administrateur. La conservation des utilisateurs et le blocage des
formulaires et codes système protègent les données opérationnelles.

## Conséquences

Le parcours comporte un appel préalable d'impact et une confirmation renforcée
dans l'interface. Les désactivations sont auditables par courriel et les
échecs de remise doivent être suivis via les journaux applicatifs.

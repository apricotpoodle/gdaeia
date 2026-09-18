# ADR 0059 : Validation locale avant partage

**Date :** 18 septembre 2026
**Statut :** Accepté
**Amende :** [ADR 0053 — Stratégie de tests et MySQL dédié à l’intégration](0053-strategie-tests-unitaires-et-integration.md)

## Contexte

L’ADR 0053 retient GitHub Actions pour reproduire les validations MySQL et la
couverture de code. L’équipe étant constituée de deux développeurs, elle
privilégie pour l’instant une procédure locale explicite, reposant sur
l’environnement Docker déjà maintenu par le projet.

Le workflow GitHub existant ne reproduit pas cette procédure : il utilise une
matrice PHP obsolète et ne valide pas le périmètre MySQL complet. Le conserver
donnerait une indication de qualité incomplète.

## Décision proposée

Jusqu’à nouvelle décision, la validation avant partage ou demande de fusion est
effectuée localement avec Docker et les cibles Makefile :

```sh
make test.all
make test.style
make cs.check
make stan
```

`make test.coverage` est exécuté avant une évolution transversale ou une
livraison importante. Ses rapports Clover et HTML restent temporaires sous
`/tmp`, sans seuil bloquant. MySQL `daetf2_test`, PCOV et les gardes de sécurité
définis par l’ADR 0053 restent obligatoires.

Le workflow GitHub Actions obsolète est retiré. Une future CI devra faire
l’objet d’un nouvel ADR et reproduire les mêmes contraintes MySQL, FULLTEXT et
vues SQL ; SQLite ne constitue pas une alternative de validation.

## Conséquences

La responsabilité de lancer et de partager le résultat des contrôles incombe
aux développeurs avant intégration. La solution évite la maintenance d’une CI
peu utilisée, au prix de l’absence de contrôle automatique distant.

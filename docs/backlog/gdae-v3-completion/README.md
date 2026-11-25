# Sous-workflow de finalisation GDAE V3

Le manifeste `manifest.json` est la source de vérité pour l'ordre d'exécution.
Avant chaque intervention, exécuter `bin/cake backlog next gdae-v3-completion`.
Consulter le statut de la fiche affichée avant de l'engager. La commande
peut afficher un ticket bloqué dont les dépendances sont satisfaites. Mettre
à jour le statut dans le manifeste au fil du travail.

Les tickets 0031 à 0039 forment une chaîne stricte. Le ticket 0030 recueille
les directives du plan et réconcilie les documents; il est `Terminé`. Le 0031
est `Terminé` après consignation et application des réponses Q-0018-02 à
Q-0018-04. Les tickets ultérieurs portent leurs bloqueurs propres.

Le ticket 0039 organise la recette dans Docker local isolé avec des données
synthétiques et des séquences configurées par l'opérateur pour chaque service.
La configuration d'Airflow et de la production relève de l'exploitation. Le
ticket 0040 est un bonus facultatif, hors du manifeste obligatoire, à examiner
seulement après la fin du 0039.

## Vérifications de livraison

Chaque ticket précise les suites adaptées à son périmètre. Les tests MySQL
utilisent exclusivement `daetf2_test` via `DATABASE_TEST_URL`. Pour la recette
transversale du 0039, exécuter `make test.all` lorsque l'environnement de test
est disponible et consigner les résultats et éventuelles limites.

## Références

- [Index du backlog racine](../README.md)
- [Synthèse exhaustive](../SYNTHESE.md)
- [Questions métier ouvertes](../../meetings/questions-ouvertes.md)
- [Cahier des charges GDAE V3](../../meetings/cahier_des_charges_gdae_v3.md)

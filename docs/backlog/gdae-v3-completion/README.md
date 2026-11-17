# Sous-workflow de finalisation GDAE V3

Le manifeste `manifest.json` est la source de vérité pour l'ordre d'exécution.
Avant chaque intervention, exécuter `bin/cake backlog next gdae-v3-completion`.
Ne commencer qu'un ticket affiché par la commande et mettre à jour son statut
dans le manifeste au fil du travail.

Les tickets 0031 à 0039 forment une chaîne stricte. Le ticket 0030 recueille
les décisions métier et corrige les statuts historiques avant tout
développement dépendant. Les règles encore ouvertes restent des bloqueurs ;
elles ne doivent pas être déduites du code existant.

Le ticket 0039 organise une recette dans un environnement explicitement
convenu. Il ne présume aucune configuration de production. Le ticket 0040 est
un bonus facultatif, hors du manifeste obligatoire, à examiner seulement
après la fin du 0039.

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

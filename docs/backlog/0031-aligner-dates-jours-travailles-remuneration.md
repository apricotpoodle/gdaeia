# 0031 — Aligner dates, jours travaillés et rémunération

**Statut :** Terminé

**Priorité :** Haute — cohérence des demandes

**Dépendance :** [0030 — Arbitrer et actualiser le périmètre GDAE V3](0030-arbitrer-et-actualiser-le-perimetre-gdae-v3.md).

## Objectif

Appliquer les règles métier arbitrées au formulaire, à la validation ORM, au
calcul du salaire de base et à l'API, sans déduire les règles des seuls libellés
de contrat.

Le ticket 0018 couvre déjà le calcul hors week-ends, la saisie et les bornes
serveur. Le nouveau travail porte sur les règles V3 décidées au 0030 et sur
les écarts vérifiés, pas sur une réécriture de ces comportements existants.

Le ticket 0030 est terminé et les arbitrages Q-0018-02 à Q-0018-04 sont
consignés. Le nombre de jours est facultatif et modifiable; zéro est refusé
quand la période contient un jour de semaine, et les jours fériés en semaine
restent comptés comme des jours de semaine. Pour €/jour sans nombre saisi, le
salaire de base reste vide. Le cadrage approuvé retire €/heure des nouvelles
saisies et demande une unité prise en charge pour modifier une rémunération
historique.

## Travail attendu

- [x] Appliquer les règles de validation de `working_days` décidées au 0030 et
  conserver la visibilité décidée (dates valides, sauf date de fin désactivée
  pour un CDI) ; conserver une validation serveur.
- [x] Déclarer les codes de contrat comme constantes publiques dans l'entité
  `Contracttype` et n'utiliser que ces constantes dans les règles métier pour
  `cdi`, `cdd`, `interim`, `appr.`, `stage`, `pige` et `fact`.
- [x] Aligner les bornes de dates, les unités de durée et le nombre de jours
  sur les règles confirmées, y compris les périodes sans jour ouvré et les
  éventuelles corrections manuelles.
- [x] Lors d'un changement de dates, afficher la valeur actuelle de
  `working_days` et le nouveau calcul, puis laisser l'opérateur décider de la
  valeur à conserver.
- [x] Pour toute valeur de `working_days` égale à zéro, afficher un avertissement
  avant l'enregistrement et envoyer le courriel à l'auteur, aux responsables
  associés au service et à tout rôle ADMIN qui voit ce service,
  uniquement après un enregistrement réussi.
- [x] Retirer l'unité €/heure des nouvelles saisies Web et API et préserver
  les valeurs historiques en €/heure, conformément au cadrage du plan.
- [x] Rendre le coefficient global de k/mois modifiable par un
  super-administrateur.
- [x] Garder le montant calculé cohérent après toute modification des dates,
  jours travaillés, période ou montant saisi.
- [x] Couvrir formulaire Web, validation ORM et mutations/calculs API.
- [x] Documenter les règles dans le README local Applicationforms.

## Critères d'acceptation

1. La visibilité du champ et les règles de validation sont celles consignées
   au 0030, tant dans l'interface que côté serveur.
2. Une requête API fabriquée ne peut contourner les règles de jours et de
   dates.
3. Le salaire de base correspond à la formule prévue pour chaque unité
   disponible. L'unité €/heure est indisponible pour toute nouvelle saisie
   et les valeurs historiques sont conservées. Le coefficient de k/mois
   est global et sa modification est réservée au super-administrateur.
4. Les erreurs de validation sont présentées en français et suivent le
   contrat API `{success, message, errors}` avec HTTP 422 lorsque pertinent.
5. Un zéro déclenche l'avertissement avant enregistrement et le courriel après
   succès, jamais sur un enregistrement rejeté.
6. Les règles métier utilisent les constantes de `Contracttype`, sans codes
   de contrat écrits en dur.

## Vérifications

- `make test.unit`
- `make test.integration` pour les règles ORM
- `make test.api` pour les formulaires et endpoints
- `make test.style` si des tests sont modifiés
- `make cs.check` et `make stan` si du PHP est modifié

Les tests MySQL ciblent exclusivement `daetf2_test` via
`DATABASE_TEST_URL`.

## Références

- [0018 — Sécuriser la saisie des jours ouvrés](0018-securiser-la-saisie-des-jours-ouvres-dae.md)
- [Questions métier ouvertes](../meetings/questions-ouvertes.md)
- `src/Model/Table/ApplicationformsTable.php`
- `src/Service/SalaryCalculationService.php`
- [ADR 0053 — Stratégie des tests](../adr/0053-strategie-tests-unitaires-et-integration.md)

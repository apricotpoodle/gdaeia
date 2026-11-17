# 0031 — Aligner dates, jours travaillés et rémunération

**Statut :** À planifier

**Priorité :** Haute — cohérence des demandes

**Dépendance :** [0030 — Arbitrer et actualiser le périmètre GDAE V3](0030-arbitrer-et-actualiser-le-perimetre-gdae-v3.md).

## Objectif

Appliquer les règles métier arbitrées au formulaire, à la validation ORM, au
calcul du salaire de base et à l'API, sans déduire les règles des seuls libellés
de contrat.

Le ticket 0018 couvre déjà le calcul hors week-ends, la saisie et les bornes
serveur. Le nouveau travail porte sur les règles V3 décidées au 0030 et sur
les écarts vérifiés, pas sur une réécriture de ces comportements existants.

## Travail attendu

- [ ] Appliquer la visibilité et le caractère obligatoire de `working_days`
  selon les contrats confirmés au 0030 ; conserver une validation serveur.
- [ ] Aligner les bornes de dates, les unités de durée et le nombre de jours
  sur les règles confirmées, y compris les périodes sans jour ouvré et les
  éventuelles corrections manuelles.
- [ ] Implémenter ou exclure explicitement le calcul €/heure selon la décision
  métier, avec une erreur française cohérente si l'unité n'est pas calculable.
- [ ] Garder le montant calculé cohérent après toute modification des dates,
  jours travaillés, période ou montant saisi.
- [ ] Couvrir formulaire Web, validation ORM et mutations/calculs API.
- [ ] Documenter les règles dans le README local Applicationforms.

## Critères d'acceptation

1. Les contrats inclus et exclus du champ et son caractère obligatoire sont
   ceux consignés au 0030, tant dans l'interface que côté serveur.
2. Une requête API fabriquée ne peut contourner les règles de jours et de
   dates.
3. Le salaire de base correspond à la formule décidée pour chaque unité,
   notamment €/heure.
4. Les erreurs de validation sont présentées en français et suivent le
   contrat API `{success, message, errors}` avec HTTP 422 lorsque pertinent.

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
- `src/Model/Table/ApplicationformsTable.php`
- `src/Service/SalaryCalculationService.php`
- [ADR 0053 — Stratégie des tests](../adr/0053-strategie-tests-unitaires-et-integration.md)

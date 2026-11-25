# 0033 — Reprendre une DAE pendant son circuit

**Statut :** Terminé

**Priorité :** Haute — continuité de traitement

**Dépendance :** [0032 — Habiliter l'administration des services par les rôles ADMIN](0032-habiliter-administration-locale-par-service.md).

## Objectif

Réutiliser la remise à zéro et la relance existantes pour reprendre une DAE
transmise. Le plan approuvé limite l'éligibilité à la remise à zéro aux
cycles qui n'ont pas atteint leur dernière séquence configurée. Le métier a
confirmé la suffisance du mécanisme et accepté ses effets à Q-0030-02.

## Travail attendu

- [x] Adapter les points d'entrée existants et leur contrôle serveur à la
  limite définie par l'ADR 0076, en se fondant sur l'instantané du cycle.
- [x] Préserver la remise à zéro transactionnelle des votes, étapes et
  commentaires de ce cycle, les données métier et le parcours de relance.
- [x] Documenter le parcours et la perte de l'historique applicatif du cycle
  remis à zéro, conformément à l'ADR 0056.

## Critères d'acceptation

1. Seul un administrateur habilité peut remettre à zéro un cycle existant
   qui n'a pas atteint sa dernière séquence configurée. Dès cette dernière
   séquence, la remise à zéro est refusée.
2. La remise à zéro supprime les votes et étapes de ce cycle selon
   l'ADR 0056. Les données de la DAE et les validations étrangères au cycle
   sont conservées, et la DAE peut être relancée.
3. Une mutation forgée ou concurrente ne peut pas contourner la Policy ni
   créer un état de workflow incohérent.
4. Les réponses API et messages utilisateur sont en français.

## Vérifications

- `make test.unit` pour Policies et services
- `make test.api` pour routes et autorisations
- `make test.workflow` si le workflow ou ses vues SQL évoluent
- `make test.style` si des tests sont modifiés
- `make cs.check` et `make stan` si du PHP est modifié

Résultats : `make test.unit` réussit (220 tests, 916 assertions),
`make test.style`, `make cs.check`, `make stan` et `make test.workflow`
réussissent (9 tests, 45 assertions). Les scénarios du ticket passent dans
`make test.integration` et `make test.api`. Ces deux suites complètes restent
en échec à cause de la base `daetf2_test`, qui ne possède pas
`menus.required_permission_id` : trois erreurs d'intégration; trois erreurs
et quatre échecs API liés à cette colonne absente.

## Références

- [ADR 0055 — Workflow de validation des Applicationforms](../adr/0055-workflow-validation-applicationforms.md)
- [ADR 0056 — Remise à zéro exceptionnelle d'un cycle](../adr/0056-remise-a-zero-cycle-validation.md)
- [ADR 0076 accepté — Limiter la remise à zéro avant la dernière séquence](../adr/0076-limiter-remise-a-zero-avant-derniere-sequence.md)
- [ADR 0063 — Outrepassement des validations workflow](../adr/0063-outrepassement-validations-workflow.md)
- [ADR 0070 — Circuits courts locaux par contrat](../adr/0070-circuits-courts-locaux-par-contrat.md)
- [Questions métier ouvertes](../meetings/questions-ouvertes.md)

# 0033 — Reprendre une DAE pendant son circuit

**Statut :** À planifier

**Priorité :** Haute — continuité de traitement

**Dépendance :** [0032 — Habiliter l'administration des services par les rôles ADMIN](0032-habiliter-administration-locale-par-service.md).

## Objectif

Ajouter l'action de récupération d'une DAE transmise, selon les règles
confirmées au 0030, avec autorisation serveur et historique explicite.

## Travail attendu

- [ ] Modéliser les états et transitions de récupération sans supprimer ni
  réécrire les votes existants hors de la décision métier.
- [ ] Exposer l'action via une Policy et les fabriques d'actions du domaine.
- [ ] Protéger les mutations Web/API et tracer l'acteur, la date, l'étape et
  les décisions de conservation ou d'annulation du cycle.
- [ ] Refuser les états ou rôles non admissibles selon les règles arbitrées.
- [ ] Documenter le parcours et les effets sur le workflow.

## Critères d'acceptation

1. Seuls les rôles et états autorisés peuvent récupérer la DAE.
2. Les votes et étapes antérieurs sont conservés ou invalidés exactement
   selon la décision du 0030 et restent auditables.
3. Une mutation forgée ou concurrente ne peut pas contourner la Policy ni
   créer un état de workflow incohérent.
4. Les réponses API et messages utilisateur sont en français.

## Vérifications

- `make test.unit` pour Policies et services
- `make test.api` pour routes et autorisations
- `make test.workflow` si le workflow ou ses vues SQL évoluent
- `make test.style` si des tests sont modifiés
- `make cs.check` et `make stan` si du PHP est modifié

## Références

- [ADR 0055 — Workflow de validation des Applicationforms](../adr/0055-workflow-validation-applicationforms.md)
- [ADR 0063 — Outrepassement des validations workflow](../adr/0063-outrepassement-validations-workflow.md)
- [ADR 0070 — Circuits courts locaux par contrat](../adr/0070-circuits-courts-locaux-par-contrat.md)

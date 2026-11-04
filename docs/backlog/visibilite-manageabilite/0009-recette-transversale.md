# 0009 — Recette transversale et clôture

**Statut :** À planifier

**Dépendance :** 0008

## Objectif

Vérifier la cohérence finale entre finders, Policies, services, contrôleurs,
API, grilles et rôles.

## Scénarios de recette

- superuser ;
- administrateur local et user de son périmètre ;
- user visible associé à un département parent ;
- user d'une branche indépendante ;
- périmètre multi-branches ;
- changement de rôle fonctionnel ;
- refus d'attribution de `ADMIN` par un administrateur local ;
- attribution de `ADMIN` par un superuser ;
- accès direct Web et API aux mutations.

## Vérifications attendues

- `make test.unit`
- `make test.integration`
- `make test.api`
- `make test.style`
- `make cs.check`
- `make stan`
- `make test.all`
- vérification REPL des associations ORM selon l'ADR 0010

## Clôture

- Mettre à jour les README locaux et la synthèse du backlog.
- Passer les sous-tickets et le ticket parent à `Terminé` après validation.
- Proposer la mise à jour du statut de l'ADR 0073 vers `Accepté` si la
  réalisation correspond à la décision.


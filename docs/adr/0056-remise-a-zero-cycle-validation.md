# ADR 0056 : Remise à zéro exceptionnelle d’un cycle de validation

**Date :** 17 septembre 2026  
**Statut :** Accepté  
**Dépendances :** [ADR 0026](./0026-controle-acces-visuel-grid-rights.md), [ADR 0054](./0054-commandes-ui-autorisees-par-domaine.md), [ADR 0055](./0055-workflow-validation-applicationforms.md)

## Contexte

L’ADR 0055 qualifie l’exécution d’un cycle de validation d’immuable. En pratique, une erreur de configuration ou de lancement doit pouvoir être corrigée avant de relancer la demande. La remise à zéro demandée doit restituer strictement les données produites par l’appui sur la fusée.

## Décision

1. Une action de grille `resetValidation` est visible uniquement pour un administrateur qui peut voir la demande et lorsqu’un cycle existe.
2. L’API n’accepte qu’une requête POST autorisée et protégée par CSRF.
3. Une transaction efface, dans l’ordre des dépendances, les votes liés aux étapes du cycle, les étapes, puis l’exécution de workflow. Les données de la demande et les validations historiques étrangères au cycle sont conservées.
4. L’opération retourne le décompte des lignes supprimées afin que l’interface puisse confirmer le résultat. Les courriels déjà remis ne peuvent pas être retirés des boîtes de réception.

## Justification

La transaction rend l’opération atomique : une erreur annule toute suppression. Le contrôle de Policy reste le verrou effectif et `grid_rights` ne sert qu’à présenter l’action autorisée. La suppression ciblée conserve l’état antérieur au lancement sans supprimer d’éventuelles données antérieures qui ne font pas partie de l’exécution.

## Conséquences

### Positives

* Une demande peut être relancée après correction de sa configuration.
* Les vues de statut reviennent à l’état sans cycle dès la fin de la transaction.
* Les votes et étapes concernés ne subsistent pas de façon partielle.

### Négatives

* Le cycle supprimé n’est plus disponible comme piste d’audit applicative.
* Cette exception amende le caractère immuable défini par l’ADR 0055 pour les administrateurs habilités.

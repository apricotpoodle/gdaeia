# 0020 — Réorganiser le paramétrage du workflow

**Statut :** Terminé  
**Dépendance :** 0012

## Contexte

L'écran `/validationsequences` présente aussi le délai global et le catalogue
des commentaires prédéfinis. Cette cohabitation rend l'écran moins lisible et
ne correspond pas clairement à la responsabilité de configuration des
séquences par département.

## Travail attendu

Étudier la création d'une URL d'administration dédiée au paramétrage global du
workflow (délai par défaut et commentaires prédéfinis), en cohérence avec les
routes, Policies et abstractions d'interface existantes. Si cette séparation
constitue une évolution architecturale, proposer l'ADR correspondant avant une
mise en œuvre étendue.

Présenter le catalogue de commentaires sous forme de grille Tabulator avec
traitement côté serveur, tri et actions d'administration cohérentes avec les
fabriques et adaptateurs existants.

## Critères d'acceptation

1. L'URL et la responsabilité de chaque écran sont explicites et documentées.
2. Les paramètres globaux et le catalogue ne sont plus mélangés à la gestion
   des séquences par département, sauf décision documentée contraire.
3. Le catalogue est affiché dans une grille Tabulator avec traitement distant.
4. Les droits d'administration restent contrôlés par une Policy côté serveur.
5. Les tests HTTP et les vérifications de qualité couvrent le nouvel écran.

## Vérifications

- make test.api
- make test.unit
- make cs.check
- make stan

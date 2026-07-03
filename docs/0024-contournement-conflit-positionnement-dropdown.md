# ADR 0024 : Résolution du conflit de positionnement des menus déroulants dans les en-têtes Tabulator

**Date :** 03 Juillet 2026
**Statut :** Accepté

## Contexte
L'en-tête de la table "withActions" requiert un menu déroulant d'administration globale. L'utilisation du mécanisme natif de Bootstrap 5.3 (`data-bs-toggle="dropdown"`) provoquait une défaillance visuelle majeure : le moteur de positionnement sous-jacent (Popper.js) calculait des coordonnées erronées en raison de l'encapsulation dynamique et des contraintes structurelles (`overflow: hidden`) appliquées par Tabulator sur ses colonnes.

## Décision
1. **Désactivation de Popper.js** : Retrait complet de la directive d'automatisation de Bootstrap sur le bouton de l'en-tête généré par la `ButtonFactory`.
2. **Prise en main programmatique** : Implémentation d'une logique d'affichage unitaire au sein du gestionnaire d'événements `headerClick` du `TabulatorBuilder`, pilotant directement la présence de la classe CSS `.show`.
3. **Sanctuarisation CSS** : Injection d'une directive d'affichage agressive (`overflow: visible !important`) dans `custom-theme.css` ciblant les 7 niveaux de calques structurels des en-têtes Tabulator.

## Justification
Cette solution applique scrupuleusement le principe **KISS** (Keep It Simple, Stupid). En éliminant la surcouche de calcul de Popper.js au profit d'un positionnement CSS relatif/absolu standardisé contrôlé par l'application, nous supprimons définitivement les effets de clignotement ou de disparition du menu lors du défilement ou du redimensionnement de la grille.

# ADR 0039 : Défilement Infini (Progressive Loading) au lieu de la Pagination

**Date :** 10 Juillet 2026
**Statut :** Accepté

## Contexte
La pagination classique (boutons Précédent/Suivant, numéros de page) interrompt le flux de lecture de l'utilisateur et surcharge visuellement l'interface. Tabulator propose un mode `progressiveLoad: "scroll"` permettant de charger les données de manière transparente à mesure que l'utilisateur fait défiler la grille vers le bas.

## Décision
1. Ajout de la méthode `.setContinuousScroll(size)` dans le `TabulatorBuilder`.
2. Activation par défaut de ce comportement dans le socle commun (`TabulatorFactory._createBaseGrid`).
3. La taille des lots (pages masquées) est fixée par défaut à 40 enregistrements, garantissant que l'ascenseur apparaisse immédiatement sur les grands écrans.

## Justification (KISS & UX)
L'expérience utilisateur devient similaire aux standards des réseaux sociaux et des SPA modernes. L'API backend (CakePHP) ne subit aucune modification : le `TabulatorAdapter` génère déjà le format compatible exigé par le Progressive Loading (`{ "data": [...], "last_page": X }`). Le couplage avec l'ADR 0038 (Confinement Flexbox) garantit un défilement fluide et exclusivement interne à la grille.

# ADR 0038 : Confinement de la hauteur des grilles de données (Tabulator)

**Date :** 10 Juillet 2026
**Statut :** Accepté

## Contexte
Sur des écrans standards, l'affichage de grilles de 20 lignes engendre un débordement du flux DOM hors de la fenêtre d'affichage (Viewport). Cela fait apparaître la barre de défilement verticale principale du navigateur (`body`), causant la perte de visibilité des en-têtes de l'application (Navbar) et une mauvaise expérience utilisateur.

## Décision
1. Ajout de la méthode `.setHeight(height)` dans le `TabulatorBuilder`.
2. Utilisation systématique d'une hauteur calculée en CSS via `calc(100vh - Xpx)` (où X représente la somme des marges et du menu de navigation) dans les fabriques métiers (`TabulatorFactory`).

## Justification (KISS & UX)
En contraignant la hauteur de l'enveloppe de la grille, le navigateur ne défile plus. C'est Tabulator qui active intelligemment sa propre barre de défilement interne *uniquement si le contenu l'exige*. Les en-têtes de colonnes restent fixes et les boutons de navigation globaux sont toujours accessibles.

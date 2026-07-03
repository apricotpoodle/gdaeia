# ADR 0022 : Standardisation de la colonne d'actions (DataGrids)

**Date :** 03 Juillet 2026
**Statut :** Accepté

## Contexte
Les tableaux de bord nécessitent fréquemment des boutons d'interaction par ligne (CRUD, Export, etc.) et des actions globales (Création, Réinitialisation de l'état). Répéter le balisage HTML (Bootstrap 5.3) et la logique de capture d'événements dans chaque fichier de vue constitue une violation du principe DRY (Don't Repeat Yourself) et complexifie la maintenance.

## Décision
Intégration d'une méthode abstraite `setWithActions(buttonsArray)` dans le composant `TabulatorBuilder`.
1. **En-tête** : Injection stricte d'un Dropdown Bootstrap contenant les actions globales (Créer / Reset).
2. **Cellules** : Injection d'un groupe de boutons configuré dynamiquement selon les clés demandées (ex: `view`, `edit`, `delete`, `viewpdf`, `impersonate`).
3. **Communication inter-composants** : La capture des clics est interceptée par la grille, qui retransmet l'intention au bus d'événements global (`TabulatorObserver`) sous le format d'événement `[sélecteur]:action:[nom_action]`.

## Justification
Cette approche supprime totalement la logique de manipulation du DOM dans les scripts de vues (Event-Driven Architecture). La grille émet un événement métier, et le script local de la vue décide de la manière d'y réagir (affichage d'une modale, redirection, etc.), garantissant un couplage lâche.

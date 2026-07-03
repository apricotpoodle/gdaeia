# ADR 0013 : Patrons de conception pour le front-end JavaScript (Tabulator)

**Date :** 03 Juillet 2026
**Statut :** Accepté

## Contexte
L'instanciation de composants complexes comme Tabulator.js directement dans les scripts de vues engendre une forte duplication de code (violation du principe DRY) et un couplage fort rendant les mises à jour de l'API difficiles.

## Décision
1. Mise en place d'un dossier `webroot/js/core/Tabulator/` centralisant l'architecture front-end.
2. Implémentation du patron **Builder** pour abstraire la configuration native de Tabulator.
3. Implémentation du patron **Factory** pour centraliser la déclaration des grilles métiers.
4. Implémentation du patron **Observer** pour gérer la communication inter-composants sur des vues complexes.
5. Obligation d'utiliser la `JSDoc` et un `README.md` local pour documenter ce cœur technique.
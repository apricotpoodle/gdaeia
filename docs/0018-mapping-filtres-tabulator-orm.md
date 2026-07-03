# ADR 0018 : Mapping des opérateurs de filtrage Tabulator vers ORM CakePHP

**Date :** 03 Juillet 2026
**Statut :** Accepté

## Contexte
En activant le mode `filterMode: remote` dans Tabulator, les saisies utilisateurs dans les en-têtes génèrent des requêtes HTTP contenant un tableau `filters`. Ce tableau précise le champ, la valeur, et l'opérateur souhaité (ex: `like`, `=`, `<`). L'API back-end ignorait jusqu'à présent ces paramètres.

## Décision
Le `TabulatorAdapter` a été enrichi pour intercepter le tableau `filters` et construire dynamiquement la clause `WHERE` de la requête CakePHP.
1. Utilisation de la méthode `resolveOrmField()` pour sécuriser les noms de colonnes et éviter les ambiguïtés (cf ADR 0015).
2. Création d'un bloc `switch` traduisant l'opérateur textuel de Tabulator (ex: `like`) en véritable clause SQL sécurisée par PDO (ex: `champ LIKE %valeur%`).

## Justification
Cette évolution centralise la logique de recherche dans l'adaptateur global. Les contrôleurs restent vides de toute logique de traitement des entrées utilisateur, garantissant une architecture pérenne (SOLID). La logique de résolution d'ambiguïté a été factorisée pour respecter le principe DRY.
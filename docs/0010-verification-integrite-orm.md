# ADR 0010 : Procédure de vérification de l'intégrité de l'ORM

**Date :** 03 Juillet 2026
**Statut :** Accepté

## Contexte
Suite à l'utilisation d'une boucle shell pour contourner les erreurs de la commande `bake model all` (voir ADR 0008) causées par des dépendances SQL circulaires, il est nécessaire de valider que les classes d'associations générées (`Table`) sont fonctionnelles au moment de l'exécution (Runtime).

## Décision
La vérification de l'intégrité de l'ORM s'effectue via la Console REPL interactive de CakePHP (`bin/cake console`). 
*Note : Dans CakePHP 5, cet outil nécessite l'installation préalable du plugin de développement via `composer require --dev cakephp/repl` et son activation via `bin/cake plugin load Cake/Repl`.*

En invoquant le `TableLocator` et en interrogeant la méthode `associations()->keys()` sur les modèles interdépendants, on force le framework à compiler l'arbre des relations en mémoire. Si aucune `DatabaseException` n'est levée, le modèle est considéré comme intègre et utilisable dans les Contrôleurs.

## Justification
L'utilisation du REPL respecte le principe KISS : c'est un outil dédié au débogage qui permet un diagnostic immédiat et non destructif des liens mémoire de l'ORM, sans avoir à créer des scripts de test jetables.
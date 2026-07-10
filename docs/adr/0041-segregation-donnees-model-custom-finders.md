# ADR 0041 : Ségrégation des Données (RLS) Centralisée via Custom Finders Composites

**Date :** 10 Juillet 2026
**Statut :** Accepté

## Contexte
L'application impose un cloisonnement strict des données (Row-Level Security) : un opérateur ne doit avoir accès qu'aux enregistrements (utilisateurs, formulaires) liés à son propre périmètre de départements. Écrire des structures conditionnelles `if/else` ou des jointures manuelles dans les contrôleurs violerait le principe de Responsabilité Unique (SRP) et introduirait un risque élevé de fuite de données en cas d'oubli de duplication du code.

## Décision
Nous centralisons la gouvernance des habilitations d'accès aux lignes de données au sein de la couche Modèle (`ORM\Table`) en utilisant des **Custom Finders hautement typés et sémantiques** :

1. **`UserDepartmentsTable::findDepartmentsOf(user)`** : Responsabilité factuelle. Extrait la sous-requête des IDs de départements associés à une entité `User` spécifique.
2. **`UsersTable::findVisibleTo(user)`** : Responsabilité de gouvernance (Composite). Gère le privilège du `issuperuser` (Bail Early / Vision globale) ou applique la jointure interne (`innerJoinWith`) restrictive basée sur le premier finder pour les utilisateurs cloisonnés.

## Justification (SOLID & Fat Models)
Cette architecture découple entièrement la sécurité graphique et technique du contrôleur (qui se contente d'invoquer `find('visibleTo', user: $currentUser)`). Passer l'entité `$user` complète garantit un typage fort (PHPStan Ready) et une excellente évolutivité. Le recours à l'arbre de dépendance des finders évite toute fuite de variables scalaires ou d'entiers magiques, sanctuarisant la base de données.

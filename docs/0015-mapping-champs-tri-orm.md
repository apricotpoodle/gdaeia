# ADR 0015 : Mapping dynamique des champs de tri entre JSON et ORM

**Date :** 03 Juillet 2026
**Statut :** Accepté

## Contexte
Le composant front-end Tabulator envoie des requêtes de tri basées sur le nommage de l'objet JSON reçu (ex: `field: "id"` ou `field: "role.name"`). L'injection directe de ces champs dans la clause `ORDER BY` de CakePHP génère des erreurs d'ambiguïté SQL (`Ambiguous column error`) lors des jointures (`contain`), car MySQL ne sait pas à quelle table appartient l'ID.

## Décision
Le `TabulatorAdapter` a été enrichi d'un mécanisme d'inflexion (via `\Cake\Utility\Inflector`). 
1. Tout champ simple (sans point) est automatiquement préfixé par l'alias de la table principale (ex: `Users.id`).
2. Tout champ imbriqué (avec un point) est converti de la convention JSON (singulier) vers la convention ORM CakePHP (Pluriel/CamelCase) (ex: `role.name` devient `Roles.name`).

## Justification
Cette solution est 100% DRY. Elle dispense le développeur front-end de devoir spécifier des champs SQL dans la configuration Javascript (séparation des préoccupations). L'API traduit elle-même le dialecte JSON en dialecte SQL de manière sécurisée.
# ADR 0044 : Recherche FULLTEXT MySQL via Callback FriendsOfCake/Search

**Date :** 14 Août 2026  
**Statut :** Accepté

## Contexte
La recherche globale sur les demandes de recrutement (`applicationforms`) nécessite d'interroger simultanément plusieurs colonnes textuelles (`jobtitle`, `applicantname`, `qualification`, `reasonforreplacement`) avec un niveau de performance maximal sur un volume important de données.

## Décision
1. Création d'un index `FULLTEXT` composite (`ft_applicationforms_global`) via une migration Phinx sur les colonnes cibles.
2. Déclaratif du Behavior `Search.Search` dans `ApplicationformsTable`.
3. Implémentation du callback `q` dans `searchManager()` pour formater dynamiquement la requête en syntaxe booléenne MySQL (`+terme*`) et appliquer la clause `MATCH() AGAINST(... IN BOOLEAN MODE)`.

## Justification
* **Performance** : Exploite l'index `FULLTEXT` natif d'InnoDB/MySQL au lieu de requêtes `LIKE %...%` coûteuses qui provoquent des balayages complets de table (*full table scans*).
* **SOLID / Fat Model** : Toute la logique de formatage booléen et d'appel à l'index est isolée dans la Table, rendant le contrôleur et l'API agnostiques.
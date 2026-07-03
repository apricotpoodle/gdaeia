# ADR 0009 : Implémentation du module Utilisateurs via API et Tabulator

**Date :** 03 Juillet 2026
**Statut :** Accepté

## Contexte
Mise en œuvre concrète de l'architecture découplée pour l'affichage des données. L'objectif est d'afficher la liste des utilisateurs de manière performante en déléguant le rendu front-end à Tabulator et le traitement métier (pagination/tri) au serveur, tout en imposant une documentation stricte (PHPDoc/JSDoc) sur l'ensemble du code produit.

## Décision
1. **Routage :** Création d'un préfixe de route `/api` forçant les extensions `.json` via `routes.php`.
2. **Service (`TabulatorAdapter.php`) :** Traduit les requêtes JSON de Tabulator en requêtes SQL pour l'ORM CakePHP et formate la réponse paginée.
3. **Séparation MVC (SoC) :** * `Controller/UsersController` : Ne charge aucune donnée, affiche uniquement la coquille HTML.
    * `Controller/Api/UsersController` : Contrôleur technique exposant strictement les données brutes au format JSON.
4. **Webroot :** Isoler le script JS d'instanciation dans `webroot/js/views/Users/index.js` en respectant la nomenclature miroir du backend.

## Justification
Cette approche (Separation of Concerns) garantit que notre API JSON pourra être réutilisée indépendamment (pour une application mobile ou un système externe), tout en gardant des contrôleurs Web extrêmement légers. Le niveau d'exigence sur le PHPDoc assure la maintenabilité du code par n'importe quel développeur.
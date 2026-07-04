# ADR 0027 : Gestion Dynamique des Messages Flash et Suppressions Asynchrones (Ajax)

**Date :** 05 Juillet 2026
**Statut :** Accepté

## Contexte
Pour moderniser l'expérience utilisateur (approche SPA - Single Page Application) et éviter les rechargements de page intempestifs lors des actions de suppression depuis les grilles Tabulator, les requêtes matérielles (`window.location.href`) doivent être remplacées par des appels asynchrones (`fetch`).
Cette transition soulève deux défis majeurs :
1. **Sécurité :** CakePHP exige la présence d'un jeton CSRF valide pour toute altération de données (POST/DELETE).
2. **Retour Utilisateur (UX) :** L'application doit pouvoir notifier l'utilisateur du succès ou de l'échec de l'opération (y compris en affichant les messages d'erreur métiers générés par CakePHP) sans nécessiter de rechargement de la page pour afficher les composants Flash natifs du framework.

## Décision
1. **Création du `FlashManager`** : Implémentation d'une classe utilitaire statique front-end (`webroot/js/core/FlashManager.js`) générant à la volée des alertes Bootstrap 5 flottantes. Ces alertes s'auto-détruisent via un minuteur asynchrone (DOM Garbage Collection) pour ne pas polluer l'arbre DOM.
2. **Injection du Jeton CSRF** : Le layout principal de l'application (`templates/layout/default.php`) expose désormais le jeton de sécurité natif de CakePHP via une balise `<meta name="csrfToken">`.
3. **Appels API standardisés** : Les actions destructrices de l'orchestrateur de vue (ex: `delete` dans `index.js`) utilisent l'API `fetch` en injectant l'en-tête `X-CSRF-Token` et `Accept: application/json`.
4. **Interception des Erreurs JSON** : Si le serveur CakePHP renvoie un code 4xx/5xx, le payload JSON est intercepté côté client pour extraire la clé `message` et l'afficher de manière native via le `FlashManager`.

## Justification
* **Performances** : La suppression combinée d'un enregistrement en base de données et de la ligne front-end (`usersTable.deleteRow(id)`) est instantanée, économisant un aller-retour complet de rendu de page complet par le serveur.
* **Propreté du DOM (KISS)** : Ne nécessitant aucun conteneur HTML codé en dur, le `FlashManager` est entièrement autonome et peut être invoqué depuis n'importe quel module JavaScript de l'application.

## Conséquences
* Toute future requête Ajax (POST, PUT, DELETE) devra obligatoirement récupérer la balise `<meta name="csrfToken">` pour s'authentifier auprès de CakePHP.
* L'utilisation des redirections de contrôleurs (`$this->redirect()`) doit être proscrite sur les méthodes ciblées par des appels Ajax. Le serveur doit répondre avec un code HTTP 200 (ou 204) et idéalement un payload JSON.

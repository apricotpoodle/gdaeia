# 0003 — Moderniser l'appel `Table::get()` des menus API

**Statut :** À planifier
**Priorité :** Moyenne

## Contexte

Les tests d'intégration de `Api/MenusController` signalent une dépréciation CakePHP 5 : l'appel à `Table::get()` avec un tableau d'options n'est plus recommandé.

L'appel concerné charge l'utilisateur et son rôle dans l'action `index`. Le comportement actuel est fonctionnel, mais devra être adapté avant le retrait définitif de cette signature.

## Objectif

Vérifier l'appel de `Table::get()` de `Api/MenusController` et le convertir, si nécessaire, vers les arguments nommés CakePHP compatibles.

## Critères d'acceptation

- L'appel utilise la signature CakePHP recommandée avec `contain` nommé.
- Les données `userData` de l'API des menus conservent leur comportement actuel.
- Les tests HTTP de `Api/MenusController` passent sans avertissement de dépréciation associé.
- La suite complète reste verte avec `make test.all`.

## Références

- [Api/MenusController](../../src/Controller/Api/MenusController.php)
- [Tests HTTP des menus](../../tests/TestCase/Controller/Api/MenusControllerTest.php)
- [Ticket 0002 — Étendre la couverture de tests applicatifs](0002-etendre-la-couverture-de-tests.md)

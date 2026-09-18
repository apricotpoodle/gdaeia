# 0005 — Moderniser l'appel `TreeBehavior::recover()` des menus

**Statut :** Terminé

**Priorité :** Moyenne

## Contexte

`MenusTable::afterSave()` reconstruit l'arbre intervallaire après une modification structurelle. Son appel actuel, `$this->recover()`, passe par la délégation magique des méthodes du comportement `Tree` vers la table.

CakePHP 5.3 déprécie cette délégation. Le comportement est encore fonctionnel, mais les tests affichent un avertissement : l'appel doit cibler explicitement le comportement. Une future version de CakePHP pourra retirer cette compatibilité et rendre la sauvegarde d'un menu défaillante.

## Objectif

Remplacer l'appel déprécié par l'accès explicite au comportement Tree :

```php
$this->getBehavior('Tree')->recover();
```

La correction ne doit modifier ni la stratégie de reconstruction de l'arbre, ni les droits, ni les données des menus existants.

## Critères d'acceptation

- `MenusTable::afterSave()` appelle explicitement le comportement `Tree` pour exécuter `recover()`.
- Une sauvegarde créant ou déplaçant une option de menu conserve un arbre cohérent (`lft` et `rght`).
- Un test de non-régression couvre le chemin qui déclenche la reconstruction.
- Les tests ciblant les menus ne produisent plus l'avertissement de dépréciation relatif à `Table::__call()` et `TreeBehavior::recover()`.
- La suite complète reste verte avec `make test.all`.

## Références

- [MenusTable::afterSave()](../../src/Model/Table/MenusTable.php#L78)
- [Documentation CakePHP TreeBehavior](../vendor_docs/cakephp-5/orm/behaviors/tree.md)
- [ADR 0032 — Standardisation du flux de travail de développement](../adr/0032-flux-de-travail-developpement.md)
- [Ticket 0002 — Étendre la couverture de tests applicatifs](0002-etendre-la-couverture-de-tests.md)

## Réalisation

Le commit `b506fe2` avait introduit la reconstruction manuelle dans
`MenusTable::afterSave()`. Le commit `d2577ae` a ensuite supprimé ce hook et
l'appel déprécié `$this->recover()` : le `TreeBehavior` gère directement les
mises à jour de l'arbre lors des opérations sur les menus. La dépréciation
visée par ce ticket n'est donc plus présente ; remplacer un appel désormais
absent par `getBehavior('Tree')->recover()` ne serait pas justifié.

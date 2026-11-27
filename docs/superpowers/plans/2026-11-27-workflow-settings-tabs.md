# Refonte des paramètres du workflow — plan d’implémentation

> **Pour les agents d’implémentation :** exécuter ce plan tâche par tâche et
> vérifier chaque étape avant de passer à la suivante.

**Objectif :** réorganiser `/workflow-settings` en trois onglets et permettre
la sauvegarde immédiate des paramètres simples par cellule Tabulator.

**Architecture :** les onglets « Validation » et « Paramètres des DAE »
contiennent des grilles locales alimentées par les endpoints GET existants.
Chaque ligne conserve son endpoint POST, sa validation et son autorisation.
Le catalogue des commentaires prédéfinis conserve son API CRUD dans son
propre onglet.

**Technologies :** CakePHP 5, PHP, JavaScript, Tabulator, PHPUnit et `node:test`.

**Spécification :** `docs/backlog/0043-refondre-ihm-parametrage-workflow.md`.

## Contraintes globales

- Conserver les contrats API et validations métier des réglages existants.
- Garder `k_coefficient` réservé au super-administrateur côté serveur.
- Enregistrer chaque cellule à sa validation, restaurer sa valeur en cas
  d’erreur et rendre le retour visible.
- Prendre en charge les types nombre, booléen, date et texte dans l’éditeur.
- Garder le réglage de duplication du ticket 0040 hors de cette livraison.
- Ne pas créer de commit sans validation explicite de l’opérateur.

---

### Tâche 1 : grilles typées et sauvegarde par réglage

**Fichiers :**
- Modifier `templates/WorkflowSettings/index.php`.
- Modifier `webroot/js/views/WorkflowSettings/index.js`.
- Créer `webroot/js/views/WorkflowSettings/settings-grid.js` pour les
  éditeurs typés, la configuration locale de la grille et les requêtes
  correspondant à chaque réglage.
- Modifier `src/Controller/Api/WorkflowSettingsController.php` pour conserver
  la valeur courante des champs commentaire écartés par l’ACL.
- Modifier `tests/TestCase/Controller/Api/WorkflowSettingsControllerTest.php`.
- Créer `tests/js/workflow-settings-grid.test.mjs`.
- Mettre à jour `webroot/js/views/README.md`.

**Interfaces :**
- Les GET et POST existants restent les frontières de lecture et d’écriture.
- Les lignes de grille exposent un identifiant, un libellé, une aide ou une
  unité, une valeur et son type.
- La fabrique commune `TabulatorFactory.createBaseGrid()` fournit le socle
  Tabulator ; `setLocalData()` remplace son chargement distant.
- La mutation d’une cellule passe par `TabulatorCellEditHandler` et
  `TabulatorMutationClient`, qui restaurent l’ancienne valeur et signalent
  l’erreur si la requête échoue.

- [x] **Étape 1 :** ajouter des tests JavaScript pour convertir chaque type
  (`number`, `boolean`, `date`, `text`) en un contrôle de saisie correspondant
  et valider que le basculement booléen confirme immédiatement la cellule.
- [x] **Étape 2 :** lancer `rtk node tests/js/workflow-settings-grid.test.mjs`
  et confirmer l’échec attendu avant toute implémentation.
- [x] **Étape 3 :** ajouter des tests JavaScript qui vérifient les URLs et
  payloads POST des quatre réglages, dont la préservation de la seconde
  obligation de commentaire lors de la sauvegarde d’une cellule.
- [x] **Étape 4 :** lancer à nouveau le test JavaScript et confirmer les échecs
  attendus sur les requêtes qui ne sont pas encore implémentées.
- [x] **Étape 5 :** compléter les tests PHPUnit pour vérifier les trois
  onglets, leurs panneaux, l’édition par champ, le refus des écritures directes
  non autorisées et la conservation d’un champ commentaire filtré par l’ACL.
- [x] **Étape 6 :** lancer `rtk make test.api` et vérifier que les nouveaux
  critères d’onglets échouent avant le changement de la vue.
- [x] **Étape 7 :** déplacer les contenus de la vue dans les trois onglets,
  ajouter les aides et unités, puis exposer les droits d’édition individuels.
- [x] **Étape 8 :** préserver dans l’API des obligations les valeurs des champs
  non présents après filtrage ACL, puis implémenter les éditeurs typés et
  raccorder chaque ligne aux GET/POST existants sans changer leur contrat.
- [x] **Étape 9 :** initialiser les grilles avec `setLocalData()`, brancher
  `TabulatorCellEditHandler`, et recalculer leur affichage au passage sur un
  onglet masqué jusque-là.
- [x] **Étape 10 :** relancer `rtk node tests/js/workflow-settings-grid.test.mjs` et
  `rtk make test.api`, puis corriger les échecs éventuels.
- [x] **Étape 11 :** vérifier au clavier les boutons d’onglets et l’édition,
  contrôler la configuration responsive des grilles et documenter le flux
  dans `webroot/js/views/README.md`.
- [x] **Étape 12 :** exécuter `rtk make test.style`, puis inspecter le diff et
  l’état Git.

La navigation au clavier est vérifiée par les sémantiques ARIA des onglets et
les tests de confirmation/annulation des éditeurs. Le responsive est vérifié
par la configuration Tabulator et les priorités de colonnes testées ; aucune
session navigateur authentifiée n’a été ouverte pour une inspection visuelle.

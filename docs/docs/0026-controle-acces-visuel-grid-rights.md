# 26. Contrôle d'Accès Visuel Unifié via 'grid_rights'

## Statut
Accepté

## Contexte
Dans le cadre de la standardisation des grilles applicatives Tabulator et du respect du principe de responsabilité unique (SRP), il est nécessaire de piloter l'affichage des composants d'interface (boutons d'action sur les lignes et visibilité des colonnes structurelles) directement depuis le serveur (Back-End) pour des raisons de sécurité, tout en garantissant une expérience utilisateur fluide (Front-End).

Les essais précédents ont mis en évidence des conflits de synchronisation liés au cycle de rendu asynchrone de Tabulator lors de l'utilisation de l'écouteur `dataProcessed`.

## Décision
1. **Contrat d'échange Back-End** : Toute entité CakePHP destinée à être rendue dans une grille hérite de `AppEntity`. Elle expose une propriété virtuelle unique `grid_rights` calculée via la méthode `_getGridRights()`. Cette propriété encapsule deux sous-domaines : `actions` (activation des boutons) et `columns` (visibilité des colonnes).
2. **Interception Front-End Infaillible** : La logique de traitement des droits de structure (colonnes) est injectée de manière immuable au cœur de la méthode `build()` du `TabulatorBuilder` via l'événement natif `ajaxResponse`.
3. **Contournement du Timing de Rendu** : Pour s'assurer que l'instance géométrique de la table est stabilisée dans le DOM avant d'appliquer les masquages, l'exécution des commandes `tableInstance.hideColumn()` est encapsulée dans une macro-tâche asynchrone différée (`setTimeout` de 10ms).
4. **Cache-Busting Industriel** : Fixation de la configuration des assets à `'force'` pour éliminer les rétentions de cache navigateur lors des déploiements de scripts d'infrastructure.

## Conséquences
* **Sécurité renforcée** : L'interface obéit aveuglément aux règles calculées par le serveur.
* **Code client allégé** : Les fabriques de grilles métiers (ex: `TabulatorFactory.createUsersTable`) n'ont plus à gérer la plomberie d'écoute des événements de droits, le comportement est hérité par l'infrastructure sous-jacente.
* **Maintenance simplifiée** : Zéro modification de code JavaScript requise lors du changement des règles d'accès d'un profil utilisateur.

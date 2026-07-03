# ADR 0025 : Routage Dynamique Polymorphique des Actions de Ligne Tabulator via les Métadonnées

**Date :** 03 Juillet 2026
**Statut :** Accepté

## Contexte
Avec l'expansion de l'application (visant plus de 30 boutons d'actions métiers différents, ex: `view`, `edit`, `viewpdf`, `impersonate`), l'architecture front-end doit être capable de mapper chaque clic de ligne vers l'URL ou l'API CakePHP correspondante (`/controller/action/id`) de manière entièrement générique. Écrire des blocs `switch` ou `if/else` monolithiques pour chaque action violerait les principes **DRY** et **KISS** et rendrait la maintenance impossible.

## Décision
1. **Routage par Métadonnées (Data Attributes)** : Injection des directives de routage (`data-action`, `data-target`, `data-is-event`) directement par la `ButtonFactory` au sein des balises HTML des boutons.
2. **Couplage par Registre de Configuration Centralisé** : Centralisation du dictionnaire de configuration de tous les boutons de l'application dans `ButtonFactory.js`.
3. **Paramétrage Fluide du Contrôleur** : Introduction de la méthode `.setController(string)` dans `TabulatorBuilder` pour dynamiquement contextualiser la racine de l'URL CakePHP selon l'entité de la table.
4. **Gestion des Tables sans Contrôleur Dédié (Tables Mixtes/Polymorphes)** : Si aucun contrôleur global n'est défini, le système lit l'attribut `data-controller` de la ligne de données. Cela permet à une seule table (ex: un tableau de bord global ou un flux d'activités) de rediriger vers des contrôleurs différents (ex: `/articles/view/12` sur la ligne 1, et `/users/view/4` sur la ligne 2).

## Spécification du dictionnaire de routage (ButtonFactory)
Chaque bouton déclaré doit respecter l'interface de configuration suivante :
- `icon` (string) : Classes FontAwesome de l'icône.
- `color` (string) : Variante de couleur Bootstrap (`primary`, `info`, `danger`, `warning`, etc.).
- `title` (string) : Texte du tooltip de survol.
- `target` (string, optionnel) : Cible de navigation (`_self` ou `_blank`). Par défaut `_self`.
- `isEvent` (boolean, optionnel) : Si `true`, empêche la redirection d'URL native et propage instantanément un événement global via le `TabulatorObserver` (idéal pour les confirmations de suppression ou l'ouverture de modales).

## Conséquences
- **Maintenance ultra-réduite** : L'ajout d'une action métier prend moins de 10 secondes (une simple ligne de déclaration dans le dictionnaire).
- **Zéro logique conditionnelle dans le Builder** : Le moteur de clic du `TabulatorBuilder` reste figé et immuable à environ 30 lignes de code, quel que soit le nombre d'actions futures.
- **Flexibilité totale** : Support natif du multi-contrôleur par ligne pour les tableaux de bord composites.

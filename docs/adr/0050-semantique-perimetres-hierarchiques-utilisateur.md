# ADR 0050 : Sémantique des périmètres hiérarchiques utilisateur

**Date :** 11 Septembre 2026  
**Statut :** Proposé  
**Dépendances :**
* [ADR 0041 : Ségrégation des données (RLS) centralisée via Custom Finders](./0041-segregation-donnees-model-custom-finders.md)
* [ADR 0049 : Intégration de TreeselectJS pour les structures hiérarchiques](./0049-integrationtreeselectjs_pour_structures_hierarchiques_departments.md)

---

## 1. Contexte

L'application affecte aux utilisateurs un périmètre de départements au moyen de la table de liaison `user_departments`. Les départements constituent une arborescence. L'interface d'ajout et de modification des utilisateurs utilise TreeselectJS pour sélectionner plusieurs nœuds de cet arbre.

Sans règle explicite, la signification d'une ligne dans `user_departments` est ambiguë : l'enregistrement d'un département parent peut être interprété comme un droit implicite sur tous ses descendants, ou comme un droit limité au nœud lui-même. Cette ambiguïté affecte directement les finders de visibilité et peut conduire à des droits plus larges que ceux affichés ou attendus.

## 2. Décision

1. **Droits explicites** : chaque ligne `user_departments` représente un département explicitement accordé. Elle ne confère pas, à elle seule, un droit implicite à l'ensemble de ses descendants.
2. **Sélection d'un parent** : dans les formulaires Utilisateurs, cocher un département parent sélectionne également tous ses descendants présents dans l'arbre à cet instant.
3. **Persistance exhaustive** : lors de la soumission, `user_departments` enregistre le parent sélectionné et chacun des descendants alors sélectionnés. La table de liaison contient donc la liste complète des droits effectifs.
4. **Désélection ciblée** : décocher un enfant retire cet enfant ainsi que ses propres descendants de la sélection et de la persistance, tout en conservant le parent et les autres branches sélectionnées. Décocher un parent retire le parent et tous ses descendants.
5. **Lecture cohérente** : les finders de visibilité s'appuient sur les identifiants explicitement présents dans `user_departments`. Ils ne doivent pas inférer de droits supplémentaires par parcours de l'arbre.

## 3. Justifications

Cette décision privilégie le principe du moindre privilège : un périmètre est déterminé par des droits observables en base, plutôt que par une règle d'héritage implicite. Elle permet aussi d'exprimer un périmètre partiel, par exemple conserver une direction et un de ses services tout en excluant une autre branche.

La sélection parent-vers-descendants améliore l'ergonomie pour l'attribution d'un périmètre complet. La persistance exhaustive rend ensuite la décision auditable, simple à requêter et indépendante du composant JavaScript. La séparation entre l'interface hiérarchique et la lecture explicite des droits respecte les principes de responsabilité unique et de défense en profondeur établis par l'ADR 0041.

## 4. Conséquences

### Positives

* Les droits effectifs sont lisibles et vérifiables directement dans `user_departments`.
* Un sous-arbre peut être accordé puis affiné par l'exclusion d'une branche, sans ambiguïté de sécurité.
* Les finders ORM restent simples et ne dépendent pas d'une logique récursive d'héritage des droits.
* L'interface TreeselectJS peut optimiser la sélection sans devenir la source de vérité des habilitations.

### Négatives

* Une attribution de périmètre complet produit plusieurs lignes dans `user_departments`.
* L'ajout ultérieur d'un nouveau département sous un parent déjà accordé ne lui donne pas automatiquement accès : une révision explicite du périmètre utilisateur est nécessaire.
* Les tests doivent couvrir les sélections et désélections de parents, d'enfants et de sous-arbres.

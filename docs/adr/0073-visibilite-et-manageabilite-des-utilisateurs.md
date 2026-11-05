# ADR 0073 : Distinction entre visibilité et gérabilité des utilisateurs

- Date : 2026-11-04
- Statut : Accepté
- Dépendances : ADR 0041, ADR 0050, ADR 0065, ADR 0069 et ADR 0072

## Contexte

`UsersTable::findVisibleTo()` répond actuellement à une question de lecture :
quels utilisateurs sont dans le périmètre visible de l'opérateur ? Cette
réponse est insuffisante pour les mutations. Un administrateur local peut
voir un autre administrateur dont le périmètre contient un département parent
de sa propre racine, notamment pour permettre la conduite des cycles de
validation, sans pour autant être autorisé à modifier cet administrateur.

La grille des utilisateurs ou l'onglet « Responsables » ne peut pas porter
seule cette distinction. Une URL, une requête POST ou un appel API construit
manuellement doit recevoir la même décision d'autorisation.

L'arborescence des départements repose sur des intervalles nested set
(`lft`/`rght`). Les lignes `user_departments` représentent les associations
effectives, conformément à l'ADR 0050 : aucun droit supplémentaire ne doit
être inféré d'une association parent dans la lecture des périmètres.

## Décision proposée

### Deux notions distinctes

Les modèles et Policies distinguent explicitement :

- **visible** : l'objet peut être consulté ou affiché dans le périmètre de
  l'opérateur ;
- **gérable** : l'objet peut être ciblé par une mutation autorisée pour
  l'opérateur.

`findVisibleTo()` conserve sa responsabilité de filtrage des utilisateurs
lisibles. Un nouveau finder sémantique, nommé `findManageableTo()`, fournit la
liste des utilisateurs pouvant être gérés dans le contexte de l'opérateur.
Il doit recevoir l'opérateur et, lorsque nécessaire, son périmètre racine.

### Règle de gérabilité

Un utilisateur visible est gérable si son périmètre ne contient aucun
département qui soit un ancêtre strict d'une racine explicite du périmètre de
l'opérateur courant.

Pour deux départements `A` et `B`, `A` est un ancêtre strict de `B` lorsque :

```text
A.lft < B.lft
ET
A.rght > B.rght
```

La détection ne s'appuie que sur les associations présentes dans
`user_departments`. Les liens hiérarchiques servent à comparer les
départements effectivement associés ; ils ne créent pas de nouveaux droits
implicites.

Un utilisateur peut donc être visible mais non gérable lorsqu'il est
associé à un département parent de la racine de l'opérateur. À l'inverse, un
utilisateur dont le périmètre est inclus dans celui de l'opérateur peut être
gérable, sous réserve des autres règles de Policy.

Pour les opérateurs disposant de plusieurs branches explicites, la règle est
évaluée par rapport à chacune de leurs racines. Une implémentation ne doit
pas supposer silencieusement qu'un périmètre possède toujours une racine
unique.

### Mutations couvertes

La gérabilité couvre les opérations suivantes :

1. modification des informations d'un utilisateur ;
2. ajout ou retrait d'un utilisateur dans `user_departments` ;
3. association ou dissociation d'un utilisateur comme responsable dans
   `department_managers`.

Le critère est appliqué à la liste, à la fiche et à la mutation :

- les listes peuvent masquer ou marquer les utilisateurs non gérables ;
- la fiche de modification refuse l'accès direct à un utilisateur non
  gérable ;
- les contrôleurs, Policies et services revérifient la règle juste avant
  l'écriture ;
- les endpoints API refusent les requêtes directes hors périmètre.

La présentation d'une association déjà existante peut rester en lecture
seule lorsque son responsable devient non gérable. L'absence de bouton ne
remplace pas le contrôle serveur.

### Règle sur les rôles

La modification d'un rôle est une mutation distincte, car elle peut changer
les droits de l'utilisateur cible. Un administrateur local peut modifier le
rôle d'un utilisateur gérable, sous réserve des restrictions suivantes :

- seul un superuser peut attribuer le rôle `ADMIN` ;
- seul un superuser peut modifier `issuperuser` ;
- une requête directe ne peut pas contourner ces restrictions ;
- l'interface limite les choix proposés, mais la Policy reste l'arbitre.

La gérabilité ne donne donc pas automatiquement le droit d'attribuer
n'importe quel rôle.

## Incompatibilités détectées dans l'implémentation actuelle

Cette décision ne peut pas être appliquée par un simple changement de grille.
Les points suivants devront être corrigés dans l'implémentation :

1. **`UserPolicy::canEdit()` est trop large.** Il autorise actuellement un
   administrateur à modifier un utilisateur sans vérifier que la cible est
   gérable. Une Policy ou un service de domaine doit intégrer ce contrôle.
2. **`UserPolicy::canAssociate()` ne vérifie pas la gérabilité.** Il vérifie
   le rôle de l'opérateur et l'activité de la cible, mais pas la relation entre
   les périmètres des deux utilisateurs.
3. **`DepartmentsController::addManager()` charge la cible avec
   `findVisibleTo()`.** Il doit utiliser la règle de gérabilité avant
   d'autoriser l'ajout.
4. **Les listes de responsables disponibles utilisent
   `findVisibleTo()`.** Elles doivent distinguer les utilisateurs visibles et
   les utilisateurs gérables.
5. **La dissociation utilise déjà une protection partielle.** La liste des
   responsables associés marque les cibles hors périmètre comme non
   dissociables, mais ce contrôle doit être aligné sur la nouvelle règle et
   rester appliqué côté serveur.
6. **`DepartmentPolicy` traite actuellement tout `ADMIN` comme global.**
   `canManageDepartments()` repose sur `isSuperUserOrAdmin()`. Autoriser un
   administrateur local à attribuer `ADMIN` lui donnerait donc potentiellement
   un accès global, contraire à la décision. Le rôle `ADMIN` doit rester
   réservé au superuser tant que cette Policy n'est pas rendue sensible au
   périmètre.
7. **Les affectations `user_departments` doivent être contrôlées par ligne.**
   Une modification partielle ne doit pas permettre d'ajouter une branche
   située au-dessus de la racine de l'opérateur ni de construire un nouveau
   périmètre global par API.

## Justification

Cette séparation applique le principe du moindre privilège et évite de
confondre une capacité de lecture avec une capacité de mutation. Elle
réutilise les custom finders prévus par l'ADR 0041, respecte la sémantique
d'associations explicites de l'ADR 0050 et conserve la Policy comme arbitre
des mutations conformément aux ADR 0065 et 0069.

La comparaison des intervalles `lft`/`rght` évite de recalculer l'arbre en
PHP et rend détectable le cas particulier des administrateurs dont le
périmètre est plus large que celui de l'opérateur courant.

## Conséquences

### Positives

- Les utilisateurs visibles mais non gérables sont identifiables et
  présentables en lecture seule.
- Les grilles, les fiches Web et les APIs peuvent partager la même règle.
- Les mutations directes ne dépendent plus d'un contrôle JavaScript ou d'un
  bouton absent.
- Le rôle `ADMIN` ne peut pas être attribué accidentellement avec un
  périmètre global.

### Négatives

- Le finder de gérabilité est contextuel et ne remplace pas
  `findVisibleTo()`.
- Les Policies, services et tests doivent être alignés pour éviter des
  décisions contradictoires.
- Les périmètres multiples exigent de comparer plusieurs racines.
- La règle ne pourra être considérée comme livrée tant que les mutations Web
  et API n'auront pas été sécurisées ensemble.

## Vérification attendue

Les tests doivent couvrir au minimum :

- un opérateur local et un utilisateur de son périmètre ;
- un utilisateur visible associé à un département parent, non gérable ;
- un utilisateur d'une branche indépendante, non visible ;
- l'accès direct à une fiche non gérable ;
- la modification des données utilisateur ;
- l'ajout et le retrait dans `user_departments` ;
- l'association et la dissociation dans `department_managers` ;
- la modification d'un rôle fonctionnel par un administrateur local ;
- le refus d'attribuer `ADMIN` ou `issuperuser` par un administrateur local ;
- l'attribution de `ADMIN` par un superuser ;
- les périmètres multi-branches et les associations explicites de l'ADR 0050.


# ADR 0052 : Présentation unifiée des erreurs de validation Web et API

**Date :** 11 Septembre 2026
**Statut :** Proposé
**Dépendances :**
* [ADR 0045 : Administration CRUD de la sécurité des champs](./0045-gestion-crud-field-authorizations.md)
* [ADR 0051 : Français comme langue applicative par défaut](./0051-francais-langue-applicative-par-defaut.md)

---

## 1. Contexte

CakePHP collecte les erreurs de validation et de règles d'intégrité dans les entités ORM. Elles sont disponibles de manière structurée par `$entity->getErrors()` et peuvent être affichées sous les champs par le `FormHelper`. Les contrôleurs Web et API de l'application produisent néanmoins encore des messages génériques ou des implémentations locales qui ne présentent qu'une partie des erreurs.

Cette dispersion entraîne trois problèmes : l'utilisateur ne sait pas toujours quel champ corriger, les réponses JSON ne suivent pas un contrat uniforme, et la même logique de parcours d'erreurs et de traduction de libellés est dupliquée dans plusieurs contrôleurs.

## 2. Décision

1. **Source de vérité** : les règles de validation et d'intégrité restent définies dans les classes `Table` CakePHP. Les messages de règles métier y sont rédigés en français.
2. **Service commun** : un service sans dépendance HTTP, `ValidationErrorPresenter`, centralise la transformation des erreurs d'entité. Il reçoit une entité et une ressource, puis retourne un résumé et toutes les erreurs structurées.
3. **Contrat d'erreur** : chaque erreur exposée contient au minimum le nom technique du champ, son libellé fonctionnel et son motif. Les associations imbriquées conservent leur chemin de champ.
4. **Rendu Web** : les contrôleurs Web utilisent le résumé pour le message Flash. Les templates continuent d'afficher les erreurs natives de CakePHP au plus près de chaque contrôle de formulaire.
5. **Rendu API** : les mutations refusées pour cause de validation retournent HTTP `422 Unprocessable Content` et le payload JSON standardisé `{success, message, errors}`. La clé `errors` contient la liste complète des erreurs structurées.
6. **Libellés métier** : les libellés de champs sont centralisés par ressource dans le service ou dans une configuration dédiée, et ne sont plus définis dans chaque contrôleur.

## 3. Justifications

Un service applicatif est préféré à un Component CakePHP. La présentation d'erreurs est une transformation pure des données ORM : elle doit pouvoir être réutilisée par les contrôleurs Web, les API, les commandes CLI et les tests sans dépendre du cycle de vie d'une requête HTTP.

Cette séparation respecte la responsabilité unique : les Models décident si une donnée est valide, le service explique les erreurs produites, et les contrôleurs choisissent le canal de réponse. Elle élimine les formatages dupliqués, garantit que tous les champs en erreur sont visibles et rend le contrat API prévisible pour les clients JavaScript.

## 4. Conséquences

### Positives

* Tous les utilisateurs reçoivent des messages français indiquant le champ et la raison de chaque erreur.
* Les interfaces Web et API s'appuient sur la même présentation des erreurs.
* Les clients API peuvent associer une erreur à un contrôle de formulaire précis.
* Le service est testable unitairement sans serveur HTTP ni navigateur.

### Négatives

* Les contrôleurs existants doivent migrer progressivement vers le nouveau service.
* Les messages anglais hérités de certaines règles ORM doivent être remplacés par des messages métier français.
* Le passage à HTTP `422` doit être pris en compte par les clients JavaScript qui supposaient un statut `400`.

# ADR 0047 - Centralisation de l'identité dans AppView et gestion des scripts de vue

* **Status :** Accepted
* **Date :** 2026-08-20

---

## Context

L'accès à l'identité de l'utilisateur connecté dans la couche de présentation nécessitait jusqu'à présent de récupérer manuellement l'attribut de requête (`$this->request->getAttribute('identity')`) dans chaque template CTP/PHP. Cette répétition alourdissait les vues et posait des soucis de typage statique (Intelephense / PHPStan) lors de l'évaluation des autorisations via `$identity->can()`.

Par ailleurs, l'insertion ponctuelle de blocs JavaScript inline (`<script>`) au sein des templates de vue posait des problèmes majeurs :
1. **Sécurité :** Violation des politiques *Content Security Policy* (CSP) strictes interdisant `unsafe-inline`.
2. **Performances :** Absence de mise en cache HTTP des scripts d'interaction.
3. **Maintenabilité :** Dispersion de la logique comportementale et rupture de la séparation des responsabilités (*Separation of Concerns*).

---

## Décision

### 1. Exposition globale et typage de l'identité dans `AppView`
L'instance de l'identité connectée est automatiquement injectée dans le contexte global de rendu via la méthode `initialize()` de `AppView` :

```php
// src/View/AppView.php
public function initialize(): void
{
    parent::initialize();
    $this->set('identity',$this->getRequest()->getAttribute('identity'));
}
```

- **Typage IHM / DocBlock** : Pour garantir la résolution des méthodes de contrôle d'accès (`can()`) par l'analyse statique et les IDE, les templates de vue doivent annoter l'identité avec l'interface du décorateur d'autorisation :

```PHP
/** @var \Authorization\IdentityInterface|null $identity */
```

- **Règle** : Les templates de vue ne doivent plus réinterroger la requête. La variable `$identity` est disponible nativement dans toutes les vues (`templates/**/*.php`).

### 2. Isolation des assets JavaScript dans webroot/js/views/{model}/

- **Interdiction stricte des scripts inline** : Les balises `<script>` sont proscrites des templates .`php`.

- **Arborescence dédiée par modèle** : Les scripts comportementaux de vue doivent être enregistrés sous le dossier du modèle concerné : 
`webroot/js/views/{model}/{script}.js` (ex: `webroot/js/views/applicationforms/comments-handler.js`).

- **Chargement via bloc CakePHP** : L'injection dans la mise en page s'effectue exclusivement via le helper HTML :

```PHP
<?= $this->Html->script('views/applicationforms/comments-handler', ['block' => true]) ?>
```

### 3. Normalisation de la disposition UI des vues CRUD

Les vues de détail (`view.php`) et d'édition (`edit.php`) adoptent une structure standardisée à deux colonnes :

- Un volet latéral (`aside.side-nav`) regroupant la navigation contextuelle et les actions de modification/suppression protégées par `$identity?->can()`.

- Une zone principale (`div.content`) réservée aux données métier et aux composants associés (ex: fil de discussion).

## Consequences

### Positive

- **Analyse statique sans avertissement** : Le typage via `\Authorization\IdentityInterface` résout les fausses erreurs IDE (ex: `P1013 Undefined method 'can'`).

- **Conformité CSP** : Élimination totale des vulnérabilités liées aux scripts inline.

- **Mise en cache optimale** : Exploitation native du cache HTTP pour les assets sous `webroot/js/views/{model}/`.

- **Ergonomie homogène** : Harmonisation visuelle de l'ensemble des modules CRUD.

### Négative

- Nécessite de migrer le code JS inline existant vers des fichiers statiques dédiés sous `webroot/js/views/{model}/` lors du refactoring des vues legacy.

# ADR 0054 : Commandes UI autorisées par domaine

**Date :** 15 septembre 2026

**Statut :** Proposé

**Dépendances :** [ADR 0023](./0023-factory-boutons-actions-ui.md), [ADR 0032](./0032-flux-de-travail-developpement.md), [ADR 0047](./0047-normalisation-vues-identity-assets-js.md)

## Contexte

Les templates construisaient directement des liens et boutons métiers. Ils devaient donc connaître simultanément la route CakePHP, l'action de Policy, la ressource à autoriser, les classes CSS et parfois le type de requête HTTP. Cette répétition rendait une migration d'interface fragile et pouvait créer un écart entre l'autorisation d'affichage et celle exécutée par le contrôleur.

Le précédent `AuthorizedLinkHelper` centralisait partiellement l'autorisation, mais exigeait encore du template de fournir du HTML et des détails d'implémentation. Il ne constituait donc pas une abstraction de domaine satisfaisante.

## Décision

1. Chaque action métier de présentation est une commande immuable `UiAction`. Elle porte son type de rendu, son libellé, son icône, sa destination, l'action de Policy, la ressource concernée et les options de rendu.
2. Chaque domaine possède un catalogue de fabriques statiques dédié : `MenusActions`, `UsersActions`, `ApplicationformsActions` et `FieldAuthorizationsActions`. Un template demande une commande métier ; il ne compose ni HTML, ni route, ni nom de Policy.
3. `ActionHelper::render()` est l'unique point de passage pour évaluer la Policy et rendre la commande avec `HtmlHelper` ou `FormHelper`. Une action refusée produit une chaîne vide.
4. Les navigations qui sont explicitement publiques sont déclarées dans `PublicActions` avec `requiresAuthorization` à `false`. Elles restent donc distinctes des actions métiers protégées.
5. Toute nouvelle action métier doit être ajoutée à la fabrique de son domaine, documentée en PHPDoc et couverte par un test portant sur son contrat déclaratif. Les templates n'instancient jamais `UiAction` directement.

## Justification

Cette séparation applique la responsabilité unique : les fabriques portent la sémantique du domaine, le Helper porte l'autorisation et le rendu, et le template exprime uniquement l'intention de la vue. Le modèle Command rend les actions testables sans HTTP ni rendu HTML. Le découpage par domaine conserve une surface réduite, évite une fabrique globale monolithique et respecte les principes DRY, KISS et ouvert/fermé.

## Conséquences

### Positives

* Les routes, Policies, icônes et styles des actions sont centralisés et cohérents.
* La visibilité d'une action est systématiquement évaluée avant son rendu.
* Les tests unitaires valident les contrats des actions sans dépendre des templates.
* Les templates sont plus courts et ne transportent plus de balisage métier répliqué.

### Négatives

* Une action très locale nécessite tout de même une méthode de fabrique et son test.
* Le catalogue doit être tenu à jour lors de l'ajout d'un domaine ou d'une nouvelle Policy.

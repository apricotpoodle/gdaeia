# ADR 0031 : Stratégie d'exemption de sécurité pour les outils de développement (DebugKit)

## 1. Contexte
L'application utilise les middlewares CakePHP `Authentication` et `Authorization` avec une politique de type "Fail-Closed" (`requireAuthorizationCheck` activé). Cela garantit que toute route dépourvue d'une vérification explicite de droits est automatiquement bloquée par sécurité (Erreur 500 - `AuthorizationRequiredException`).

Cependant, le plugin de développement `DebugKit` possède ses propres contrôleurs (ex: `ToolbarController`). Ces contrôleurs n'héritent pas du `AppController` de l'application et n'implémentent aucune logique de vérification d'autorisation (Policies). Par conséquent, l'affichage de la barre d'outils est systématiquement bloqué par les middlewares de l'application hôte.

Tenter de résoudre ce problème en modifiant `AppController::beforeFilter` est inefficace, car les requêtes vers les plugins n'empruntent pas ce cycle de vie.

## 2. Décision
Nous avons décidé d'implémenter une **dérogation de sécurité globale et centralisée**, gérée par un écouteur d'événements (Event Listener) situé au niveau de l'amorçage de l'application (`config/bootstrap.php`), plutôt que de polluer les contrôleurs métiers.

Nous utilisons l'événement `Controller.startup` pour intercepter toutes les requêtes de manière agnostique. Si la requête cible explicitement le plugin `DebugKit` :
1. Le gestionnaire d'événements injecte dynamiquement le composant `Authorization` sur le contrôleur du plugin.
2. Il force l'instruction `$controller->Authorization->skipAuthorization()` pour bypasser légitimement le middleware.
3. Il ouvre l'accès non-authentifié si le composant `Authentication` est présent.

## 3. Conséquences

**Positives :**
* **Pureté du code métier** : Le fichier `src/Controller/AppController.php` reste propre et ne contient aucune logique conditionnelle liée à l'environnement de développement ou à des outils externes.
* **Robustesse de sécurité** : La dérogation est hermétique, strictement limitée par la condition `$request->getParam('plugin') === 'DebugKit'`. Elle n'expose aucune route de l'application.
* **Compatibilité PHPStan** : L'utilisation d'annotations ciblées (`@phpstan-ignore-next-line`) pour les composants chargés dynamiquement maintient le niveau d'exigence maximal de l'analyse statique.

**Négatives :**
* L'ajout d'un écouteur global sur `Controller.startup` ajoute une micro-pénalité de performance (négligeable) à chaque requête. (À noter : DebugKit étant désactivé en production, ce code pourrait être encapsulé dans une vérification d'environnement `if (Configure::read('debug'))` pour une optimisation absolue).

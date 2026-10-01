# ADR 0061 : Gestion unifiée des notifications Flash Web

**Date :** 01 Octobre 2026
**Statut :** Accepté
**Dépendances :** [ADR 0027](./0027-gestion-messages-flash-dynamiques.md), [ADR 0030](./0030-modernisation-scripts-modules-es6.md), [ADR 0060](./0060-un-flash-par-champ-invalide-web.md)

## Contexte

Les Flash CakePHP et les notifications JavaScript partagent désormais un emplacement, mais deux scripts règlent séparément leur durée et leur fermeture. Les messages serveur `warning` et `info` utilisent encore un rendu différent, sans expiration. Cette duplication complique l'application de la règle des cinq secondes prévue par l'ADR 0060.

## Décision

1. `FlashManager` devient le seul gestionnaire de l'affichage, de l'empilement et de la fermeture des notifications Web. Les Flash CakePHP sont rendus en HTML par un élément commun, puis initialisés par le gestionnaire ; les appels AJAX créent le même toast en JavaScript.
2. `app.js` est le point d'entrée ES6 commun chargé par les layouts et importe `FlashManager`. Le layout n'inclut pas directement un module `core/`. Une feuille CSS locale donne le même rendu aux pages ordinaires et aux pages d'erreur, sans dépendre du JavaScript de Bootstrap.
3. La durée par défaut est définie une seule fois dans `FlashManager` : cinq secondes pour tous les types. Les appels JavaScript gardent leur paramètre de durée explicite, dont `0` pour un message permanent.
4. Chaque Flash serveur conserve son temps restant lors du rechargement du même chemin. La fermeture manuelle ou l'expiration retire individuellement le message du stockage de session. Les notifications AJAX ne sont pas restaurées après rechargement, comme auparavant.
5. Les contrôleurs CakePHP, l'API statique JavaScript (`show`, `success`, `error`, `warning`, `info`) et le contrat des réponses API restent inchangés.

Cette décision fait évoluer le rendu par alertes AJAX et l'autonomie sans conteneur préexistant décrits dans l'ADR 0027. `FlashManager` peut toujours créer le conteneur si une page n'en fournit pas.

## Justification

Un seul cycle de vie évite des minuteries et des règles de fermeture divergentes. Le rendu initial côté serveur maintient les messages lisibles avant l'exécution de JavaScript ; leur initialisation côté client assure l'expiration et la restauration. Le point d'entrée partagé répond au caractère transversal des notifications tout en conservant des imports ES6 explicites.

## Conséquences

* Les erreurs, succès, avertissements et informations utilisent tous le même rendu et la même durée par défaut.
* Les modèles PHP et la création JavaScript doivent conserver les mêmes classes et attributs de toast.
* L'API dynamique accepte toujours du HTML ; les données variables interpolées dans ce HTML doivent être échappées par les appelants.

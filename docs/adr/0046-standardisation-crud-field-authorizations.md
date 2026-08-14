# ADR 0046 : Standardisation du CRUD hybride (FieldAuthorizations)

**Date :** 14 Août 2026
**Statut :** Accepté

## Contexte
Suite au succès de l'architecture hybride déployée pour la gestion des utilisateurs, il est nécessaire de dupliquer ce fonctionnement pour la table `FieldAuthorizations`. L'objectif est de conserver des "Skinny Controllers" et de séparer les responsabilités entre l'affichage (Web) et la manipulation de données (API).

## Décision
1. **Contrôleur Web (`src/Controller/FieldAuthorizationsController.php`)** : Aura la responsabilité stricte de rendre les gabarits HTML (`index`, `add`, `edit`). Il gère également l'action `delete` de manière hybride (redirection standard ou payload JSON) pour satisfaire la grille Tabulator.
2. **Contrôleur API (`src/Controller/Api/FieldAuthorizationsController.php`)** : S'occupera exclusivement de consommer les requêtes XHR/Fetch (`add`, `edit`) en renvoyant un standard JSON stricte (`success`, `message`).
3. **Sécurité (Policy)** : Toutes les méthodes intègrent un verrou d'autorisation (`$this->Authorization->authorize()`) pointant vers `FieldAuthorizationPolicy`.

## Justification (DRY, SoC, KISS)
* **SoC (Separation of Concerns)** : Le rendu HTML n'est jamais mélangé avec la logique métier JSON.
* **KISS** : La gestion hybride du `delete` évite de dupliquer la logique de vérification d'intégrité de l'ORM.

## Liens
* [README Contrôleurs Web](../../src/Controller/README.md)
* [README Contrôleurs API](../../src/Controller/Api/README.md)
# ADR 0060 : Un message Flash par champ invalide dans les formulaires Web

**Date :** 01 Octobre 2026
**Statut :** Accepté
**Dépendance :** [ADR 0052 : Présentation unifiée des erreurs de validation Web et API](./0052-presentation-unifiee-erreurs-validation-web-api.md)

## Contexte

L'ADR 0052 prévoit un seul message Flash Web résumant la première erreur. L'entité et `ValidationErrorPresenter` conservent pourtant toutes les erreurs. Ainsi, un envoi vide de `roles/add` produit des erreurs pour Code, Libellé et Clé de tri, mais seul Code apparaît dans les notifications. Les erreurs sous les champs restent utiles, sans donner une vue complète dans la pile de messages.

## Décision

1. Les contrôleurs Web utilisent la liste complète produite par `ValidationErrorPresenter` et émettent un message Flash par chemin de champ invalide, dans l'ordre des erreurs ORM.
2. Si plusieurs règles échouent sur un même champ, leurs motifs distincts sont réunis dans son message. Une sauvegarde échouée sans erreur d'entité conserve un seul message générique.
3. Les erreurs natives de CakePHP restent affichées sous les champs du formulaire.
4. Le contrat API de l'ADR 0052 reste `{success, message, errors}` avec HTTP 422 ; `message` conserve le résumé de la première erreur et `errors` la liste complète.
5. Les notifications Flash s'empilent et chacune expire cinq secondes après son apparition. Un rechargement du même écran conserve seulement le temps restant.

Cette décision remplace uniquement le point 4 « Rendu Web » de l'ADR 0052.

## Justification

Le service commun fournit déjà les libellés et les motifs. Un seul traitement dans `AppController` évite de répéter le parcours des erreurs dans chaque contrôleur Web. Regrouper par champ permet de signaler tous les champs à corriger sans multiplier les notifications pour un même contrôle.

## Conséquences

* L'utilisateur voit immédiatement tous les champs invalides et peut encore consulter les erreurs sous chaque contrôle.
* Un formulaire très incomplet peut afficher plusieurs notifications simultanées ; leur pile doit rester lisible.
* Les clients API et leurs tests ne changent pas de contrat.

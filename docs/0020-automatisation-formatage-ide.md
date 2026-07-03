# ADR 0020 : Standardisation et Automatisation du Formatage de Code via l'IDE

**Date :** 03 Juillet 2026
**Statut :** Accepté

## Contexte
Afin de maintenir une base de code lisible, homogène et conforme aux standards de l'industrie (PSR-12/PER-CS pour PHP, ES6+ pour JavaScript) entre tous les développeurs du projet, le formatage manuel ou l'oubli de formatage doit être éradiqué.

## Décision
Le fichier `.vscode/settings.json` est partagé au sein du dépôt pour automatiser le cycle de vie du code :
1. **`editor.formatOnSave: true`** : Devient obligatoire. Chaque écriture sur le disque déclenche le linter/formateur.
2. **Assignation des Formateurs** :
   * PHP : Délégué à `Intelephense` (aligné sur les standards PER/PSR).
   * Web (JS, CSS, JSON) : Délégué aux outils natifs de l'IDE.
3. **Hygiène du code** : Nettoyage automatique des espaces en fin de ligne et injection d'une ligne finale vide (`insertFinalNewline`).

## Justification
Cette approche élimine les revues de code (Pull Requests) polluées par des conflits d'indentation ou des retours à la ligne intempestifs. Le style est appliqué à la racine, garantissant la propreté du dépôt Git (KISS).

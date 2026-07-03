# ADR 0016 : Organisation des Services de Données et Cohérence PSR-4

**Date :** 03 Juillet 2026
**Statut :** Accepté

## Contexte
Avec l'augmentation du nombre de composants front-end, les adaptateurs et services de manipulation des requêtes de grilles doivent être regroupés logiquement. L'introduction du sous-dossier `src/Service/DataGrid/` nécessite un alignement strict avec la norme d'autoloading PSR-4 pour éviter les erreurs `Class not found`.

## Décision
1. **Rangement** : Tous les adaptateurs de données pour les librairies de datagrid (Tabulator ou autres futurs outils) doivent résider dans le sous-namespace `App\Service\DataGrid`.
2. **Rigueur PSR-4** : Le nommage des fichiers physiques doit correspondre au caractère et à la casse près au nom de la classe PHP interne (ex: `TabulatorAdapter.php`).
3. **Consommation** : Les contrôleurs utilisant ces services doivent importer le FQCN complet (ex: `use App\Service\DataGrid\TabulatorAdapter`).

## Justification
Le respect strict de la norme PSR-4 élimine les comportements imprévisibles de l'autoloading de Composer entre les environnements de développement (parfois insensibles à la casse sur macOS/Windows) et les environnements de production (strictement sensibles à la casse sur Linux/Docker).
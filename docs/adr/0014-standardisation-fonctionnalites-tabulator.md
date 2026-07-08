# ADR 0014 : Standardisation des fonctionnalités de base des grilles (Tri, Multi-tri et Filtrage)

**Date :** 03 Juillet 2026
**Statut :** Accepté

## Contexte
L'application comporte de nombreuses vues de données complexes (Utilisateurs, Demandes d'autorisation d'engagement, Services, etc.). Afin d'offrir une expérience utilisateur (UX) homogène et performante, chaque tableau doit systématiquement proposer le tri simple, le tri combiné (multi-colonnes) et le filtrage individuel par colonne, sans que cela n'engendre de lignes de codes répétitives.

## Décision
1. **Évolution du Monteur (`TabulatorBuilder`)** : Ajout de la méthode `setColumnDefaults(defaults)` mappée sur la configuration native de Tabulator pour appliquer des comportements transversaux descendants.
2. **Synchronisation Remote** : L'activation de `setRemotePagination()` force également l'état `filterMode: "remote"`. L'application des filtres front-end émettra une requête AJAX structurée vers le contrôleur API.
3. **Configuration par défaut obligatoire** : Toute table métier créée au sein du projet via la `TabulatorFactory` doit explicitement invoquer `setColumnDefaults` pour définir la politique par défaut de tri et de filtrage du module concerné.

## Justification
Cette approche garantit le respect strict des principes DRY et KISS. L'activation des fonctionnalités de recherche et d'organisation est découplée de la définition des colonnes. Les performances de rendu front-end sont préservées, car la captation des entrées de filtrage est soumise à validation (`LiveFilter: false`), évitant le déclenchement de requêtes HTTP à chaque caractère saisi.
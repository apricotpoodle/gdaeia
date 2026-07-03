# ADR 0004 : Architecture de l'API Tabulator et Patrons de Conception GoF

**Date :** 02 Juillet 2026
**Statut :** Accepté

## Contexte
L'application doit afficher des tables de données complexes via Tabulator. Pour garantir la performance face à de gros volumes de données, le traitement (pagination, tri, filtres) doit être délégué au serveur MySQL via CakePHP. De plus, les assets front-end sous `webroot` doivent être organisés de manière propre et modulaire.

## Décisions
1. **Stratégie de chargement :** Utilisation exclusive du mode *Remote* de Tabulator (requêtes AJAX successives lors des changements de page/tri/filtres).
2. **Patron de conception GoF - L'Adaptateur (Adapter) :** Nous créons un composant de type *Adapter* pour faire la passerelle entre le format des requêtes/réponses de Tabulator et le fonctionnement de l'ORM de CakePHP.
3. **Structure de `webroot` :** Organisation stricte et modulaire des fichiers JavaScript et CSS pour séparer la configuration globale de Tabulator de la logique spécifique de chaque page.

## Justification
* **SOLID (SRP) :** Le contrôleur CakePHP ne sait pas comment Tabulator formate ses requêtes. C'est l'Adaptateur qui a cette unique responsabilité.
* **DRY :** L'Adaptateur est générique. Il peut être réutilisé pour n'importe quelle table (Utilisateurs, Commandes, Produits) sans dupliquer de code.
* **KISS :** Le navigateur ne télécharge que les données visibles à l'écran (ex: 20 lignes), préservant la mémoire et la fluidité de l'interface.